<?php

namespace App\Services;

use App\Models\User;

class UserActionReadinessService
{
    /**
     * Data identitas yang wajib sebelum aksi penting.
     */
    private array $requiredIdentityFields = [
        'name',
        'nik',
        'address',
        'phone',
        'emergency_phone',
        'date_of_birth',
    ];

    public function missingIdentityFields(User $user): array
    {
        $missing = [];

        foreach ($this->requiredIdentityFields as $field) {
            if (empty($user->{$field})) {
                $missing[] = $field;
            }
        }

        return $missing;
    }

    public function isIdentityComplete(User $user): bool
    {
        return count($this->missingIdentityFields($user)) === 0;
    }

    public function needsTierOnboarding(User $user): bool
    {
        if ((int) $user->level !== 1) {
            return false;
        }

        return empty($user->tier)
            || empty($user->tier_source)
            || $user->experience === null;
    }
}
