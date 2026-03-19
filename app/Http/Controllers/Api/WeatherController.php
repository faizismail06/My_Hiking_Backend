<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WeatherService;
use Illuminate\Http\Request;

class WeatherController extends Controller
{
    public function __construct(private WeatherService $weatherService)
    {
    }

    public function current(Request $request)
    {
        $validated = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $weather = $this->weatherService->getCurrentWeather(
            (float) $validated['lat'],
            (float) $validated['lng']
        );

        // Keep payload shape compatible with existing Flutter weather model.
        return response()->json([
            'success' => true,
            'data' => [
                'current' => [
                    'temperature_2m' => $weather['temperature'],
                    'weather_code' => $weather['code'],
                    'wind_speed_10m' => $weather['wind_speed'],
                    'precipitation_probability' => $weather['precipitation_probability'],
                ],
            ],
        ]);
    }

    public function forecast(Request $request)
    {
        $validated = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $forecast = $this->weatherService->getForecast(
            (float) $validated['lat'],
            (float) $validated['lng']
        );

        return response()->json([
            'success' => true,
            'data' => $forecast,
        ]);
    }
}
