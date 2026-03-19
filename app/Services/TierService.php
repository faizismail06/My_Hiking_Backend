<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\UserExperience;
use Illuminate\Support\Facades\DB;

class TierService
{
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

    public function createSelfClaimExperience(User $user, int $jumlahPendakian, int $jumlahSummit): User
    {
        return DB::transaction(function () use ($user, $jumlahPendakian, $jumlahSummit) {
            UserExperience::create([
                'user_id' => $user->id,
                'jumlah_pendakian' => $jumlahPendakian,
                'jumlah_summit' => $jumlahSummit,
                'onboarding_completed_at' => now(),
            ]);

            $user->forceFill([
                'tier' => $this->determineTierByPendakian($jumlahPendakian),
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

        $jumlahBooking = Order::where('id_user', $user->id)->count();
        $jumlahSelesai = Order::where('id_user', $user->id)->where('status', 'Selesai')->count();
        $jumlahPendakianSistem = max($jumlahBooking, $jumlahSelesai);

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
}
