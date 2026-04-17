<?php

namespace App\Http\Controllers;

use App\Services\RecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecommendationController extends Controller
{
    public function index(Request $request, RecommendationService $recommendationService): JsonResponse
    {
        $limit = $request->integer('limit');
        $limit = $limit > 0 ? $limit : null;
        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'code' => 'UNAUTHORIZED',
                'message' => 'Autentikasi dibutuhkan untuk mengambil rekomendasi tier-aware.',
            ], 401);
        }

        if ((int) $user->level !== 1) {
            return response()->json([
                'success' => false,
                'code' => 'HIKER_ONLY',
                'message' => 'Rekomendasi DSS hanya tersedia untuk user pendaki (level 1).',
            ], 403);
        }

        if (empty($user->tier)) {
            return response()->json([
                'success' => false,
                'code' => 'EXPERIENCE_ONBOARDING_REQUIRED',
                'message' => 'Tier belum tersedia. Selesaikan onboarding experience terlebih dahulu.',
            ], 409);
        }

        $recommendations = $recommendationService->getRecommendations($limit, $user);

        return response()->json([
            'recommendations' => $recommendations,
        ]);
    }
}
