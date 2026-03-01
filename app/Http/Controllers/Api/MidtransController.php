<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\User;
use App\Services\MidtransService;
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

            // Calculate total amount
            $memberCount = $order->members->count() + 1; // booker + members
            $totalAmount = $memberCount * $order->total_harga_tiket;

            // Generate unique midtrans order ID
            $midtransOrderId = 'MH-' . $order->id . '-' . time();

            // Check if transaction exists
            $transaction = Transaction::where('id_pesanan', $order->id)->first();

            if ($transaction && $transaction->status_pesanan === 'Complete') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran sudah lunas'
                ], 400);
            }

            // Determine enabled payments based on selected payment method
            $enabledPayments = null;
            $paymentMethod = $request->payment_method;
            
            if ($paymentMethod) {
                // Map payment method to Midtrans enabled_payments
                $paymentMapping = [
                    'gopay' => ['gopay'],
                    'shopeepay' => ['shopeepay'],
                    'qris' => ['gopay', 'shopeepay'], // QRIS uses gopay/shopeepay
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
                ]);
            } else {
                $transaction = Transaction::create([
                    'id_pesanan' => $order->id,
                    'total_bayar' => $totalAmount,
                    'status_pesanan' => 'Incomplete',
                    'snap_token' => $result['snap_token'],
                    'midtrans_order_id' => $midtransOrderId,
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
                'payment_type' => $paymentType,
                'transaction_id' => $transactionId,
                'transaction_time' => $transactionTime,
                'fraud_status' => $fraudStatus,
                'waktu_pembayaran' => $newStatus === 'Complete' ? now() : null,
            ]);

            // Update order status if payment is complete
            if ($newStatus === 'Complete') {
                $transaction->order->update(['status' => 'Booking']);
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

            // If we have midtrans_order_id, check status from Midtrans
            if ($transaction->midtrans_order_id) {
                $result = $this->midtransService->getTransactionStatus($transaction->midtrans_order_id);

                if ($result['success']) {
                    $data = $result['data'];
                    $newStatus = $this->mapTransactionStatus(
                        $data['transaction_status'] ?? 'pending',
                        $data['fraud_status'] ?? null
                    );

                    // Update local transaction status
                    $transaction->update([
                        'status_pesanan' => $newStatus,
                        'payment_type' => $data['payment_type'] ?? $transaction->payment_type,
                        'transaction_id' => $data['transaction_id'] ?? $transaction->transaction_id,
                        'fraud_status' => $data['fraud_status'] ?? $transaction->fraud_status,
                    ]);

                    if ($newStatus === 'Complete') {
                        $transaction->update(['waktu_pembayaran' => now()]);
                        $transaction->order->update(['status' => 'Booking']);
                    }
                }
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
                
                if ($newStatus === 'Complete') {
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
}
