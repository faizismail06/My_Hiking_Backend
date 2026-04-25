<?php

namespace App\Services;

/**
 * TopsisExplainerService
 *
 * Generates human-readable explanations from TOPSIS contribution data.
 *
 * This service has NO influence on ranking results — it only reads the
 * 'contributions' map that TopsisService attaches to each scored alternative
 * and translates numbers into user-facing text.
 *
 * Contract:
 *   Input  : 'contributions' array from TopsisService::rank() output.
 *            Each value is signed:
 *              +large  → this criterion is a STRENGTH for this route
 *              -large  → this criterion is a WEAKNESS for this route
 *              ≈ 0     → criterion is neutral / degenerate
 *
 *   Output : [
 *     'explanation' => string   e.g. "Direkomendasikan karena panorama terbaik dan durasi singkat"
 *     'key_factor'  => string   e.g. "panorama_score"
 *   ]
 */
class TopsisExplainerService
{
    /**
     * Indonesian labels for each criterion key.
     * Used in the explanation sentence.
     */
    private const LABELS = [
        'distance'         => 'jarak tempuh',
        'elevation'        => 'elevasi',
        'duration'         => 'durasi',
        'cost'             => 'biaya',
        'difficulty'       => 'tingkat kesulitan',
        'crowd_level'      => 'tingkat keramaian',
        'panorama_score'   => 'panorama',
        'fasilitas_score'  => 'fasilitas',
        'popularity_score' => 'popularitas',
        'safety_score'     => 'keamanan jalur',
    ];

    /**
     * Criteria that are COST type (lower = better).
     * A POSITIVE contribution on a cost criterion means this route has a
     * LOW value → user-facing phrasing should say "singkat", "murah", etc.
     */
    private const COST_CRITERIA = [
        'distance', 'elevation', 'duration', 'cost', 'difficulty', 'crowd_level',
    ];

    /**
     * Adjective pairs [strength_phrase, weakness_phrase] for each criterion,
     * used to construct the explanation sentence.
     */
    private const PHRASES = [
        'distance'         => ['jarak dekat',          'jarak jauh'],
        'elevation'        => ['elevasi rendah',        'elevasi tinggi'],
        'duration'         => ['durasi singkat',        'waktu tempuh panjang'],
        'cost'             => ['biaya terjangkau',      'biaya mahal'],
        'difficulty'       => ['jalur ramah pemula',    'jalur menantang'],
        'crowd_level'      => ['jalur tidak padat',     'jalur ramai'],
        'panorama_score'   => ['pemandangan terbaik',   'panorama terbatas'],
        'fasilitas_score'  => ['fasilitas lengkap',     'fasilitas minim'],
        'popularity_score' => ['jalur populer',         'kurang dikenal'],
        'safety_score'     => ['jalur aman',            'keamanan perlu perhatian'],
    ];

    /**
     * Threshold below which a contribution is considered negligible.
     * Contributions smaller than this (in absolute terms) are ignored so
     * genuinely degenerate or near-zero criteria don't appear in explanations.
     */
    private const MIN_CONTRIBUTION = 1e-9;

    /**
     * Generate explanation from a contribution map.
     *
     * @param  array<string, float> $contributions  From TopsisService ranked item.
     * @param  float                $score          CC score (0-1) of this alternative.
     * @return array{explanation: string, key_factor: string}
     */
    public function explain(array $contributions, float $score): array
    {
        // ── Guard: empty or all-zero contributions ──────────────────────────
        if (empty($contributions)) {
            return $this->fallback($score);
        }

        // ── Filter out negligible contributions ─────────────────────────────
        $active = array_filter(
            $contributions,
            fn (float $v) => abs($v) >= self::MIN_CONTRIBUTION
        );

        if (empty($active)) {
            return $this->fallback($score);
        }

        // ── Sort by absolute magnitude descending ───────────────────────────
        // Largest absolute value = most decisive criterion for this route.
        arsort($active); // sort by value descending (largest positive first)

        // Split into strengths (positive) and weaknesses (negative)
        $strengths  = array_filter($active, fn (float $v) => $v > 0);
        $weaknesses = array_filter($active, fn (float $v) => $v < 0);

        // The key_factor is the single criterion with the largest absolute impact.
        $allByMagnitude = $contributions;
        uasort($allByMagnitude, fn ($a, $b) => abs($b) <=> abs($a));
        $keyFactor = (string) array_key_first($allByMagnitude);

        // ── Build explanation sentence ───────────────────────────────────────
        $explanation = $this->buildSentence($strengths, $weaknesses, $score);

        return [
            'explanation' => $explanation,
            'key_factor'  => $keyFactor,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Build a natural-language sentence from top strengths and weaknesses.
     *
     * Rules:
     *  - Lead with top 1-2 strengths if the route scored well (score ≥ 0.5)
     *  - Lead with top weakness if it scored poorly (score < 0.5)
     *  - Always name at most 2 factors to keep the string short and readable
     */
    private function buildSentence(
        array $strengths,
        array $weaknesses,
        float $score
    ): string {
        $parts = [];

        if ($score >= 0.5) {
            // Good route — lead with strengths
            $topStrengths = array_slice($strengths, 0, 2, true);
            foreach ($topStrengths as $key => $val) {
                $parts[] = self::PHRASES[$key][0] ?? self::LABELS[$key] ?? $key;
            }

            // Append top weakness (max 1) only if it's significant
            if (!empty($weaknesses)) {
                $topWeak = array_key_first($weaknesses);
                $parts[] = 'namun ' . (self::PHRASES[$topWeak][1] ?? 'ada kelemahan');
            }

            $intro = 'Direkomendasikan karena ';
        } else {
            // Weaker route — lead with weakness, then best strength
            $intro = 'Peringkat lebih rendah karena ';

            if (!empty($weaknesses)) {
                $topWeak = array_key_first($weaknesses);
                $parts[] = self::PHRASES[$topWeak][1] ?? self::LABELS[$topWeak] ?? $topWeak;
            }

            if (!empty($strengths)) {
                $topStrength = array_key_first($strengths);
                $parts[] = 'meski ' . (self::PHRASES[$topStrength][0] ?? 'ada kelebihan');
            }
        }

        if (empty($parts)) {
            return $score >= 0.5
                ? 'Jalur ini sesuai dengan preferensi Anda.'
                : 'Jalur ini kurang sesuai dengan preferensi Anda.';
        }

        return $intro . implode(', ', $parts) . '.';
    }

    /**
     * Fallback when contributions are unavailable or all degenerate.
     *
     * @return array{explanation: string, key_factor: string}
     */
    private function fallback(float $score): array
    {
        $text = $score >= 0.5
            ? 'Jalur ini memiliki keseimbangan kriteria yang baik.'
            : 'Jalur ini kurang optimal berdasarkan preferensi Anda.';

        return [
            'explanation' => $text,
            'key_factor'  => '',
        ];
    }
}
