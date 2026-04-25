<?php

namespace App\Services;

/**
 * TopsisService – clean TOPSIS implementation.
 *
 * COST criteria  (lower is better):
 *   distance, elevation, duration, cost, difficulty, crowd_level
 *
 * BENEFIT criteria (higher is better):
 *   panorama_score, fasilitas_score, popularity_score, safety_score
 *
 * Pre-processing pipeline (applied inside rank() before the decision matrix):
 *   scaleCriteria() – handles scale imbalance and outliers per criterion.
 *
 * Weights are supplied by the caller (already normalised to sum = 1.0).
 * If no weights are supplied, equal weights are used.
 */
class TopsisService
{
    /**
     * Criterion type definitions.
     * 'cost'    → lower is better
     * 'benefit' → higher is better
     */
    private const CRITERIA_TYPES = [
        // cost
        'distance'         => 'cost',
        'elevation'        => 'cost',
        'duration'         => 'cost',
        'cost'             => 'cost',
        'difficulty'       => 'cost',
        'crowd_level'      => 'cost',
        // benefit
        'panorama_score'   => 'benefit',
        'fasilitas_score'  => 'benefit',
        'popularity_score' => 'benefit',
        'safety_score'     => 'benefit',
    ];

    /**
     * Rank alternatives using TOPSIS.
     *
     * Each alternative must contain:
     *   'route_id'      => int
     *   'route_name'    => string
     *   'mountain_name' => string
     *   'criteria'      => [
     *       'distance'         => float,
     *       'elevation'        => float,
     *       'duration'         => float,
     *       'cost'             => float,
     *       'difficulty'       => float,  // numeric only: 1=mudah … 4=sangat_sulit
     *       'crowd_level'      => float,
     *       'panorama_score'   => float,
     *       'fasilitas_score'  => float,
     *       'popularity_score' => float,
     *       'safety_score'     => float,
     *   ]
     *
     * $weights is an associative array keyed by criterion name.
     * It will be normalised internally so callers don't need to ensure sum = 1.
     *
     * @param  array<int, array<string, mixed>> $alternatives
     * @param  array<string, float>             $weights
     * @return array<int, array<string, mixed>>
     *   Each element contains:
     *     'route_id', 'route_name', 'mountain_name', 'score'
     *     'contributions' => [ criterion => float ]
     *       Positive value  = this criterion pulled the route TOWARD A+ (good).
     *       Negative value  = this criterion pushed the route AWAY from A+ (bad).
     *       Magnitude reflects weighted Euclidean gap; used by the explainer only.
     */
    public function rank(array $alternatives, array $weights = []): array
    {
        if (empty($alternatives)) {
            return [];
        }

        $criterionKeys = array_keys(self::CRITERIA_TYPES);
        $normWeights   = $this->normaliseWeights($weights, $criterionKeys);

        // ── Step 1: Build raw decision matrix ──────────────────────────────
        //
        // FIX – Scale imbalance: apply per-criterion pre-scaling BEFORE the
        // matrix is built so that criteria with wildly different value ranges
        // (e.g. popularity_score 10-10000 vs panorama_score 1-5) do not
        // dominate the vector-normalisation step.
        $matrix = [];
        foreach ($alternatives as $alt) {
            $row = [];
            foreach ($criterionKeys as $key) {
                $raw         = (float) ($alt['criteria'][$key] ?? 0.0);
                $row[$key]   = $this->scaleCriterion($key, $raw);
            }
            $matrix[] = $row;
        }

        // ── Step 2: Vector normalisation  r_ij = x_ij / sqrt(sum x_ij²) ──
        //
        // FIX – Zero-variance bug: when every alternative shares the same
        // value for a criterion, sumSq > 0 but all normalised values would be
        // identical non-zero numbers.  They contribute a constant offset to
        // EVERY distance calculation – a phantom bias that cannot change any
        // relative ranking but still adds noise and misleads weight tuning.
        // The correct treatment: mark the column degenerate and set all
        // normalised values to 0.0, removing its influence entirely.
        $divisors    = [];   // sqrt(sum x_ij²) per criterion
        $degenerate  = [];   // true when all alternatives are identical

        foreach ($criterionKeys as $key) {
            $col    = array_column($matrix, $key);
            $sumSq  = array_sum(array_map(fn (float $v) => $v ** 2, $col));

            // Zero-variance check: variance == 0 ⟺ all values are equal.
            // We detect it by checking min == max (cheaper than computing
            // variance and avoids floating-point equality pitfalls).
            $min = min($col);
            $max = max($col);

            if ($min === $max) {
                // All alternatives identical on this criterion → degenerate.
                $degenerate[$key] = true;
                $divisors[$key]   = 1.0;   // avoid division by zero below
            } else {
                $degenerate[$key] = false;
                $divisors[$key]   = $sumSq > 0.0 ? sqrt($sumSq) : 1.0;
            }
        }

        $normMatrix = [];
        foreach ($matrix as $row) {
            $normRow = [];
            foreach ($criterionKeys as $key) {
                // Degenerate criterion → contribute 0 to all distances.
                $normRow[$key] = $degenerate[$key]
                    ? 0.0
                    : ($row[$key] / $divisors[$key]);
            }
            $normMatrix[] = $normRow;
        }

        // ── Step 3: Weighted normalised matrix  v_ij = w_j * r_ij ─────────
        $weighted = [];
        foreach ($normMatrix as $normRow) {
            $wRow = [];
            foreach ($criterionKeys as $key) {
                $wRow[$key] = $normWeights[$key] * $normRow[$key];
            }
            $weighted[] = $wRow;
        }

        // ── Step 4 & 5: Positive (A+) and Negative (A−) ideal solutions ───
        $idealPos = [];
        $idealNeg = [];

        foreach ($criterionKeys as $key) {
            $col      = array_column($weighted, $key);
            $isBenefit = self::CRITERIA_TYPES[$key] === 'benefit';

            $idealPos[$key] = $isBenefit ? max($col) : min($col);
            $idealNeg[$key] = $isBenefit ? min($col) : max($col);
        }

        // ── Step 6: Euclidean distance to A+ (d+) and A− (d−) ─────────────
        $distances = [];
        foreach ($weighted as $idx => $wRow) {
            $dPlus  = 0.0;
            $dMinus = 0.0;
            foreach ($criterionKeys as $key) {
                $dPlus  += ($wRow[$key] - $idealPos[$key]) ** 2;
                $dMinus += ($wRow[$key] - $idealNeg[$key]) ** 2;
            }
            $distances[$idx] = [
                'd_plus'  => sqrt($dPlus),
                'd_minus' => sqrt($dMinus),
            ];
        }

        // ── Step 7: Closeness coefficient  CC_i = d−_i / (d+_i + d−_i) ───
        $scored = [];
        foreach ($alternatives as $idx => $alt) {
            $dPlus  = $distances[$idx]['d_plus'];
            $dMinus = $distances[$idx]['d_minus'];
            $denom  = $dPlus + $dMinus;

            $cc = $denom > 0.0 ? ($dMinus / $denom) : 0.0;

            // ── Contribution annotation (does NOT affect CC) ───────────────
            // For each criterion we record:
            //   gapToPos = (v_ij - A+_j)²  →  how far from ideal positive
            //   gapToNeg = (v_ij - A-_j)²  →  how far from ideal negative
            //
            // contribution = gapToNeg - gapToPos
            //   > 0  → closer to A- than to A+  (pushes CC up  = good)
            //   < 0  → closer to A+ than to A-  (already near ideal)
            //
            // The magnitude tells the explainer which criterion matters most
            // for this specific alternative relative to the whole field.
            $contributions = [];
            $wRow          = $weighted[$idx];
            foreach ($criterionKeys as $key) {
                $gapToPos = ($wRow[$key] - $idealPos[$key]) ** 2;
                $gapToNeg = ($wRow[$key] - $idealNeg[$key]) ** 2;
                // Signed: positive means criterion is a STRENGTH (far from neg ideal).
                // We flip cost criteria so the sign convention is always
                // "positive = good for this route".
                $isBenefit = self::CRITERIA_TYPES[$key] === 'benefit';
                if ($isBenefit) {
                    // High value is good: being far from A- (low) is a strength.
                    $contributions[$key] = round($gapToNeg - $gapToPos, 6);
                } else {
                    // Low value is good: being far from A- (high) means
                    // the route has a LOW cost/difficulty → strength.
                    $contributions[$key] = round($gapToNeg - $gapToPos, 6);
                }
            }

            $scored[] = [
                'route_id'      => (int)    $alt['route_id'],
                'route_name'    => (string) $alt['route_name'],
                'mountain_name' => (string) $alt['mountain_name'],
                'score'         => round($cc, 4),
                'contributions' => $contributions,   // used by explainer; stripped before API output
            ];
        }

        // ── Step 8: Sort descending by score ───────────────────────────────
        usort($scored, fn (array $a, array $b) => $b['score'] <=> $a['score']);

        return $scored;
    }

