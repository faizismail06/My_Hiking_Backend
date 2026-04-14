<?php

namespace App\Services;

use App\Models\Trail;

class RecommendationService
{
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

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRecommendations(?int $limit = null): array
    {
        $routes = Trail::with('mountain')->get();

        if ($routes->isEmpty()) {
            return [];
        }

        $alternatives = [];

        foreach ($routes as $route) {
            $coordinates = $this->resolveCoordinates($route);
            $weatherPayload = $this->weatherService->getRouteWeatherScore($coordinates['lat'], $coordinates['lng']);
            $weatherScore = (float) ($weatherPayload['weather_score'] ?? 0.6);

            $difficultyLabel = strtolower(trim((string) ($route->tingkat_kesulitan ?? 'mudah')));
            $difficultyValue = (float) ($this->difficultyMap[$difficultyLabel] ?? 4);

            $alternatives[] = [
                'route_id' => (int) $route->id,
                'route_name' => (string) $route->nama,
                'mountain_name' => (string) ($route->mountain->nama ?? '-'),
                'criteria' => [
                    'distance' => max(0.0, (float) ($route->jarak ?? 0.0)),
                    'elevation' => max(0.0, (float) ($route->elevasi ?? 0.0)),
                    'duration' => max(0.0, (float) ($route->durasi ?? 0.0)),
                    'difficulty' => $difficultyValue,
                    'cost' => max(0.0, (float) ($route->biaya ?? 0.0)),
                    // C6 TOPSIS benefit criterion in range 0..1.
                    'weather' => round(min(1.0, max(0.0, $weatherScore)), 4),
                ],
            ];
        }

        $ranked = $this->topsisService->rank($alternatives);

        if ($limit !== null && $limit > 0) {
            return array_values(array_slice($ranked, 0, $limit));
        }

        return $ranked;
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
