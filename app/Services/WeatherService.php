<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WeatherService
{
    /**
     * Get normalized weather score for TOPSIS criterion C6.
     * Score range: 0.0 - 1.0 (higher is better).
     */
    public function getRouteWeatherScore(float $lat, float $lng): array
    {
        [$normalizedLat, $normalizedLng] = $this->normalizeCoordinates($lat, $lng);
        $cacheKey = sprintf('weather:route-score:v1:%s:%s', $normalizedLat, $normalizedLng);

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($normalizedLat, $normalizedLng) {
            $openWeather = $this->fetchOpenWeatherCurrent($normalizedLat, $normalizedLng);

            if ($openWeather === null) {
                // Fallback to the existing weather pipeline so recommendations still run.
                $legacy = $this->getCurrentWeather($normalizedLat, $normalizedLng);
                $legacyCondition = strtolower((string) ($legacy['condition'] ?? 'unknown'));

                return [
                    'provider' => 'open-meteo-fallback',
                    'condition' => $legacyCondition,
                    'description' => $legacyCondition,
                    'temperature' => $legacy['temperature'] ?? null,
                    'weather_score' => $this->mapOpenWeatherConditionToScore($legacyCondition, $legacyCondition),
                ];
            }

            $weatherMain = strtolower((string) ($openWeather['weather'][0]['main'] ?? 'unknown'));
            $weatherDescription = strtolower((string) ($openWeather['weather'][0]['description'] ?? 'unknown'));
            $temperature = $this->toNullableFloat($openWeather['main']['temp'] ?? null);

            return [
                'provider' => 'openweather',
                'condition' => $weatherMain,
                'description' => $weatherDescription,
                'temperature' => $temperature,
                'weather_score' => $this->mapOpenWeatherConditionToScore($weatherMain, $weatherDescription),
            ];
        });
    }

    public function getCurrentWeather(float $lat, float $lng): array
    {
        [$normalizedLat, $normalizedLng] = $this->normalizeCoordinates($lat, $lng);
        $cacheKey = sprintf('weather:current:v2:%s:%s', $normalizedLat, $normalizedLng);

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($normalizedLat, $normalizedLng) {
            $response = Http::timeout(10)->get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => $normalizedLat,
                'longitude' => $normalizedLng,
                'current_weather' => true,
                'current' => 'weather_code,temperature_2m,wind_speed_10m',
                'hourly' => 'precipitation_probability',
                'forecast_days' => 1,
                'timezone' => 'auto',
            ]);

            if (!$response->ok()) {
                return $this->fallbackWeather();
            }

            $payload = $response->json();
            $current = (array) ($payload['current'] ?? []);
            $legacyCurrent = (array) ($payload['current_weather'] ?? []);

            $weatherCode = (int) ($current['weather_code'] ?? $legacyCurrent['weathercode'] ?? 0);
            $temperature = $this->toNullableFloat($current['temperature_2m'] ?? $legacyCurrent['temperature'] ?? null);
            $windSpeed = $this->toNullableFloat($current['wind_speed_10m'] ?? $legacyCurrent['windspeed'] ?? null) ?? 0.0;
            $precipitationProbability = $this->resolveCurrentPrecipitationProbability($payload);

            $weatherScore = $this->mapWeatherCodeToScore($weatherCode);
            $windScore = $this->mapWindSpeedToScore($windSpeed);
            $weatherScoreFinal = round(($weatherScore * 0.7) + ($windScore * 0.3), 4);

            return [
                'code' => $weatherCode,
                'condition' => $this->mapWeatherCodeToCondition($weatherCode),
                'temperature' => $temperature,
                'wind_speed' => round($windSpeed, 2),
                'precipitation_probability' => $precipitationProbability,
                'weather_score' => $weatherScore,
                'wind_score' => $windScore,
                'weather_score_final' => $weatherScoreFinal,
            ];
        });
    }

    public function getForecast(float $lat, float $lng): array
    {
        [$normalizedLat, $normalizedLng] = $this->normalizeCoordinates($lat, $lng);
        $cacheKey = sprintf('weather:forecast:%s:%s', $normalizedLat, $normalizedLng);

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($normalizedLat, $normalizedLng) {
            $response = Http::timeout(10)->get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => $normalizedLat,
                'longitude' => $normalizedLng,
                'daily' => 'temperature_2m_max,temperature_2m_min,weather_code,precipitation_probability_max,sunrise,sunset',
                'hourly' => 'temperature_2m,weather_code,relative_humidity_2m,precipitation_probability,wind_speed_10m',
                'timezone' => 'auto',
                'forecast_days' => 7,
            ]);

            if (!$response->ok()) {
                return [
                    'daily' => [
                        'time' => [],
                        'temperature_2m_max' => [],
                        'temperature_2m_min' => [],
                        'weather_code' => [],
                        'precipitation_probability_max' => [],
                        'sunrise' => [],
                        'sunset' => [],
                    ],
                    'hourly' => [
                        'time' => [],
                        'temperature_2m' => [],
                        'weather_code' => [],
                        'relative_humidity_2m' => [],
                        'precipitation_probability' => [],
                        'wind_speed_10m' => [],
                    ],
                ];
            }

            return (array) $response->json();
        });
    }

    public function mapWeatherCodeToScore(int $weatherCode): int
    {
        if ($weatherCode === 0) {
            return 1;
        }

        if ($weatherCode >= 1 && $weatherCode <= 3) {
            return 2;
        }

        if ($weatherCode >= 45 && $weatherCode <= 48) {
            return 3;
        }

        if ($weatherCode >= 51 && $weatherCode <= 67) {
            return 3;
        }

        if ($weatherCode >= 71 && $weatherCode <= 77) {
            return 3;
        }

        if ($weatherCode >= 80 && $weatherCode <= 82) {
            return 4;
        }

        if ($weatherCode >= 95 && $weatherCode <= 99) {
            return 5;
        }

        return 3;
    }

    public function mapWindSpeedToScore(float $windSpeed): int
    {
        if ($windSpeed < 10) {
            return 1;
        }

        if ($windSpeed <= 20) {
            return 2;
        }

        return 3;
    }

    public function mapWeatherCodeToCondition(int $weatherCode): string
    {
        if ($weatherCode === 0) {
            return 'Clear';
        }

        if ($weatherCode >= 1 && $weatherCode <= 3) {
            return 'Partly Cloudy';
        }

        if ($weatherCode >= 45 && $weatherCode <= 48) {
            return 'Fog';
        }

        if (($weatherCode >= 51 && $weatherCode <= 67) || ($weatherCode >= 80 && $weatherCode <= 82)) {
            return 'Rain';
        }

        if ($weatherCode >= 71 && $weatherCode <= 77) {
            return 'Snow';
        }

        if ($weatherCode >= 95 && $weatherCode <= 99) {
            return 'Thunderstorm';
        }

        return 'Unknown';
    }

    private function fallbackWeather(): array
    {
        return [
            'code' => 0,
            'condition' => 'Unknown',
            'temperature' => null,
            'wind_speed' => 0.0,
            'precipitation_probability' => null,
            'weather_score' => 3,
            'wind_score' => 1,
            'weather_score_final' => 2.4,
        ];
    }

    private function toNullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }

    private function normalizeCoordinates(float $lat, float $lng): array
    {
        return [round($lat, 4), round($lng, 4)];
    }

    private function fetchOpenWeatherCurrent(float $lat, float $lng): ?array
    {
        $apiKey = (string) config('services.openweather.api_key', '');

        if ($apiKey === '') {
            return null;
        }

        $baseUrl = (string) config('services.openweather.base_url', 'https://api.openweathermap.org/data/2.5');
        $units = (string) config('services.openweather.units', 'metric');

        $response = Http::timeout(10)->get(rtrim($baseUrl, '/') . '/weather', [
            'lat' => $lat,
            'lon' => $lng,
            'appid' => $apiKey,
            'units' => $units,
        ]);

        if (!$response->ok()) {
            return null;
        }

        $payload = $response->json();

        return is_array($payload) ? $payload : null;
    }

    /**
     * Mapping example requested:
     * clear sky -> 1.0
     * few clouds -> 0.8
     * cloudy -> 0.7
     * light rain -> 0.5
     * heavy rain -> 0.2
     */
    private function mapOpenWeatherConditionToScore(string $main, string $description): float
    {
        $main = strtolower(trim($main));
        $description = strtolower(trim($description));

        if ($description === 'clear sky' || $main === 'clear') {
            return 1.0;
        }

        if ($description === 'few clouds') {
            return 0.8;
        }

        if (
            str_contains($description, 'cloud') ||
            $main === 'clouds' ||
            $main === 'mist' ||
            $main === 'fog' ||
            $main === 'haze'
        ) {
            return 0.7;
        }

        if (
            str_contains($description, 'light rain') ||
            str_contains($description, 'drizzle')
        ) {
            return 0.5;
        }

        if (
            str_contains($description, 'heavy rain') ||
            str_contains($description, 'very heavy rain') ||
            str_contains($description, 'extreme rain') ||
            str_contains($description, 'thunderstorm') ||
            str_contains($description, 'shower rain')
        ) {
            return 0.2;
        }

        if (str_contains($description, 'rain') || $main === 'rain') {
            return 0.4;
        }

        return 0.6;
    }

    private function resolveCurrentPrecipitationProbability(array $payload): ?float
    {
        $current = (array) ($payload['current'] ?? []);
        $direct = $this->toNullableFloat($current['precipitation_probability'] ?? null);
        if ($direct !== null) {
            return $direct;
        }

        $hourly = (array) ($payload['hourly'] ?? []);
        $precipList = $hourly['precipitation_probability'] ?? [];
        if (!is_array($precipList) || empty($precipList)) {
            return null;
        }

        return $this->toNullableFloat($precipList[0] ?? null);
    }
}