    /**
     * Normalise a user-supplied weight map.
     *
     * Missing keys default to 1.0 / count(criteria) (equal weight).
     * The result always sums to 1.0.
     *
     * @param  array<string, float> $weights
     * @param  string[]             $keys
     * @return array<string, float>
     */
    public function normaliseWeights(array $weights, array $keys): array
    {
        $n       = count($keys);
        $default = $n > 0 ? 1.0 / $n : 0.0;

        $raw = [];
        foreach ($keys as $key) {
            $raw[$key] = isset($weights[$key]) && $weights[$key] >= 0
                ? (float) $weights[$key]
                : $default;
        }

        $total = array_sum($raw);

        if ($total <= 0.0) {
            // Fallback: equal weights
            return array_fill_keys($keys, $default);
        }

        $normalised = [];
        foreach ($keys as $key) {
            $normalised[$key] = $raw[$key] / $total;
        }

        return $normalised;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Pre-scaling helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Apply per-criterion scaling to a single raw value.
     *
     * Why per-criterion instead of a global transform?
     * Each criterion has a different semantic range and distribution.  A global
     * transform would be imprecise and harder to reason about.
     *
     * Strategies used:
     *
     *  popularity_score  – log1p transform
     *    Range can be 10–10 000 (visitor counts, ratings×count, etc.).
     *    Without scaling, vector-normalisation heavily favours high-traffic
     *    routes simply because their raw numbers dwarf all others.
     *    log(x + 1) compresses the range to ~2.4–9.2 while preserving order.
     *
     *  cost (biaya)  – log1p transform
     *    Prices in Rupiah span 0–500 000+.  A route costing 250 000 IDR would
     *    otherwise dominate the cost column vs. 5 000 IDR routes even after
     *    normalisation, making cost effectively the master criterion.
     *
     *  elevation  – IQR-style ceiling clamp at 3 500 m
     *    Indonesian hikes rarely exceed Rinjani (3 726 m).  An erroneous GPS
     *    value of, say, 8 000 m would distort the entire column.  Clamping to
     *    a realistic ceiling neutralises data-entry outliers without losing
     *    useful discrimination between 500 m and 3 500 m routes.
     *
     *  All other criteria – pass through unchanged.
     *    distance (km), duration (hours), difficulty (1-4), crowd_level,
     *    panorama_score, fasilitas_score, safety_score are already on
     *    comparable, bounded scales; no transformation needed.
     *
     * @param  string $key  Criterion name (must be a key of CRITERIA_TYPES).
     * @param  float  $raw  Raw value read from the database.
     * @return float        Scaled value ready for the decision matrix.
     */
    private function scaleCriterion(string $key, float $raw): float
    {
        return match ($key) {
            // ── Log1p: compresses 10–10 000 → ~2.4–9.2 ───────────────────
            'popularity_score' => log($raw + 1.0),

            // ── Log1p: compresses 0–500 000 → 0–13.1 ─────────────────────
            // Preserves the zero-cost case (log(0+1) = 0).
            'cost'             => log($raw + 1.0),

            // ── Clamp at 3 500 m to neutralise GPS outliers ───────────────
            // Indonesian peaks top out at ~3 726 m (Rinjani).
            // Any value above that is almost certainly a data error.
            'elevation'        => min($raw, 3500.0),

            // ── All other criteria pass through unchanged ─────────────────
            default            => $raw,
        };
    }
}
