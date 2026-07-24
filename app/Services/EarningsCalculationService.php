<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EarningsCalculationService
{
    /**
     * Reusable paid-payment filter that supports both normalized and legacy statuses.
     */
    private function applyPaidTransactionFilter($query)
    {
        return $query->where(function ($q) {
            $q->where('payment_status', 'paid')
                ->orWhere('status_pesanan', 'Verified')
                ->orWhere('status_pesanan', 'Complete');
        });
    }

    /**
     * Calculate and update earnings for all trail guards
     */
    public function updateAllEarnings()
    {
        $trailGuards = User::whereHas('trails')->get();

        foreach ($trailGuards as $guard) {
            $this->updateEarningsForUser($guard);
        }
    }

    /**
     * Calculate and update earnings for a specific trail guard
     */
    public function updateEarningsForUser(User $user)
    {
        // Get all paid transactions total for trails managed by this user
        $totalEarnings = $this->calculateEarningsForUser($user);

        // Get total completed withdrawn amount
        $totalWithdrawn = (float) DB::table('withdrawal_requests')
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->sum('amount');

        // Get total pending or approved (reserved) withdrawn amount
        $pendingWithdrawn = (float) DB::table('withdrawal_requests')
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->sum('amount');

        // Available balance is total earnings minus completed withdrawals minus reserved pending requests
        $availableBalance = max(0, $totalEarnings - $totalWithdrawn - $pendingWithdrawn);

        // Count paid transactions
        $trailIds = DB::table('routes')->where('user_id', $user->id)->pluck('id');
        $paidStatuses = ['Complete', 'Verified', 'paid', 'settlement', 'success', 'completed', 'confirmed'];
        $transactionCount = DB::table('transactions')
            ->join('orders', 'transactions.id_pesanan', '=', 'orders.id')
            ->whereIn('orders.id_jalur', $trailIds)
            ->whereIn('transactions.status_pesanan', $paidStatuses)
            ->count();

        // Update user balance columns
        $user->update([
            'total_earnings' => $totalEarnings,
            'withdrawn_amount' => $totalWithdrawn,
            'available_balance' => $availableBalance,
            'transaction_count' => $transactionCount,
        ]);
    }

    /**
     * Calculate total earnings for a specific user from paid orders/transactions
     */
    public function calculateEarningsForUser(User $user): float
    {
        $trailIds = DB::table('routes')->where('user_id', $user->id)->pluck('id');

        if ($trailIds->isEmpty()) {
            return 0.0;
        }

        $paidStatuses = ['Complete', 'Verified', 'paid', 'settlement', 'success', 'completed', 'confirmed'];

        $totalEarnings = DB::table('transactions')
            ->join('orders', 'transactions.id_pesanan', '=', 'orders.id')
            ->whereIn('orders.id_jalur', $trailIds)
            ->whereIn('transactions.status_pesanan', $paidStatuses)
            ->sum('transactions.total_bayar');

        return (float) $totalEarnings;
    }

    /**
     * Calculate earnings for a specific order
     */
    public function calculateOrderEarnings(Order $order): float
    {
        if (!$order->trail) {
            return 0;
        }

        // Check if order is paid
        if (!$order->transaction) {
            return 0;
        }

        $paymentStatus = strtolower((string) ($order->transaction->payment_status ?? ''));
        $isPaid = $paymentStatus === 'paid' || in_array($order->transaction->status_pesanan, ['Verified', 'Complete'], true);

        if (!$isPaid) {
            return 0;
        }

        // Count members in this order (including booker)
        $memberCount = $order->members()->count() + 1; // +1 for the booker

        // Calculate earnings: trail fee × number of members
        return $order->trail->biaya * $memberCount;
    }

    /**
     * Get earnings statistics for dashboard
     */
    public function getEarningsStats()
    {
        $stats = [
            'total_earnings' => DB::table('users')
                ->sum('total_earnings'),
            'total_withdrawn' => DB::table('withdrawal_requests')
                ->where('status', 'completed')
                ->sum('net_amount'),
            'pending_requests' => DB::table('withdrawal_requests')
                ->where('status', 'pending')
                ->count(),
            'approved_requests' => DB::table('withdrawal_requests')
                ->where('status', 'approved')
                ->count(),
            'total_available' => DB::table('users')
                ->sum('available_balance'),
        ];

        return $stats;
    }
}
