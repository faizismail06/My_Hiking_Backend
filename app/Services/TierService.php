<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\UserExperience;
use Illuminate\Support\Facades\DB;

class TierService
{
    private array $tierPriority = [
        'pemula' => 1,
        'menengah' => 2,
        'mahir' => 3,
    ];

    public function determineTierByWeightedScore(int $weightedScore): string
    {
        if ($weightedScore <= 34) {
            return 'pemula';
        }

        if ($weightedScore <= 69) {
            return 'menengah';
        }

        return 'mahir';
    }

    public function determineTierByPendakian(int $jumlahPendakian): string
    {
        if ($jumlahPendakian <= 2) {
            return 'pemula';
        }

        if ($jumlahPendakian <= 6) {
            return 'menengah';
        }

        return 'mahir';
    }

    public function createSelfClaimExperience(
        User $user,
        int $jumlahPendakian,
        int $jumlahSummit,
        ?array $questionnaireAnswers = null,
        ?int $weightedScore = null
    ): User
    {
        return DB::transaction(function () use ($user, $jumlahPendakian, $jumlahSummit, $questionnaireAnswers, $weightedScore) {
            $resolvedWeightedScore = $weightedScore;
            if (!is_null($resolvedWeightedScore)) {
                $resolvedWeightedScore = max(0, min(100, $resolvedWeightedScore));
            }

            $weightedTier = !is_null($resolvedWeightedScore)
                ? $this->determineTierByWeightedScore($resolvedWeightedScore)
                : null;

            $pendakianTier = $this->determineTierByPendakian($jumlahPendakian);
            $resolvedTier = $this->resolveSelfClaimTier($pendakianTier, $weightedTier);

            UserExperience::create([
                'user_id' => $user->id,
                'jumlah_pendakian' => $jumlahPendakian,
                'jumlah_summit' => $jumlahSummit,
                'questionnaire_answers' => $questionnaireAnswers,
                'weighted_score' => $resolvedWeightedScore,
                'weighted_tier' => $weightedTier,
                'onboarding_completed_at' => now(),
            ]);

            $user->forceFill([
                'tier' => $resolvedTier,
                'tier_source' => 'self_claim',
            ])->save();

            return $user->fresh(['experience']);
        });
    }

    public function syncVerifiedTierFromSystemActivity(User $user): User
    {
        if ((int) $user->level !== 1) {
            return $user;
        }

        $jumlahSelesai = Order::where('id_user', $user->id)->where('status', 'Selesai')->count();
        $jumlahPendakianSistem = $jumlahSelesai;

        if ($jumlahPendakianSistem <= 0) {
            return $user;
        }

        $tierBaru = $this->determineTierByPendakian($jumlahPendakianSistem);

        if ($user->tier !== $tierBaru || $user->tier_source !== 'verified') {
            $user->forceFill([
                'tier' => $tierBaru,
                'tier_source' => 'verified',
            ])->save();
        }

        return $user->fresh();
    }

    private function resolveSelfClaimTier(string $pendakianTier, ?string $weightedTier): string
    {
        if (empty($weightedTier) || !isset($this->tierPriority[$weightedTier])) {
            return $pendakianTier;
        }

        $pendakianTierLevel = $this->tierPriority[$pendakianTier] ?? 1;
        $weightedTierLevel = $this->tierPriority[$weightedTier];

        return $weightedTierLevel >= $pendakianTierLevel ? $weightedTier : $pendakianTier;
    }
}
