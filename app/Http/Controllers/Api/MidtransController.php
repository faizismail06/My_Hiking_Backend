<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Transaction;
use App\Services\MidtransService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Midtrans Payment Controller
 * 
 * Controller untuk menangani pembayaran melalui Midtrans Payment Gateway.
 */
class MidtransController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    /**
     * Create payment / Generate Snap Token
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'payment_method' => 'nullable|string', // Optional: specific payment method
            'reuse_if_pending' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Get order with relations
            $order = Order::with(['mountain', 'trail', 'booker', 'members'])->findOrFail($request->order_id);
            $user = $order->booker;
            $paymentMethod = $request->payment_method;
            $reuseIfPending = filter_var($request->input('reuse_if_pending', false), FILTER_VALIDATE_BOOLEAN);

            // Calculate total amount
            $memberCount = $order->members->count() + 1; // booker + members
            $totalAmount = $memberCount * $order->total_harga_tiket;

            // Check if transaction exists
            $transaction = Transaction::where('id_pesanan', $order->id)->first();

            if ($transaction && $transaction->status_pesanan === 'Complete') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran sudah lunas'
                ], 400);
            }

            if ($order->status === 'Expired') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran sudah melewati batas waktu. Silakan buat pesanan baru.'
                ], 410);
            }

            if (in_array($order->status, ['Cancel Requested', 'Cancelled'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan ini sedang/ sudah dibatalkan dan tidak dapat diproses pembayarannya.'
                ], 422);
            }

            if ($transaction && $transaction->midtrans_order_id) {
                $remoteStatus = $this->refreshMidtransStatus($transaction);
                $transaction->refresh();

                if ($transaction->status_pesanan === 'Complete') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Pembayaran sudah lunas'
                    ], 400);
                }

                $samePaymentMethod = !$paymentMethod || $paymentMethod === $transaction->payment_type;
                $stillPending = $remoteStatus === 'pending' && !$this->isPaymentExpiredByWindow($transaction);

                if ($reuseIfPending && $samePaymentMethod && $stillPending && !empty($transaction->snap_token)) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Melanjutkan pembayaran yang masih pending',
                        'data' => [
                            'snap_token' => $transaction->snap_token,
                            'redirect_url' => $this->midtransService->buildRedirectUrlFromSnapToken($transaction->snap_token),
                            'transaction_id' => $transaction->id,
                            'midtrans_order_id' => $transaction->midtrans_order_id,
                            'total_amount' => $transaction->total_bayar,
                            'client_key' => $this->midtransService->getClientKey(),
                            'snap_url' => $this->midtransService->getSnapUrl(),
                            'payment_expires_at' => $this->getPaymentExpiresAt($transaction),
                        ]
                    ], 200);
                }
            }

            // Generate unique midtrans order ID for a fresh payment session
            $midtransOrderId = 'MH-' . $order->id . '-' . time();

            // Determine enabled payments based on selected payment method
            $enabledPayments = null;
            
            if ($paymentMethod) {
                // Map payment method to Midtrans enabled_payments
                $paymentMapping = [
                    'gopay' => ['gopay'],
                    'shopeepay' => ['shopeepay'],
                    'qris' => ['gopay', 'shopeepay'], // QRIS uses gopay/shopeepay
                    'bank_transfer' => ['bank_transfer'],
                    'bca_va' => ['bca_va'],
                    'bni_va' => ['bni_va'],
                    'bri_va' => ['bri_va'],
                    'mandiri_va' => ['echannel'],
                    'permata_va' => ['permata_va'],
                    'cimb_va' => ['other_va'],
                    'indomaret' => ['indomaret'],
                    'alfamart' => ['alfamart'],
                    'credit_card' => ['credit_card'],
                ];
                
                $enabledPayments = $paymentMapping[$paymentMethod] ?? null;
            }

            // Build transaction params
            $params = $this->midtransService->buildTransactionParams(
                $order,
                $user,
                $totalAmount,
                $midtransOrderId,
                $enabledPayments
            );

            // Create snap token
            $result = $this->midtransService->createSnapToken($params);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membuat pembayaran',
                    'error' => $result['error'] ?? $result['message']
                ], 500);
            }

            // Create or update transaction
            if ($transaction) {
                $transaction->update([
                    'snap_token' => $result['snap_token'],
                    'midtrans_order_id' => $midtransOrderId,
                    'total_bayar' => $totalAmount,
                    'status_pesanan' => 'Incomplete',
                    'payment_type' => $paymentMethod ?: $transaction->payment_type,
                    'transaction_time' => now(),
                    'waktu_pembayaran' => null,
                    'fraud_status' => null,
                ]);
            } else {
                $transaction = Transaction::create([
                    'id_pesanan' => $order->id,
                    'total_bayar' => $totalAmount,
                    'status_pesanan' => 'Incomplete',
                    'snap_token' => $result['snap_token'],
                    'midtrans_order_id' => $midtransOrderId,
                    'payment_type' => $paymentMethod,
                    'transaction_time' => now(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Snap token berhasil dibuat',
                'data' => [
                    'snap_token' => $result['snap_token'],
                    'redirect_url' => $result['redirect_url'],
                    'transaction_id' => $transaction->id,
                    'midtrans_order_id' => $midtransOrderId,
                    'total_amount' => $totalAmount,
                    'client_key' => $this->midtransService->getClientKey(),
                    'snap_url' => $this->midtransService->getSnapUrl(),
                    'payment_expires_at' => $this->getPaymentExpiresAt($transaction),
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Create Payment Error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat pembayaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle Midtrans notification callback
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleNotification(Request $request)
    {
        try {
            $notification = $request->all();
            
            Log::info('Midtrans Notification Received', $notification);

            // Verify signature
            if (!$this->midtransService->verifySignature($notification)) {
                Log::warning('Midtrans Invalid Signature', $notification);
                return response()->json(['message' => 'Invalid signature'], 403);
            }

            $orderId = $notification['order_id'];
            $transactionStatus = $notification['transaction_status'];
            $fraudStatus = $notification['fraud_status'] ?? null;
            $paymentType = $notification['payment_type'] ?? null;
            $transactionId = $notification['transaction_id'] ?? null;
            $transactionTime = $notification['transaction_time'] ?? null;

            // Find transaction by midtrans_order_id
            $transaction = Transaction::where('midtrans_order_id', $orderId)->first();

            if (!$transaction) {
                Log::error('Transaction not found', ['order_id' => $orderId]);
                return response()->json(['message' => 'Transaction not found'], 404);
            }

            // Update transaction status based on notification
            $newStatus = $this->mapTransactionStatus($transactionStatus, $fraudStatus);

            $transaction->update([
                'status_pesanan' => $newStatus,
                'payment_type' => $paymentType ?? $transaction->payment_type,
                'transaction_id' => $transactionId,
                'transaction_time' => $transactionTime ?? $transaction->transaction_time,
                'fraud_status' => $fraudStatus,
                'waktu_pembayaran' => $newStatus === 'Complete' ? now() : null,
            ]);

            // Update order status if payment is complete
            if ($newStatus === 'Complete' && $transaction->order && $this->canSetOrderToBooking($transaction->order)) {
                $transaction->order->update(['status' => 'Booking']);
            } elseif (
                in_array($transactionStatus, ['expire', 'cancel'], true)
                && $transaction->order
                && $this->canSetOrderToExpired($transaction->order)
            ) {
                $transaction->order->update(['status' => 'Expired']);
            }

            Log::info('Transaction Updated', [
                'order_id' => $orderId,
                'status' => $newStatus
            ]);

            return response()->json(['message' => 'OK'], 200);

        } catch (\Exception $e) {
            Log::error('Notification Error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error processing notification'], 500);
        }
    }

    /**
     * Check payment status
     * 
     * @param string $orderId Order ID (app order ID, not midtrans)
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkStatus($orderId)
    {
        try {
            // Search by transaction ID (primary key), order ID (id_pesanan), or midtrans_order_id
            $transaction = Transaction::where('id', $orderId)
                ->orWhere('id_pesanan', $orderId)
                ->orWhere('midtrans_order_id', $orderId)
                ->first();

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found'
                ], 404);
            }

            // Sync status from Midtrans and local expiry window
            $this->refreshMidtransStatus($transaction);
            $transaction->refresh()->loadMissing('order');

            $paymentExpired = $transaction->status_pesanan !== 'Complete' && $this->isPaymentExpiredByWindow($transaction);
            if ($paymentExpired && $transaction->order && $this->canSetOrderToExpired($transaction->order)) {
                $transaction->order->update(['status' => 'Expired']);
                $transaction->order->refresh();
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'transaction_id' => $transaction->id,
                    'order_id' => $transaction->id_pesanan,
                    'midtrans_order_id' => $transaction->midtrans_order_id,
                    'status' => $transaction->status_pesanan,
                    'payment_type' => $transaction->payment_type,
                    'total_bayar' => $transaction->total_bayar,
                    'waktu_pembayaran' => $transaction->waktu_pembayaran,
                    'order_status' => $transaction->order->status ?? null,
                    'is_payment_expired' => $paymentExpired,
                    'payment_expires_at' => $this->getPaymentExpiresAt($transaction),
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Check Status Error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error checking status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Finish callback (redirect from Midtrans)
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function finish(Request $request)
    {
        $orderId = $request->get('order_id');
        $statusCode = $request->get('status_code');
        $transactionStatus = $request->get('transaction_status');

        // Update transaction if needed
        if ($orderId) {
            $transaction = Transaction::where('midtrans_order_id', $orderId)->first();
            if ($transaction && $transactionStatus) {
                $newStatus = $this->mapTransactionStatus($transactionStatus, null);
                $transaction->update(['status_pesanan' => $newStatus]);
                
                if ($newStatus === 'Complete' && $transaction->order && $this->canSetOrderToBooking($transaction->order)) {
                    $transaction->update(['waktu_pembayaran' => now()]);
                    $transaction->order->update(['status' => 'Booking']);
                }
            }
        }

        // Return simple HTML for webview
        return response()->view('midtrans.finish', [
            'status' => $transactionStatus,
            'orderId' => $orderId,
        ]);
    }

    /**
     * Get Midtrans configuration for frontend
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getConfig()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'client_key' => $this->midtransService->getClientKey(),
                'snap_url' => $this->midtransService->getSnapUrl(),
                'is_production' => $this->midtransService->isProduction(),
            ]
        ], 200);
    }

    /**
     * Refresh local transaction from Midtrans status API.
     */
    protected function refreshMidtransStatus(Transaction $transaction): ?string
    {
        if (empty($transaction->midtrans_order_id)) {
            return null;
        }

        $result = $this->midtransService->getTransactionStatus($transaction->midtrans_order_id);
        if (!($result['success'] ?? false)) {
            return null;
        }

        $data = $result['data'] ?? [];
        $transactionStatus = strtolower((string) ($data['transaction_status'] ?? 'pending'));
        $newStatus = $this->mapTransactionStatus(
            $transactionStatus,
            $data['fraud_status'] ?? null
        );

        $transaction->update([
            'status_pesanan' => $newStatus,
            'payment_type' => $data['payment_type'] ?? $transaction->payment_type,
            'transaction_id' => $data['transaction_id'] ?? $transaction->transaction_id,
            'transaction_time' => $data['transaction_time'] ?? $transaction->transaction_time,
            'fraud_status' => $data['fraud_status'] ?? $transaction->fraud_status,
            'waktu_pembayaran' => $newStatus === 'Complete'
                ? ($transaction->waktu_pembayaran ?? now())
                : null,
        ]);

        $transaction->loadMissing('order');

        if ($newStatus === 'Complete' && $transaction->order && $this->canSetOrderToBooking($transaction->order)) {
            $transaction->order->update(['status' => 'Booking']);
        }

        if (in_array($transactionStatus, ['expire', 'cancel'], true) && $transaction->order && $this->canSetOrderToExpired($transaction->order)) {
            $transaction->order->update(['status' => 'Expired']);
        }

        return $transactionStatus;
    }

    /**
     * Compute payment expiry timestamp from configured Midtrans window.
     */
    protected function getPaymentExpiresAt(Transaction $transaction): ?string
    {
        $baseTime = $transaction->transaction_time ?? $transaction->created_at;
        if (!$baseTime) {
            return null;
        }

        $duration = $this->midtransService->getPaymentExpiryDuration();
        $unit = $this->midtransService->getPaymentExpiryUnit();

        $expiresAt = Carbon::parse($baseTime);
        $expiresAt = match ($unit) {
            'second' => $expiresAt->addSeconds($duration),
            'hour' => $expiresAt->addHours($duration),
            'day' => $expiresAt->addDays($duration),
            default => $expiresAt->addMinutes($duration),
        };

        return $expiresAt->toDateTimeString();
    }

    /**
     * Determine whether pending payment has passed configured expiry window.
     */
    protected function isPaymentExpiredByWindow(Transaction $transaction): bool
    {
        $expiresAt = $this->getPaymentExpiresAt($transaction);
        if (!$expiresAt) {
            return false;
        }

        return now()->greaterThanOrEqualTo(Carbon::parse($expiresAt));
    }

    /**
     * Map Midtrans transaction status to app status
     * 
     * @param string $transactionStatus
     * @param string|null $fraudStatus
     * @return string
     */
    protected function mapTransactionStatus($transactionStatus, $fraudStatus = null)
    {
        // Handle fraud status
        if ($fraudStatus === 'deny') {
            return 'Incomplete';
        }

        // Map transaction status
        switch ($transactionStatus) {
            case 'capture':
                // For credit card, check fraud status
                if ($fraudStatus === 'challenge') {
                    return 'Incomplete'; // Challenge still needs verification
                }
                return 'Complete';
            
            case 'settlement':
                return 'Complete';
            
            case 'pending':
                return 'Incomplete';
            
            case 'deny':
            case 'cancel':
            case 'expire':
                return 'Incomplete';
            
            case 'refund':
            case 'partial_refund':
                return 'Incomplete';
            
            default:
                return 'Incomplete';
        }
    }

    protected function canSetOrderToBooking(Order $order): bool
    {
        return in_array($order->status, ['Booking', 'Expired'], true);
    }

    protected function canSetOrderToExpired(Order $order): bool
    {
        return in_array($order->status, ['Booking', 'Expired'], true);
    }
}
