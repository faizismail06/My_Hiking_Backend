<?php

namespace App\Services;

use App\Models\Trail;
use App\Models\User;

class DSSService
{
    public function __construct(private WeatherService $weatherService)
    {
    }

    private array $tierMap = [
        'pemula' => 1,
        'menengah' => 2,
        'mahir' => 3,
    ];

    private array $difficultyMap = [
        'mudah' => 1,
        'sedang' => 2,
        'sulit' => 3,
        'sangat_sulit' => 4,
    ];

    public function evaluateRoute(User $user, Trail $route): array
    {
        $tierKey = $this->normalizeTier((string) ($user->tier ?? 'pemula'));
        $userTier = $this->tierMap[$tierKey] ?? 1;

        $metrics = $this->calculateRouteScore($route);

        $coordinates = $this->resolveCoordinates($route);
        $weather = $this->weatherService->getCurrentWeather($coordinates['lat'], $coordinates['lng']);

        $difficultyKey = $this->resolveDifficultyKey($route, $metrics['route_score']);
        $difficultyLevel = $this->difficultyMap[$difficultyKey];

        $riskGap = $difficultyLevel - $userTier;
        $finalScore = round(($metrics['route_score'] * 0.75) + (($weather['weather_score_final'] ?? 0) * 0.25), 4);

        $riskLevel = $this->resolveRiskLevel($riskGap, $finalScore, $userTier);
        $recommendation = $riskLevel === 'high_risk' ? 'not_recommended' : 'recommended';
        $reasoning = $this->buildReasoning($weather, $riskGap);

        if (empty($reasoning)) {
            $reasoning[] = 'Kondisi cuaca relatif stabil.';
        }

        return [
            'risk_level' => $riskLevel,
            'recommendation' => $recommendation,
            'message' => $this->buildMessage($riskLevel, $tierKey, $difficultyKey, $finalScore),
            'route_score' => $metrics['route_score'],
            'weather_score_final' => $weather['weather_score_final'],
            'final_score' => $finalScore,
            'risk_gap' => $riskGap,
            'weather' => [
                'code' => $weather['code'],
                'condition' => $weather['condition'],
                'temperature' => $weather['temperature'],
                'wind_speed' => $weather['wind_speed'],
                'precipitation_probability' => $weather['precipitation_probability'],
            ],
            'reasoning' => $reasoning,
            'user_tier' => [
                'label' => $tierKey,
                'level' => $userTier,
                'source' => $user->tier_source,
            ],
            'difficulty' => [
                'label' => $difficultyKey,
                'level' => $difficultyLevel,
            ],
            'metrics' => [
                'jarak_km' => $metrics['jarak_km'],
                'elevasi_meter' => $metrics['elevasi_meter'],
                'durasi_jam' => $metrics['durasi_jam'],
                'jarak_norm' => $metrics['jarak_norm'],
                'elevasi_norm' => $metrics['elevasi_norm'],
                'durasi_norm' => $metrics['durasi_norm'],
                'weather_score' => $weather['weather_score'],
                'wind_score' => $weather['wind_score'],
            ],
        ];
    }

    private function resolveCoordinates(Trail $route): array
    {
        $lat = (float) ($route->latitude ?? 0);
        $lng = (float) ($route->longitude ?? 0);

        if ($lat !== 0.0 && $lng !== 0.0) {
            return ['lat' => $lat, 'lng' => $lng];
        }

        $mountain = $route->relationLoaded('mountain') ? $route->mountain : $route->mountain()->first();

        return [
            'lat' => (float) ($mountain?->latitude ?? -7.242),
            'lng' => (float) ($mountain?->longitude ?? 109.208),
        ];
    }

    private function normalizeTier(string $tier): string
    {
        $tier = strtolower(trim($tier));

        return array_key_exists($tier, $this->tierMap) ? $tier : 'pemula';
    }

