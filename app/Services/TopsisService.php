<?php

namespace App\Services;

/**
 * TopsisService – clean TOPSIS implementation with Min-Max Normalization.
 *
 * COST criteria  (lower is better):
 *   distance, elevation, duration, cost, difficulty, crowd_level
 *
 * BENEFIT criteria (higher is better):
 *   panorama_score, fasilitas_score, popularity_score, safety_score
 *
 * Normalization method: MIN-MAX NORMALIZATION
 * ─────────────────────────────────────────────
 * Mengapa Min-Max dan BUKAN Vector Normalization?
 *
 * Vector normalization (r_ij = x_ij / √(Σx²)) memiliki kelemahan fundamental:
 * kriteria dengan range mentah yang besar (misal cost: 15.000–1.000.000)
 * tetap mendominasi jarak Euclidean meskipun bobotnya kecil, karena
 * magnitude kolom setelah normalisasi TIDAK seragam.
 *
 * Min-Max normalization memetakan SEMUA kriteria ke [0, 1]:
 *   r_ij = (x_ij − min_j) / (max_j − min_j)
 *
 * Ini menjamin setiap kriteria memiliki kontribusi proporsional
 * terhadap bobot yang diberikan user, sesuai prinsip TOPSIS standar.
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
     * Rank alternatives using TOPSIS with Min-Max Normalization.
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
        $matrix = [];
        foreach ($alternatives as $alt) {
            $row = [];
            foreach ($criterionKeys as $key) {
                $row[$key] = (float) ($alt['criteria'][$key] ?? 0.0);
            }
            $matrix[] = $row;
        }

        // ── Step 2: Min-Max Normalization ──────────────────────────────────
        //
        // r_ij = (x_ij − min_j) / (max_j − min_j)
        //
        // Semua nilai dinormalisasi ke range [0, 1].
        // Jika semua alternatif memiliki nilai sama (degenerate),
        // kolom tersebut di-set 0.0 karena tidak memberikan diskriminasi.
        $colMin     = [];
        $colMax     = [];
        $degenerate = [];

        foreach ($criterionKeys as $key) {
            $col = array_column($matrix, $key);
            $min = min($col);
            $max = max($col);

            $colMin[$key] = $min;
            $colMax[$key] = $max;

            // Zero-variance check: semua nilai identik → degenerate
            $degenerate[$key] = (abs($max - $min) < 1e-9);
        }

        $normMatrix = [];
        foreach ($matrix as $row) {
            $normRow = [];
            foreach ($criterionKeys as $key) {
                if ($degenerate[$key]) {
                    // Degenerate: semua alternatif identik → kontribusi 0
                    $normRow[$key] = 0.0;
                } else {
                    $range = $colMax[$key] - $colMin[$key];
                    $normRow[$key] = ($row[$key] - $colMin[$key]) / $range;
                }
            }
            $normMatrix[] = $normRow;
        }

        // ── Step 3: Weighted normalised matrix  v_ij = w_j * r_ij ─────────
        $weighted = [];
        foreach ($normMatrix as $normRow) {
            $wRow = [];
            foreach ($criterionKeys as $key) {
                // If criterion wasn't provided by user, weight = 0 (excluded from ranking)
                $w = isset($normWeights[$key]) ? $normWeights[$key] : 0.0;
                $wRow[$key] = $w * $normRow[$key];
            }
            $weighted[] = $wRow;
        }

        // ── Step 4 & 5: Positive (A+) and Negative (A−) ideal solutions ───
        //
        // Untuk Min-Max normalization:
        //   BENEFIT: A+ = max(v_ij) = w_j * 1.0 = w_j,  A- = min(v_ij) = w_j * 0.0 = 0
        //   COST:    A+ = min(v_ij) = w_j * 0.0 = 0,     A- = max(v_ij) = w_j * 1.0 = w_j
        //
        // Namun kita tetap menghitung dari data aktual agar degenerate
        // dan edge case tertangani dengan benar.
        $idealPos = [];
        $idealNeg = [];

        foreach ($criterionKeys as $key) {
            $col       = array_column($weighted, $key);
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
            //   > 0  → closer to A+ than to A-  (strength)
            //   < 0  → closer to A- than to A+  (weakness)
            $contributions = [];
            $wRow          = $weighted[$idx];
            foreach ($criterionKeys as $key) {
                $gapToPos = ($wRow[$key] - $idealPos[$key]) ** 2;
                $gapToNeg = ($wRow[$key] - $idealNeg[$key]) ** 2;
                // Signed: positive means criterion is a STRENGTH (far from neg ideal).
                $contributions[$key] = round($gapToNeg - $gapToPos, 6);
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
     * Only normalises weights that were explicitly provided by the user.
     * Missing keys are not given any default weight — they are excluded entirely.
     * The result always sums to 1.0 (for the provided weights only).
     *
     * This design supports partial preferences:
     *   - User provides {cost: 5, distance: 3} → only these are ranked
     *   - Other criteria are set to weight 0 implicitly
     *   - This makes the semantics clear and predictable
     *
     * @param  array<string, float> $weights    User-supplied weights (may be a subset)
     * @param  string[]             $keys       All criterion keys (unused, for compatibility)
     * @return array<string, float>            Normalised weights (only keys from input)
     */
    public function normaliseWeights(array $weights, array $keys): array
    {
        // Edge case: no weights provided → return empty (backend caller will handle)
        if (empty($weights)) {
            return [];
        }

        $total = array_sum($weights);

        if ($total <= 0.0) {
            // All weights were <= 0 → invalid, return empty
            return [];
        }

        // Normalize ONLY the weights that were provided
        $normalised = [];
        foreach ($weights as $key => $value) {
            if ($value > 0) {
                $normalised[$key] = $value / $total;
            }
        }

        return $normalised;
    }
}
