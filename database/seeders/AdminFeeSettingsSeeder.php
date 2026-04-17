<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdminFeeSettings;

class AdminFeeSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin fee settings if not exists
        AdminFeeSettings::firstOrCreate(
            ['id' => 1],
            [
                'fee_percentage' => 5.00,
                'fixed_fee' => 0.00,
                'fee_type' => 'percentage',
                'description' => 'Default admin fee of 5% on all withdrawals',
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