    private function resolveDifficultyKey(Trail $route, float $routeScore): string
    {
        $difficulty = strtolower(trim((string) ($route->tingkat_kesulitan ?? '')));
        if (array_key_exists($difficulty, $this->difficultyMap)) {
            return $difficulty;
        }

        // Fallback jika tingkat_kesulitan belum tersedia: estimasi dari route_score.
        if ($routeScore <= 0.8) {
            return 'mudah';
        }

        if ($routeScore <= 1.6) {
            return 'sedang';
        }

        if ($routeScore <= 2.4) {
            return 'sulit';
        }

        return 'sangat_sulit';
    }

    private function calculateRouteScore(Trail $route): array
    {
        $jarakKm = max(0.0, (float) ($route->jarak ?? 0));
        $elevasiMeter = max(0.0, (float) ($route->elevasi ?? 0));
        $durasiJam = max(0.0, (float) ($route->durasi ?? 0));

        $jarakNorm = $jarakKm / 10;
        $elevasiNorm = $elevasiMeter / 1000;
        $durasiNorm = $durasiJam / 10;

        $score = ($jarakNorm * 0.3) + ($elevasiNorm * 0.4) + ($durasiNorm * 0.3);

        return [
            'jarak_km' => round($jarakKm, 2),
            'elevasi_meter' => round($elevasiMeter, 2),
            'durasi_jam' => round($durasiJam, 2),
            'jarak_norm' => round($jarakNorm, 4),
            'elevasi_norm' => round($elevasiNorm, 4),
            'durasi_norm' => round($durasiNorm, 4),
            'route_score' => round($score, 4),
        ];
    }

    private function resolveRiskLevel(int $riskGap, float $finalScore, int $userTier): string
    {
        $cautionThresholdByTier = [
            1 => 1.6,
            2 => 2.0,
            3 => 2.4,
        ];

        $highThresholdByTier = [
            1 => 2.3,
            2 => 2.7,
            3 => 3.1,
        ];

        $cautionThreshold = $cautionThresholdByTier[$userTier] ?? 2.0;
        $highThreshold = $highThresholdByTier[$userTier] ?? 2.7;

        if ($riskGap >= 2) {
            return 'high_risk';
        }

        if ($riskGap === 1) {
            return $finalScore >= $highThreshold ? 'high_risk' : 'caution';
        }

        if ($finalScore >= ($highThreshold + 0.3)) {
            return 'high_risk';
        }

        if ($finalScore >= $cautionThreshold) {
            return 'caution';
        }

        return 'safe';
    }

    private function buildReasoning(array $weather, int $riskGap): array
    {
        $reasoning = [];

        $condition = strtolower((string) ($weather['condition'] ?? ''));
        if ($condition === 'rain') {
            $reasoning[] = 'Cuaca hujan.';
        } elseif ($condition === 'thunderstorm') {
            $reasoning[] = 'Ada potensi badai petir.';
        } elseif ($condition === 'fog') {
            $reasoning[] = 'Kabut dapat mengurangi visibilitas.';
        }

        $windScore = (int) ($weather['wind_score'] ?? 1);
        if ($windScore === 2) {
            $reasoning[] = 'Angin cukup kencang.';
        } elseif ($windScore >= 3) {
            $reasoning[] = 'Angin kencang meningkatkan risiko pendakian.';
        }

        if ($riskGap > 0) {
            $reasoning[] = 'Jalur lebih sulit dari pengalaman Anda.';
        }

        return $reasoning;
    }

    private function buildMessage(string $riskLevel, string $tierKey, string $difficultyKey, float $finalScore): string
    {
        $tierText = ucfirst(str_replace('_', ' ', $tierKey));
        $difficultyText = ucfirst(str_replace('_', ' ', $difficultyKey));

        if ($riskLevel === 'safe') {
            return "Rute {$difficultyText} sesuai dengan tier {$tierText}. Skor akhir {$finalScore}.";
        }

        if ($riskLevel === 'caution') {
            return "Rute {$difficultyText} sedikit di atas tier {$tierText}. Disarankan persiapan ekstra. Skor akhir {$finalScore}.";
        }

        return "Rute {$difficultyText} berisiko tinggi untuk tier {$tierText}. Tidak direkomendasikan tanpa persiapan matang. Skor akhir {$finalScore}.";
    }
}
