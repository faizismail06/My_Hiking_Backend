<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PythonAiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiGatewayController extends Controller
{
    public function predictWeather(Request $request, PythonAiService $pythonAiService): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'elevation' => 'nullable|numeric',
            'hour' => 'nullable|integer|min:0|max:23',
        ]);

        $result = $pythonAiService->predictWeather($validated);

        if ($result === null || !isset($result['weather_score'])) {
            return response()->json([
                'success' => false,
                'message' => 'Python weather service unavailable',
            ], 502);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'weather_score' => round((float) $result['weather_score'], 4),
                'source' => 'python-ai-service',
            ],
        ]);
    }
}
