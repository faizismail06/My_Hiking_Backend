<?php

namespace App\Http\Controllers;

use App\Services\RecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    public function index(Request $request, RecommendationService $recommendationService): JsonResponse
    {
        $limit = $request->integer('limit');
        $limit = $limit > 0 ? $limit : null;

        $recommendations = $recommendationService->getRecommendations($limit);

        return response()->json([
            'recommendations' => $recommendations,
        ]);
    }
}
