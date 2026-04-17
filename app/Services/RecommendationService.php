<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Trail;
use App\Models\User;

class RecommendationService
{
    private const TIER3_VERY_HARD_UNLOCK_COMPLETED_HIKES = 3;

    public function __construct(
        private WeatherService $weatherService,
        private TopsisService $topsisService
    ) {
    }

    private array $difficultyMap = [
        'mudah' => 1,
        'sedang' => 2,
        'sulit' => 3,
        'sangat_sulit' => 4,
    ];

    private array $tierMap = [
        'pemula' => 1,
        'menengah' => 2,
        'mahir' => 3,
    ];

    private array $tierDifficultyPolicy = [
        1 => [
            'preferred' => ['mudah'],
            'allowed' => ['mudah', 'sedang'],
        ],
        2 => [
            'preferred' => ['sedang'],
            'allowed' => ['sedang'],
        ],
        3 => [
            'preferred' => ['sulit', 'sangat_sulit'],
            'allowed' => ['sulit', 'sangat_sulit'],
        ],
    ];

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRecommendations(?int $limit = null, ?User $user = null): array
    {
        $routes = Trail::with('mountain')->get();

        if ($routes->isEmpty()) {
            return [];
        }

        $userTierLevel = $this->resolveUserTierLevel($user);

        if ($userTierLevel === null) {
            return [];
        }

        $completedHikesCount = $this->resolveCompletedHikesCount($user);

        $allAlternatives = [];

        foreach ($routes as $route) {
            $coordinates = $this->resolveCoordinates($route);
            $weatherPayload = $this->weatherService->getRouteWeatherScore($coordinates['lat'], $coordinates['lng']);
            $weatherScore = (float) ($weatherPayload['weather_score'] ?? 0.6);

            $difficultyLabel = strtolower(trim((string) ($route->tingkat_kesulitan ?? 'mudah')));
            $difficultyValue = (float) ($this->difficultyMap[$difficultyLabel] ?? 4);
            $overallDifficultyScore = $this->calculateOverallDifficultyScore($route, $difficultyValue);
            $overallDifficultyLabel = $this->resolveOverallDifficultyLabel($overallDifficultyScore);
            $overallDifficultyLevel = $this->difficultyMap[$overallDifficultyLabel] ?? (int) round($difficultyValue);
            $riskLabel = $this->resolveRiskLabel($overallDifficultyLevel, $userTierLevel);

            $allAlternatives[] = [
                'route_id' => (int) $route->id,
                'route_name' => (string) $route->nama,
                'mountain_name' => (string) ($route->mountain->nama ?? '-'),
                'risk' => $riskLabel,
                'risk_priority' => $this->riskPriority($riskLabel),
                'overall_difficulty' => $overallDifficultyLabel,
                'overall_difficulty_score' => $overallDifficultyScore,
                'criteria' => [
                    'distance' => max(0.0, (float) ($route->jarak ?? 0.0)),
                    'elevation' => max(0.0, (float) ($route->elevasi ?? 0.0)),
                    'duration' => max(0.0, (float) ($route->durasi ?? 0.0)),
                    'difficulty' => $overallDifficultyScore,
                    'cost' => max(0.0, (float) ($route->biaya ?? 0.0)),
                    // C6 TOPSIS benefit criterion in range 0..1.
                    'weather' => round(min(1.0, max(0.0, $weatherScore)), 4),
                ],
            ];
        }

        $alternatives = $this->selectAlternativesByTierPolicy($allAlternatives, $userTierLevel, $completedHikesCount);
        if (empty($alternatives)) {
            return [];
        }

        $ranked = $this->topsisService->rank($alternatives);

        $metaByRouteId = [];
        foreach ($alternatives as $alternative) {
            $metaByRouteId[(int) $alternative['route_id']] = [
                'risk' => $alternative['risk'] ?? null,
                'risk_priority' => $alternative['risk_priority'] ?? null,
            ];
        }

        $enriched = [];
        foreach ($ranked as $item) {
            $routeId = (int) ($item['route_id'] ?? 0);
            $meta = $metaByRouteId[$routeId] ?? [];

            $risk = (string) ($meta['risk'] ?? '');
            if ($risk === '') {
                $risk = 'HIGH';
            }

            $item['risk'] = $risk;
            $item['risk_priority'] = $meta['risk_priority'] ?? $this->riskPriority($risk);

            $enriched[] = $item;
        }

        usort($enriched, function (array $a, array $b) {
            $aPriority = (int) ($a['risk_priority'] ?? 3);
            $bPriority = (int) ($b['risk_priority'] ?? 3);

            if ($aPriority !== $bPriority) {
                return $aPriority <=> $bPriority;
            }

            return ((float) ($b['score'] ?? 0.0)) <=> ((float) ($a['score'] ?? 0.0));
        });

        foreach ($enriched as $index => &$item) {
            $item['rank'] = $index + 1;
            unset($item['risk_priority']);
        }
        unset($item);

        if ($limit !== null && $limit > 0) {
            return array_values(array_slice($enriched, 0, $limit));
        }

        return $enriched;
    }

