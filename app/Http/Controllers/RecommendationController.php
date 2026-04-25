<?php

namespace App\Http\Controllers;

use App\Services\RecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class RecommendationController extends Controller
{
    /**
     * GET /recommendations
     *
     * Query parameters:
     *   limit                 (int,   optional) – max results to return
     *   max_budget            (float, optional) – filter routes over this cost (Rupiah)
     *   bust_cache            (bool,  optional) – pass "1" to force-refresh cache
     *
     * Priority weights (all optional, positive floats, auto-normalised):
     *   priority_distance
     *   priority_elevation
     *   priority_difficulty
     *   priority_cost
     *   priority_duration
     *   priority_crowd_level
     *   priority_panorama
     *   priority_fasilitas
     *   priority_popularity
     *   priority_safety
     *
     * Cache strategy
     * ──────────────
     * Results are cached per (user_id × tier × weight_fingerprint × budget × limit).
     * TTL: 10 minutes.  Weights are fingerprinted via SHA-256 so different
     * weight combinations each get their own cache slot without exposing raw
     * values in the key string.
     *
     * The cache is automatically invalidated when:
     *   - bust_cache=1 is passed (useful after penjaga updates DSS data)
     *   - The TTL expires
     */
    public function index(Request $request, RecommendationService $recommendationService): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'code'    => 'UNAUTHORIZED',
                'message' => 'Autentikasi dibutuhkan untuk mengambil rekomendasi.',
            ], 401);
        }

        if ((int) $user->level !== 1) {
            return response()->json([
                'success' => false,
                'code'    => 'HIKER_ONLY',
                'message' => 'Rekomendasi DSS hanya tersedia untuk user pendaki (level 1).',
            ], 403);
        }

        if (empty($user->tier)) {
            return response()->json([
                'success' => false,
                'code'    => 'EXPERIENCE_ONBOARDING_REQUIRED',
                'message' => 'Tier belum tersedia. Selesaikan onboarding experience terlebih dahulu.',
            ], 409);
        }

        // ── Parse optional parameters ──────────────────────────────────────
        $limit     = $request->integer('limit');
        $limit     = $limit > 0 ? $limit : null;
        $maxBudget = $request->filled('max_budget') ? (float) $request->input('max_budget') : null;
        $bustCache = $request->boolean('bust_cache', false);

        // ── Collect user-supplied priority weights ─────────────────────────
        $weightKeys = [
            'priority_distance',
            'priority_elevation',
            'priority_difficulty',
            'priority_cost',
            'priority_duration',
            'priority_crowd_level',
            'priority_panorama',
            'priority_fasilitas',
            'priority_popularity',
            'priority_safety',
        ];

        $userWeights = [];
        foreach ($weightKeys as $key) {
            if ($request->filled($key)) {
                $value = (float) $request->input($key);
                if ($value >= 0) {
                    $userWeights[$key] = $value;
                }
            }
        }

        // ── Build cache key ────────────────────────────────────────────────
        // Sort weights before hashing so identical weights in different order
        // produce the same key.
        $sortedWeights = $userWeights;
        ksort($sortedWeights);

        $weightFingerprint = hash('sha256', json_encode($sortedWeights));

        $cacheKey = implode('|', [
            'recommendations',
            'u' . $user->id,
            't' . ($user->tier ?? 'none'),
            'w' . substr($weightFingerprint, 0, 16),   // first 16 hex chars is enough
            'b' . (int) ($maxBudget ?? 0),
            'l' . (int) ($limit ?? 0),
        ]);

        // ── Bust cache if requested ────────────────────────────────────────
        if ($bustCache) {
            Cache::forget($cacheKey);
        }

        // ── Serve from cache or compute ────────────────────────────────────
        /** @var array $recommendations */
        $recommendations = Cache::remember(
            $cacheKey,
            now()->addMinutes(10),                     // TTL: 10 minutes
            function () use ($recommendationService, $limit, $user, $userWeights, $maxBudget) {
                return $recommendationService->getRecommendations(
                    $limit,
                    $user,
                    $userWeights,
                    $maxBudget
                );
            }
        );

        return response()->json([
            'success'         => true,
            'total'           => count($recommendations),
            'weights_applied' => !empty($userWeights),
            'cached'          => !$bustCache && Cache::has($cacheKey),
            'recommendations' => $recommendations,
        ]);
    }
}
