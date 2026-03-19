<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Mountain;
use App\Services\DSSService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MountainController extends Controller
{
    public function index()
    {
        $mountainList = Mountain::with('province')->get();

        // Format response
        $result = $mountainList->map(function ($mountain) {
            $imageUrl = url('api/images/' . $mountain->gambar_gunung);

            return [
                'id' => $mountain->id,
                'nama' => $mountain->nama,
                'gambar' => $imageUrl,
                'province' => $mountain->province ? ['id' => $mountain->province->id, 'name' => $mountain->province->name] : null,
            ];
        });

        return response()->json($result);
    }

    public function homeFeed(Request $request, DSSService $dssService)
    {
        $mountains = Mountain::with(['province', 'trails'])->get();
        $user = Auth::guard('sanctum')->user();

        $recommendedPayload = null;
        $recommendedMountainId = null;

        if ($user && (int) $user->level === 1 && !empty($user->tier)) {
            $rankedCandidates = [];

            foreach ($mountains as $mountain) {
                if ($mountain->trails->isEmpty()) {
                    continue;
                }

                $bestTrail = null;
                $bestEvaluation = null;

                foreach ($mountain->trails as $trail) {
                    $evaluation = $dssService->evaluateRoute($user, $trail);

                    if ($bestEvaluation === null || ($evaluation['final_score'] ?? INF) < ($bestEvaluation['final_score'] ?? INF)) {
                        $bestEvaluation = $evaluation;
                        $bestTrail = $trail;
                    }
                }

                if ($bestEvaluation !== null) {
                    $rankedCandidates[] = [
                        'mountain' => $mountain,
                        'trail' => $bestTrail,
                        'dss' => $bestEvaluation,
                    ];
                }
            }

            if (!empty($rankedCandidates)) {
                usort($rankedCandidates, function ($a, $b) {
                    $riskPriority = [
                        'safe' => 1,
                        'caution' => 2,
                        'high_risk' => 3,
                    ];

                    $aRisk = $riskPriority[$a['dss']['risk_level'] ?? 'high_risk'] ?? 3;
                    $bRisk = $riskPriority[$b['dss']['risk_level'] ?? 'high_risk'] ?? 3;

                    if ($aRisk !== $bRisk) {
                        return $aRisk <=> $bRisk;
                    }

                    $aScore = (float) ($a['dss']['final_score'] ?? INF);
                    $bScore = (float) ($b['dss']['final_score'] ?? INF);

                    return $aScore <=> $bScore;
                });

                $winner = $rankedCandidates[0];
                $recommendedMountainId = $winner['mountain']->id;
                $recommendedPayload = array_merge(
                    $this->formatMountain($winner['mountain']),
                    [
                        'dss' => $winner['dss'],
                        'recommended_trail_id' => $winner['trail']?->id,
                    ]
                );
            }
        }

        $otherMountains = $mountains
            ->filter(fn ($mountain) => $recommendedMountainId === null || $mountain->id !== $recommendedMountainId)
            ->values()
            ->map(fn ($mountain) => $this->formatMountain($mountain))
            ->all();

        return response()->json([
            'recommended' => $recommendedPayload,
            'mountains' => $otherMountains,
        ]);
    }

    private function formatMountain(Mountain $mountain): array
    {
        return [
            'id' => $mountain->id,
            'nama' => $mountain->nama,
            'gambar' => url('api/images/' . $mountain->gambar_gunung),
            'province' => $mountain->province
                ? ['id' => $mountain->province->id, 'name' => $mountain->province->name]
                : null,
        ];
    }
}
