<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WeatherService
{
    public function getCurrentWeather(float $lat, float $lng): array
    {
        [$normalizedLat, $normalizedLng] = $this->normalizeCoordinates($lat, $lng);
        $cacheKey = sprintf('weather:current:v2:%s:%s', $normalizedLat, $normalizedLng);

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($normalizedLat, $normalizedLng) {
            $openWeatherKey = config('services.openweather.key');
            if (!empty($openWeatherKey)) {
                $owResult = $this->fetchOpenWeatherCurrent($normalizedLat, $normalizedLng, $openWeatherKey);
                if ($owResult !== null) {
                    return $owResult;
                }
            }

            return $this->fetchOpenMeteoCurrent($normalizedLat, $normalizedLng);
        });
    }

    public function fetchOpenWeatherCurrent(float $lat, float $lng, ?string $apiKey = null): ?array
    {
        $key = $apiKey ?? config('services.openweather.key');
        if (empty($key)) {
            return null;
        }

        try {
            $baseUrl = config('services.openweather.base_url', 'https://api.openweathermap.org/data/2.5');
            $units = config('services.openweather.units', 'metric');

            $response = Http::timeout(4)->get("{$baseUrl}/weather", [
                'lat' => $lat,
                'lon' => $lng,
                'appid' => $key,
                'units' => $units,
            ]);

            if (!$response->ok()) {
                return null;
            }

            $data = (array) $response->json();
            $owId = (int) ($data['weather'][0]['id'] ?? 800);
            $weatherCode = $this->mapOpenWeatherIdToWmoCode($owId);
            $temperature = $this->toNullableFloat($data['main']['temp'] ?? null);

            // OpenWeather wind speed in metric is m/s. Convert to km/h for consistency
            $windSpeedMs = $this->toNullableFloat($data['wind']['speed'] ?? null) ?? 0.0;
            $windSpeedKmH = $windSpeedMs * 3.6;

            $precipProb = null;
            if (isset($data['pop'])) {
                $precipProb = (float) ($data['pop'] * 100);
            } elseif (isset($data['rain']['1h'])) {
                $precipProb = min(100.0, (float) ($data['rain']['1h'] * 20));
            }

            $weatherScore = $this->mapWeatherCodeToScore($weatherCode);
            $windScore = $this->mapWindSpeedToScore($windSpeedKmH);
            $weatherScoreFinal = round(($weatherScore * 0.7) + ($windScore * 0.3), 4);

            return [
                'code' => $weatherCode,
                'condition' => $this->mapWeatherCodeToCondition($weatherCode),
                'temperature' => $temperature,
                'wind_speed' => round($windSpeedKmH, 2),
                'precipitation_probability' => $precipProb,
                'weather_score' => $weatherScore,
                'wind_score' => $windScore,
                'weather_score_final' => $weatherScoreFinal,
                'source' => 'openweather',
            ];
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }

    public function fetchOpenMeteoCurrent(float $lat, float $lng): array
    {
        try {
            $response = Http::timeout(3)->get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => $lat,
                'longitude' => $lng,
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
        } catch (\Throwable $e) {
            report($e);
            return $this->fallbackWeather();
        }
    }

    public function getForecast(float $lat, float $lng): array
    {
        [$normalizedLat, $normalizedLng] = $this->normalizeCoordinates($lat, $lng);
        $cacheKey = sprintf('weather:forecast:%s:%s', $normalizedLat, $normalizedLng);

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($normalizedLat, $normalizedLng) {
            $openWeatherKey = config('services.openweather.key');
            if (!empty($openWeatherKey)) {
                $owForecast = $this->fetchOpenWeatherForecast($normalizedLat, $normalizedLng, $openWeatherKey);
                if ($owForecast !== null) {
                    return $owForecast;
                }
            }

            return $this->fetchOpenMeteoForecast($normalizedLat, $normalizedLng);
        });
    }

    public function fetchOpenWeatherForecast(float $lat, float $lng, ?string $apiKey = null): ?array
    {
        $key = $apiKey ?? config('services.openweather.key');
        if (empty($key)) {
            return null;
        }

        try {
            $baseUrl = config('services.openweather.base_url', 'https://api.openweathermap.org/data/2.5');
            $units = config('services.openweather.units', 'metric');

            $response = Http::timeout(5)->get("{$baseUrl}/forecast", [
                'lat' => $lat,
                'lon' => $lng,
                'appid' => $key,
                'units' => $units,
            ]);

            if (!$response->ok()) {
                return null;
            }

            $data = (array) $response->json();
            $list = (array) ($data['list'] ?? []);

            $hourlyTime = [];
            $hourlyTemp = [];
            $hourlyCode = [];
            $hourlyHumidity = [];
            $hourlyPrecip = [];
            $hourlyWind = [];

            $dailyMap = [];

            foreach ($list as $item) {
                $dtTxt = $item['dt_txt'] ?? null;
                if (!$dtTxt) continue;

                $isoTime = str_replace(' ', 'T', substr($dtTxt, 0, 16));
                $dateStr = substr($dtTxt, 0, 10);

                $temp = (float) ($item['main']['temp'] ?? 0);
                $tempMin = (float) ($item['main']['temp_min'] ?? $temp);
                $tempMax = (float) ($item['main']['temp_max'] ?? $temp);
                $owId = (int) ($item['weather'][0]['id'] ?? 800);
                $wmoCode = $this->mapOpenWeatherIdToWmoCode($owId);
                $humidity = (int) ($item['main']['humidity'] ?? 0);
                $pop = (int) round(((float) ($item['pop'] ?? 0)) * 100);
                $windSpeedKmH = round(((float) ($item['wind']['speed'] ?? 0)) * 3.6, 2);

                $hourlyTime[] = $isoTime;
                $hourlyTemp[] = $temp;
                $hourlyCode[] = $wmoCode;
                $hourlyHumidity[] = $humidity;
                $hourlyPrecip[] = $pop;
                $hourlyWind[] = $windSpeedKmH;

                if (!isset($dailyMap[$dateStr])) {
                    $dailyMap[$dateStr] = [
                        'max_temp' => $tempMax,
                        'min_temp' => $tempMin,
                        'weather_codes' => [$wmoCode],
                        'precip_max' => $pop,
                    ];
                } else {
                    $dailyMap[$dateStr]['max_temp'] = max($dailyMap[$dateStr]['max_temp'], $tempMax);
                    $dailyMap[$dateStr]['min_temp'] = min($dailyMap[$dateStr]['min_temp'], $tempMin);
                    $dailyMap[$dateStr]['weather_codes'][] = $wmoCode;
                    $dailyMap[$dateStr]['precip_max'] = max($dailyMap[$dateStr]['precip_max'], $pop);
                }
            }

            $dailyTime = [];
            $dailyMaxTemp = [];
            $dailyMinTemp = [];
            $dailyCode = [];
            $dailyPrecipMax = [];
            $sunriseList = [];
            $sunsetList = [];

            $citySunrise = isset($data['city']['sunrise']) ? date('Y-m-d\TH:i', $data['city']['sunrise']) : null;
            $citySunset = isset($data['city']['sunset']) ? date('Y-m-d\TH:i', $data['city']['sunset']) : null;

            foreach ($dailyMap as $dateStr => $dayInfo) {
                $dailyTime[] = $dateStr;
                $dailyMaxTemp[] = round($dayInfo['max_temp'], 1);
                $dailyMinTemp[] = round($dayInfo['min_temp'], 1);
                $dailyCode[] = $dayInfo['weather_codes'][0] ?? 0;
                $dailyPrecipMax[] = $dayInfo['precip_max'];
                $sunriseList[] = $citySunrise ? "{$dateStr}T" . substr($citySunrise, 11) : null;
                $sunsetList[] = $citySunset ? "{$dateStr}T" . substr($citySunset, 11) : null;
            }

            return [
                'daily' => [
                    'time' => $dailyTime,
                    'temperature_2m_max' => $dailyMaxTemp,
                    'temperature_2m_min' => $dailyMinTemp,
                    'weather_code' => $dailyCode,
                    'precipitation_probability_max' => $dailyPrecipMax,
                    'sunrise' => $sunriseList,
                    'sunset' => $sunsetList,
                ],
                'hourly' => [
                    'time' => $hourlyTime,
                    'temperature_2m' => $hourlyTemp,
                    'weather_code' => $hourlyCode,
                    'relative_humidity_2m' => $hourlyHumidity,
                    'precipitation_probability' => $hourlyPrecip,
                    'wind_speed_10m' => $hourlyWind,
                ],
                'source' => 'openweather',
            ];
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }

    public function fetchOpenMeteoForecast(float $lat, float $lng): array
    {
        try {
            $response = Http::timeout(10)->get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => $lat,
                'longitude' => $lng,
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
        } catch (\Throwable $e) {
            report($e);
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
    }

    public function prefetchWeather(array $coordinatesList): void
    {
        $uncached = [];
        $keys = [];

        foreach ($coordinatesList as $coords) {
            if (!isset($coords[0]) || !isset($coords[1])) {
                continue;
            }
            [$normalizedLat, $normalizedLng] = $this->normalizeCoordinates((float) $coords[0], (float) $coords[1]);
            $cacheKey = sprintf('weather:current:v2:%s:%s', $normalizedLat, $normalizedLng);

            if (!Cache::has($cacheKey)) {
                // Prevent duplicate coordinates in the batch request
                $coordsKey = "$normalizedLat,$normalizedLng";
                if (!isset($uncached[$coordsKey])) {
                    $uncached[$coordsKey] = [$normalizedLat, $normalizedLng];
                    $keys[$coordsKey] = $cacheKey;
                }
            }
        }

        if (empty($uncached)) {
            return;
        }

        // Fetch concurrently
        $responses = Http::pool(function (\Illuminate\Http\Client\Pool $pool) use ($uncached) {
            $poolCalls = [];
            foreach ($uncached as $coords) {
                $poolCalls[] = $pool->timeout(3)->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => $coords[0],
                    'longitude' => $coords[1],
                    'current_weather' => true,
                    'current' => 'weather_code,temperature_2m,wind_speed_10m',
                    'hourly' => 'precipitation_probability',
                    'forecast_days' => 1,
                    'timezone' => 'auto',
                ]);
            }
            return $poolCalls;
        });

        // Parse and cache responses
        $responses = array_values($responses);
        $keys = array_values($keys);
        
        foreach ($responses as $index => $response) {
            if (!isset($keys[$index])) {
                continue;
            }
            $cacheKey = $keys[$index];

            try {
                if (!$response->ok()) {
                    Cache::put($cacheKey, $this->fallbackWeather(), now()->addMinutes(15));
                    continue;
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

                $data = [
                    'code' => $weatherCode,
                    'condition' => $this->mapWeatherCodeToCondition($weatherCode),
                    'temperature' => $temperature,
                    'wind_speed' => round($windSpeed, 2),
                    'precipitation_probability' => $precipitationProbability,
                    'weather_score' => $weatherScore,
                    'wind_score' => $windScore,
                    'weather_score_final' => $weatherScoreFinal,
                ];

                Cache::put($cacheKey, $data, now()->addMinutes(15));
            } catch (\Throwable $e) {
                report($e);
                Cache::put($cacheKey, $this->fallbackWeather(), now()->addMinutes(15));
            }
        }
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
            'code' => 1,
            'condition' => 'Partly Cloudy',
            'temperature' => 15.0,
            'wind_speed' => 5.0,
            'precipitation_probability' => 0.0,
            'weather_score' => 2,
            'wind_score' => 1,
            'weather_score_final' => 1.7,
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
        return [round($lat, 2), round($lng, 2)];
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

    private function mapOpenWeatherIdToWmoCode(int $id): int
    {
        if ($id >= 200 && $id <= 232) {
            return 95; // Thunderstorm
        }
        if ($id >= 300 && $id <= 321) {
            return 51; // Drizzle
        }
        if ($id >= 500 && $id <= 531) {
            return 61; // Rain
        }
        if ($id >= 600 && $id <= 622) {
            return 71; // Snow
        }
        if ($id >= 701 && $id <= 781) {
            return 45; // Atmosphere / Fog
        }
        if ($id === 800) {
            return 0; // Clear
        }
        if ($id === 801 || $id === 802) {
            return 1; // Few / Scattered Clouds
        }
        if ($id === 803 || $id === 804) {
            return 3; // Broken / Overcast Clouds
        }

        return 0;
    }
}