    private function resolveUserTierLevel(?User $user): ?int
    {
        if (!$user || (int) ($user->level ?? 0) !== 1) {
            return null;
        }

        $tier = strtolower(trim((string) ($user->tier ?? '')));
        if ($tier === '' || !array_key_exists($tier, $this->tierMap)) {
            return null;
        }

        return $this->tierMap[$tier];
    }

    private function resolveCompletedHikesCount(?User $user): int
    {
        if (!$user) {
            return 0;
        }

        return Order::where('id_user', $user->id)
            ->where('status', 'Selesai')
            ->count();
    }

    private function calculateOverallDifficultyScore(Trail $route, float $difficultyValue): float
    {
        $distance = max(0.0, (float) ($route->jarak ?? 0.0));
        $elevation = max(0.0, (float) ($route->elevasi ?? 0.0));
        $duration = max(0.0, (float) ($route->durasi ?? 0.0));

        $distanceNorm = min(1.0, $distance / 12.0);
        $elevationNorm = min(1.0, $elevation / 1700.0);
        $durationNorm = min(1.0, $duration / 11.0);

        // Mengubah demand jalur ke skala 1..4 agar sebanding dengan tingkat_kesulitan.
        $routeDemandScore = (($distanceNorm * 0.3) + ($elevationNorm * 0.4) + ($durationNorm * 0.3)) * 4.0;

        return round(($difficultyValue * 0.65) + ($routeDemandScore * 0.35), 4);
    }

    private function resolveOverallDifficultyLabel(float $overallDifficultyScore): string
    {
        if ($overallDifficultyScore <= 1.75) {
            return 'mudah';
        }

        if ($overallDifficultyScore <= 2.5) {
            return 'sedang';
        }

        if ($overallDifficultyScore <= 3.25) {
            return 'sulit';
        }

        return 'sangat_sulit';
    }

    /**
     * @param array<int, array<string, mixed>> $alternatives
     * @return array<int, array<string, mixed>>
     */
    private function selectAlternativesByTierPolicy(array $alternatives, int $userTierLevel, int $completedHikesCount = 0): array
    {
        $policy = $this->tierDifficultyPolicy[$userTierLevel] ?? [
            'preferred' => ['sedang'],
            'allowed' => ['sedang'],
        ];

        if ($userTierLevel === 3 && $completedHikesCount < self::TIER3_VERY_HARD_UNLOCK_COMPLETED_HIKES) {
            $policy = [
                'preferred' => ['sulit'],
                'allowed' => ['sulit'],
            ];
        }

        $preferred = array_values(array_filter($alternatives, function (array $alternative) use ($policy) {
            $label = (string) ($alternative['overall_difficulty'] ?? '');
            return in_array($label, $policy['preferred'], true);
        }));

        if (!empty($preferred)) {
            return $preferred;
        }

        return array_values(array_filter($alternatives, function (array $alternative) use ($policy) {
            $label = (string) ($alternative['overall_difficulty'] ?? '');
            return in_array($label, $policy['allowed'], true);
        }));
    }

    private function resolveRiskLabel(int $overallDifficultyLevel, int $userTierLevel): string
    {
        $riskGap = $overallDifficultyLevel - $userTierLevel;

        if ($riskGap >= 2) {
            return 'HIGH';
        }

        if ($riskGap === 1) {
            return 'MEDIUM';
        }

        return 'SAFE';
    }

    private function riskPriority(string $risk): int
    {
        $value = strtoupper(trim($risk));

        if ($value === 'SAFE' || $value === 'LOW') {
            return 1;
        }

        if ($value === 'MEDIUM' || $value === 'CAUTION') {
            return 2;
        }

        return 3;
    }

    private function resolveCoordinates(Trail $route): array
    {
        $lat = (float) ($route->latitude ?? 0.0);
        $lng = (float) ($route->longitude ?? 0.0);

        if ($lat !== 0.0 && $lng !== 0.0) {
            return ['lat' => $lat, 'lng' => $lng];
        }

        return [
            'lat' => (float) ($route->mountain->latitude ?? -7.2420),
            'lng' => (float) ($route->mountain->longitude ?? 109.2080),
        ];
    }
}
