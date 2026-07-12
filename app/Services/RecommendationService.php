<?php

namespace App\Services;

use App\Models\Trail;
use App\Models\User;

/**
 * RecommendationService
 *
 * Flow:
 *  1. Fetch ALL routes (no tier / difficulty filtering).
 *  2. Apply optional basic constraints (budget).
 *  3. Map user-supplied priority weights to TOPSIS criterion keys.
 *  4. Send ALL candidates to TopsisService with user weights (tier does NOT modify weights).
 *  5. Sort by TOPSIS closeness coefficient (descending).
 *  6. Generate explanation + key_factor from contribution data (TopsisExplainerService).
 *  7. Call DSSService per route for risk annotation (risk_level, warning).
 *     Tier is ONLY used here — for informational risk assessment, not ranking.
 *  8. Return enriched, clean API response.
 */
class RecommendationService
{
    // DIFFICULTY_MAP is intentionally removed.
    // Difficulty is now computed from real route metrics inside computeDifficulty().
    // Keeping a label-to-integer map would couple ranking to subjective text values.

    public function __construct(
        private TopsisService         $topsisService,
        private TopsisExplainerService $explainerService,
        private DSSService            $dssService,
        private ?WeatherService       $weatherService = null
    ) {}

    /**
     * Get DSS recommendations.
     *
     * @param  int|null              $limit          Max items to return (null = all).
     * @param  User|null             $user           Authenticated hiker.
     * @param  array<string, float>  $userWeights    Priority weights from the client.
     *         Supported keys (all optional, un-normalised):
     *           priority_distance, priority_elevation, priority_difficulty,
     *           priority_cost, priority_duration, priority_crowd_level,
     *           priority_panorama, priority_fasilitas,
     *           priority_popularity, priority_safety
     * @param  float|null            $maxBudget      Optional budget constraint (Rupiah).
     * @return array<int, array<string, mixed>>
     */
    public function getRecommendations(
        ?int   $limit       = null,
        ?User  $user        = null,
        array  $userWeights = [],
        ?float $maxBudget   = null
    ): array {
        // ── Step 1: Fetch all routes ────────────────────────────────────────
        $routes = Trail::has('mountain')
            ->with('mountain')
            ->where('dss_status', 'approved')
            ->get();

        if ($routes->isEmpty()) {
            return [];
        }

        // ── Step 2: Basic constraints (budget only) ─────────────────────────────
        if ($maxBudget !== null && $maxBudget > 0) {
            $routes = $routes->filter(
                fn (Trail $r) => (float) ($r->biaya ?? 0) <= $maxBudget
            );
        }

        if ($routes->isEmpty()) {
            return [];
        }

        // ── Step 3: Build alternatives for TOPSIS ──────────────────────────
        $alternatives = [];
        foreach ($routes as $route) {
            $alternatives[] = $this->buildAlternative($route);
        }

        // Map client-supplied weight keys → TOPSIS criterion keys
        $topsisWeights = $this->mapUserWeights($userWeights);

        // NOTE: If no weights are supplied, default to equal weights (3.0 for all criteria)
        if (empty($topsisWeights)) {
            $topsisWeights = [
                'distance'         => 3.0,
                'elevation'        => 3.0,
                'difficulty'       => 3.0,
                'cost'             => 3.0,
                'duration'         => 3.0,
                'crowd_level'      => 3.0,
                'panorama_score'   => 3.0,
                'fasilitas_score'  => 3.0,
                'popularity_score' => 3.0,
                'safety_score'     => 3.0,
            ];
        }

        // NOTE: Tier is intentionally NOT used here.
        // TOPSIS ranking reflects user preferences only.
        // Tier info is used exclusively for risk_level assessment (DSSService).

        // ── Step 4: TOPSIS ranking ─────────────────────────────────────────
        $ranked = $this->topsisService->rank($alternatives, $topsisWeights);

        if (empty($ranked)) {
            return [];
        }

        // ── Tiebreaker & Early slicing ──────────────────────────────────────
        // PHP's usort is not stable. Enforce deterministic tiebreaker
        // before slicing to keep ordering consistent.
        usort($ranked, function (array $a, array $b) {
            $scoreDiff = $b['score'] <=> $a['score'];
            if ($scoreDiff !== 0) {
                return $scoreDiff;
            }
            return $a['route_id'] <=> $b['route_id'];
        });

        if ($limit !== null && $limit > 0) {
            $ranked = array_slice($ranked, 0, $limit);
        }

        // Index originals by route_id for O(1) lookup
        $routeById = [];
        foreach ($routes as $route) {
            $routeById[(int) $route->id] = $route;
        }

        // ── Prefetch weather concurrently for the ranked slice ──────────────
        $coordsList = [];
        foreach ($ranked as $item) {
            $route = $routeById[(int) $item['route_id']] ?? null;
            if ($route) {
                $coords = $this->dssService->resolveCoordinates($route);
                $coordsList[] = [$coords['lat'], $coords['lng']];
            }
        }
        if ($this->weatherService) {
            $this->weatherService->prefetchWeather($coordsList);
        }

        // ── Steps 5-7: Explain + Risk + Build API response ─────────────────
        $results = [];
        foreach ($ranked as $position => $item) {
            $routeId = (int) $item['route_id'];
            $route   = $routeById[$routeId] ?? null;

            // ── Explanation from TOPSIS contributions ─────────────────────
            // Contributions are stripped from the final API output — they are
            // an internal signal only.
            $contributions = (array) ($item['contributions'] ?? []);
            $explainResult = $this->explainerService->explain($contributions, (float) $item['score']);

            // ── Risk info from DSSService ─────────────────────────────────
            $riskInfo = $this->resolveRiskInfo($route, $user);

            $results[] = [
                'route_id'      => $routeId,
                'route_name'    => $item['route_name'],
                'mountain_name' => $item['mountain_name'],
                'score'         => $item['score'],
                'rank'          => $position + 1,
                'risk_level'    => $riskInfo['risk_level'],
                'warning'       => $riskInfo['warning'],
                'explanation'   => $explainResult['explanation'],
                'key_factor'    => $explainResult['key_factor'],
            ];
        }

        return $results;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Build the alternative array consumed by TopsisService.
     *
     * Difficulty is NOT taken from the tingkat_kesulitan label.
     * It is computed from physical route metrics so the TOPSIS matrix
     * reflects measurable reality instead of a subjective category string.
     * See computeDifficulty() for the formula.
     */
    private function buildAlternative(Trail $route): array
    {
        $distance  = max(0.0, (float) ($route->jarak   ?? 0.0));
        $elevation = max(0.0, (float) ($route->elevasi ?? 0.0));
        $duration  = max(0.0, (float) ($route->durasi  ?? 0.0));
 
        // Computed difficulty: derives a continuous 1-4 score purely from physical metrics.
        $difficultyValue = $this->computeDifficulty(
            $distance,
            $elevation,
            $duration
        );
 
        // Adjust popularity score to 1-100 range.
        // If the value in DB is > 100 (old seed data), divide by 10. Otherwise, use as-is.
        $rawPopularity = max(0.0, (float) ($route->popularity_score ?? 0.0));
        $popularityValue = $rawPopularity > 100.0 ? min(100.0, $rawPopularity / 10.0) : $rawPopularity;

        return [
            'route_id'      => (int)    $route->id,
            'route_name'    => (string) $route->nama,
            'mountain_name' => (string) ($route->mountain->nama ?? '-'),
            'criteria'      => [
                // ── COST ─────────────────────────────────────────────────────────
                'distance'         => $distance,
                'elevation'        => $elevation,
                'duration'         => $duration,
                'cost'             => max(0.0, (float) ($route->biaya        ?? 0.0)),
                'difficulty'       => $difficultyValue,
                'crowd_level'      => max(0.0, (float) ($route->crowd_level  ?? 0.0)),
                // ── BENEFIT ────────────────────────────────────────────────────
                'panorama_score'   => max(0.0, (float) ($route->panorama_score   ?? 0.0)),
                'fasilitas_score'  => max(0.0, (float) ($route->fasilitas_score  ?? 0.0)),
                'popularity_score' => $popularityValue,
                'safety_score'     => max(0.0, (float) ($route->safety_score     ?? 0.0)),
            ],
        ];
    }

    /**
     * Compute a continuous difficulty score on the 1–4 scale purely from physical metrics.
     *
     * Formula rationale
     * -----------------
     * Three physically measurable features drive true hiking difficulty:
     *
     *   distance (km)    – normalised against 20 km  (hard upper bound for
     *                       a single Indonesian day-hike).
     *   elevation (m)    – normalised against 3 500 m (realistic ceiling for Indonesian peaks).
     *   duration (hours) – normalised against 14 h   (a very long day-hike).
     *
     * Weights reflect rough domain consensus:
     *   elevation   40% – vertical gain is the primary fatigue driver.
     *   distance    35% – total horizontal load.
     *   duration    25% – partially redundant with the two above but captures
     *                       terrain roughness and pace.
     *
     * The demand score (0–1) is rescaled to 1–4 (matching the old label scale).
     *
     * @param  float  $distanceKm   Route length in km.
     * @param  float  $elevationM   Elevation gain in metres.
     * @param  float  $durationH    Estimated duration in hours.
     * @return float                Computed difficulty in [1.0, 4.0].
     */
    private function computeDifficulty(
        float  $distanceKm,
        float  $elevationM,
        float  $durationH
    ): float {
        // --- Physical demand score (0 to 1) ----------------------------------
        $normDistance  = min(1.0, $distanceKm / 20.0);
        $normElevation = min(1.0, $elevationM / 3500.0);
        $normDuration  = min(1.0, $durationH  / 14.0);
 
        $demandScore = ($normElevation * 0.40)
                     + ($normDistance  * 0.35)
                     + ($normDuration  * 0.25);
 
        // Rescale 0-1 demand to 1-4 range (matches old label integers).
        $metricDifficulty = 1.0 + ($demandScore * 3.0);
 
        return round($metricDifficulty, 4);
    }

    /**
     * Soft-tier weight modifier.
     *
     * Adjusts the DIFFICULTY weight up or down based on the user's experience
     * tier WITHOUT removing any route from the candidate set.
     *
     * Rationale:
     *   - Beginners should have difficulty count MORE in their ranking so
     *     easy routes naturally bubble to the top for them.
     *   - Advanced hikers should have difficulty count LESS so challenging
     *     routes are not penalised relative to their other preferences.
     *
     * The modifier is additive on the un-normalised weight before it enters
     * TopsisService::normaliseWeights(). TopsisService always re-normalises
     * to sum = 1, so this is bias-free — it only shifts relative importance.
     *
     * Multipliers (applied to whatever difficulty weight was user-supplied
     * or default-assigned):
     *   pemula   (beginner) → ×1.5  (difficulty matters more)
     *   menengah (mid)      → ×1.0  (unchanged)
     *   mahir    (advanced) → ×0.6  (difficulty matters less)
     *
     * @param  array<string, float> $weights  Mapped TOPSIS criterion weights.
     * @param  User|null            $user
     * @return array<string, float>           Modified weights (un-normalised).
     */
    private function softTierWeightModifier(array $weights, ?User $user): array
    {
        if ($user === null) {
            return $weights;
        }

        $tierMultipliers = [
            'pemula'   => 1.5,
            'menengah' => 1.0,
            'mahir'    => 0.6,
        ];

        $tierKey    = strtolower(trim((string) ($user->tier ?? '')));
        $multiplier = $tierMultipliers[$tierKey] ?? 1.0;

        if ($multiplier === 1.0 || !isset($weights['difficulty'])) {
            return $weights;
        }

        // Clone and apply multiplier; TopsisService re-normalises to sum=1.
        $modified               = $weights;
        $modified['difficulty'] = $weights['difficulty'] * $multiplier;

        return $modified;
    }

    /**
     * Translate user-supplied priority keys to TOPSIS criterion names.
     *
     * Client keys accepted:
     *   priority_distance, priority_elevation, priority_difficulty,
     *   priority_cost, priority_duration, priority_crowd_level,
     *   priority_panorama, priority_fasilitas,
     *   priority_popularity, priority_safety
     *
     * @param  array<string, float> $userWeights
     * @return array<string, float>
     */
    private function mapUserWeights(array $userWeights): array
    {
        $keyMap = [
            'priority_distance'    => 'distance',
            'priority_elevation'   => 'elevation',
            'priority_difficulty'  => 'difficulty',
            'priority_cost'        => 'cost',
            'priority_duration'    => 'duration',
            'priority_crowd_level' => 'crowd_level',
            'priority_panorama'    => 'panorama_score',
            'priority_fasilitas'   => 'fasilitas_score',
            'priority_popularity'  => 'popularity_score',
            'priority_safety'      => 'safety_score',
        ];

        $mapped = [];
        foreach ($keyMap as $clientKey => $criterionKey) {
            if (isset($userWeights[$clientKey])) {
                $mapped[$criterionKey] = (float) $userWeights[$clientKey];
            }
        }

        return $mapped;
    }

    /**
     * Call DSSService to get risk annotation.
     * Falls back gracefully when user or route is missing.
     *
     * @return array{risk_level: string, warning: bool, short_reason: string}
     */
    private function resolveRiskInfo(?Trail $route, ?User $user): array
    {
        if ($route === null || $user === null) {
            return [
                'risk_level'  => 'unknown',
                'warning'     => false,
                'short_reason' => 'Informasi risiko tidak tersedia.',
            ];
        }

        try {
            $dss = $this->dssService->evaluateRoute($user, $route);

            $riskLevel   = (string) ($dss['risk_level']     ?? 'unknown');
            $reasoning   = (array)  ($dss['reasoning']      ?? []);
            $shortReason = !empty($reasoning)
                ? implode(' ', array_slice($reasoning, 0, 2))
                : ($dss['message'] ?? 'Tidak ada keterangan tambahan.');

            return [
                'risk_level'   => $riskLevel,
                'warning'      => in_array($riskLevel, ['caution', 'high_risk'], true),
                'short_reason' => $shortReason,
            ];
        } catch (\Throwable) {
            return [
                'risk_level'   => 'unknown',
                'warning'      => false,
                'short_reason' => 'Gagal mengevaluasi risiko.',
            ];
        }
    }
}
