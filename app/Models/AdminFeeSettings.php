<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminFeeSettings extends Model
{
    use HasFactory;

    protected $table = 'admin_fee_settings';

    protected $fillable = [
        'fee_percentage',
        'fixed_fee',
        'fee_type',
        'description',
        'updated_by',
    ];

    protected $casts = [
        'fee_percentage' => 'decimal:2',
        'fixed_fee' => 'decimal:2',
    ];

    /**
     * Get current active settings
     */
    public static function getCurrent()
    {
        return static::first();
    }

    /**
     * Calculate fee for given amount
     */
    public static function calculateFee($amount)
    {
        $settings = static::getCurrent();
        if (!$settings) {
            return 0;
        }

        $fee = 0;

        if ($settings->fee_type === 'percentage' || $settings->fee_type === 'both') {
            $fee += ($amount * $settings->fee_percentage) / 100;
        }

        if ($settings->fee_type === 'fixed' || $settings->fee_type === 'both') {
            $fee += $settings->fixed_fee;
        }

        return round($fee, 2);
    }

    /**
     * Get fee type label
     */
    public function getFeeTypeLabel(): string
    {
        return match ($this->fee_type) {
            'percentage' => 'Persentase',
            'fixed' => 'Biaya Tetap',
            'both' => 'Persentase + Biaya Tetap',
            default => 'Unknown'
        };
    }
}
