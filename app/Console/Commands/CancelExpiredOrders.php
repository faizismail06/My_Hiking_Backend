<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CancelExpiredOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:cancel-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membatalkan / meng-expired pesanan yang tidak dibayar dalam 15 menit';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredCount = 0;
        $thresholdTime = now()->subMinutes(15);

        // Cari pesanan yang berusia > 15 menit dan belum dibayar (status_pesanan != 'Complete')
        $staleOrders = Order::whereIn('status', ['Waiting Payment', 'pending', 'Booking', 'Menunggu Pembayaran'])
            ->where('created_at', '<=', $thresholdTime)
            ->get();

        foreach ($staleOrders as $order) {
            $transaction = Transaction::where('id_pesanan', $order->id)->first();

            // Jika transaksi belum lengkap/paid, tandai sebagai Expired
            if (!$transaction || $transaction->status_pesanan !== 'Complete') {
                $order->update(['status' => 'Expired']);
                if ($transaction) {
                    $transaction->update([
                        'payment_status' => 'expired',
                        'status_pesanan' => 'Kedaluwarsa',
                    ]);
                }
                $expiredCount++;
            }
        }

        $this->info("Berhasil meng-expired {$expiredCount} pesanan yang kedaluwarsa.");
        Log::info("orders:cancel-expired executed. Expired {$expiredCount} orders.");

        return 0;
    }
}
