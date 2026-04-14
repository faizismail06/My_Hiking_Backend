<?php

namespace App\Services;

use App\Models\Order;
use Carbon\Carbon;

class RefundCalculationService
{
    private const ADMIN_SHARE_RATE = 0.10;
    private const RANGER_SHARE_RATE = 0.90;
    private const H_MINUS_ONE_PENALTY_RATE = 0.20;

    public function calculate(Order $order): array
    {
        $total = (float) $order->total_harga_tiket;

        $today = Carbon::now('Asia/Jakarta')->startOfDay();
        $hikingDate = Carbon::parse($order->tanggal_naik, 'Asia/Jakarta')->startOfDay();
        $hMinusOne = $hikingDate->copy()->subDay();

        $penalty = 0.0;
        $refund = 0.0;
        $message = null;

        if ($today->greaterThanOrEqualTo($hikingDate)) {
            $penalty = $total;
            $refund = 0.0;
            $message = 'Pembatalan berhasil, tetapi dana tidak dapat dikembalikan karena sudah Hari-H pendakian.';
        } elseif ($today->equalTo($hMinusOne)) {
            $penalty = round($total * self::H_MINUS_ONE_PENALTY_RATE, 2);
            $refund = $total - $penalty;
        } else {
            $penalty = 0.0;
            $refund = $total;
        }

        $adminShare = round($total * self::ADMIN_SHARE_RATE, 2);
        $rangerShare = $total - $adminShare;

        $adminPenaltyShare = round($penalty * self::ADMIN_SHARE_RATE, 2);
        $rangerPenaltyShare = $penalty - $adminPenaltyShare;

        return [
            'ticket_price' => $total,
            'refund_amount' => round($refund, 2),
            'penalty_amount' => round($penalty, 2),
            'admin_share' => $adminShare,
            'ranger_share' => round($rangerShare, 2),
            'admin_penalty_share' => $adminPenaltyShare,
            'ranger_penalty_share' => round($rangerPenaltyShare, 2),
            'is_refundable' => $refund > 0,
            'warning_message' => $message,
            'policy_timezone' => 'Asia/Jakarta',
        ];
    }
}
