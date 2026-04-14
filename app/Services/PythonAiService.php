<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PythonAiService
{
    /**
     * Example call to Python weather prediction service.
     * Expected Python response: {"weather_score": 0.74}
     */
    public function predictWeather(array $payload): ?array
    {
        $response = $this->client()->post('/ml/predict-weather', $payload);

        if (!$response->ok()) {
            return null;
        }

        $json = $response->json();
        if (!is_array($json)) {
            return null;
        }

        return [
            'weather_score' => isset($json['weather_score']) ? (float) $json['weather_score'] : null,
            'raw' => $json,
        ];
    }

    /**
     * Future model: K-Means hiker clustering.
     */
    public function clusterHikers(array $payload): ?array
    {
        $response = $this->client()->post('/ml/cluster-hikers', $payload);

        if (!$response->ok()) {
            return null;
        }

        $json = $response->json();

        return is_array($json) ? $json : null;
    }

    /**
     * Future model: Decision Tree recommendation for difficulty.
     */
    public function recommendDifficulty(array $payload): ?array
    {
        $response = $this->client()->post('/ml/recommend-difficulty', $payload);

        if (!$response->ok()) {
            return null;
        }

        $json = $response->json();

        return is_array($json) ? $json : null;
    }

    private function client()
    {
        $baseUrl = (string) config('services.python_ai.base_url', 'http://127.0.0.1:8001');
        $token = (string) config('services.python_ai.token', '');
        $timeout = (int) config('services.python_ai.timeout', 10);

        $request = Http::baseUrl(rtrim($baseUrl, '/'))
            ->acceptJson()
            ->asJson()
            ->timeout($timeout)
            ->retry(2, 200);

        if ($token !== '') {
            $request = $request->withToken($token);
        }

        return $request;
    }
}
