<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TierService;
use App\Services\UserActionReadinessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExperienceOnboardingController extends Controller
{
    public function __construct(
        private TierService $tierService,
        private UserActionReadinessService $readinessService
    ) {
    }

    public function status(Request $request)
    {
        $user = $request->user();

        $missingIdentity = $this->readinessService->missingIdentityFields($user);
        $needsExperience = $this->readinessService->needsTierOnboarding($user);

        return response()->json([
            'success' => true,
            'message' => 'Onboarding status fetched successfully.',
            'data' => [
                'is_hiker' => (int) $user->level === 1,
                'identity_complete' => empty($missingIdentity),
                'missing_identity_fields' => $missingIdentity,
                'experience_completed' => !$needsExperience,
                'tier' => $user->tier,
                'tier_source' => $user->tier_source,
                'experience' => $user->experience,
            ],
        ]);
    }

    /**
     * Simpan data self-assessment pengalaman pendaki (sekali isi).
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if ((int) $user->level !== 1) {
            return response()->json([
                'success' => false,
                'message' => 'Onboarding experience hanya untuk user pendaki (level 1).',
            ], 403);
        }

        if (!$this->readinessService->isIdentityComplete($user)) {
            return response()->json([
                'success' => false,
                'code' => 'PROFILE_INCOMPLETE',
                'message' => 'Lengkapi data identitas terlebih dahulu sebelum isi pengalaman.',
                'missing_fields' => $this->readinessService->missingIdentityFields($user),
            ], 409);
        }

        if ($user->experience) {
            return response()->json([
                'success' => false,
                'code' => 'EXPERIENCE_LOCKED',
                'message' => 'Data pengalaman hanya dapat diisi sekali dan tidak dapat diubah manual.',
            ], 409);
        }

        $validator = Validator::make($request->all(), [
            'jumlah_pendakian' => 'required|integer|min:0',
            'jumlah_summit' => 'required|integer|min:0|lte:jumlah_pendakian',
        ], [
            'jumlah_summit.lte' => 'jumlah_summit tidak boleh lebih besar dari jumlah_pendakian.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'data' => $validator->errors(),
            ], 422);
        }

        $user = $this->tierService->createSelfClaimExperience(
            $user,
            (int) $request->jumlah_pendakian,
            (int) $request->jumlah_summit
        );

        return response()->json([
            'success' => true,
            'message' => 'Experience onboarding berhasil disimpan.',
            'data' => [
                'tier' => $user->tier,
                'tier_source' => $user->tier_source,
                'experience' => $user->experience,
            ],
        ], 201);
    }
}
