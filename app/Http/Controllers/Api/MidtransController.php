<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Transaction;
use App\Services\MidtransService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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

            if (empty($paymentMethod) && $transaction && !empty($transaction->payment_type)) {
                $paymentMethod = $transaction->payment_type;
            }

            if ($transaction && $this->isTransactionPaid($transaction)) {
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

                if ($this->isTransactionPaid($transaction)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Pembayaran sudah lunas'
                    ], 400);
                }

                $samePaymentMethod = !$paymentMethod || $paymentMethod === $transaction->payment_type;
                $stillPending =
                    ($remoteStatus === 'pending' || $this->isTransactionPending($transaction))
                    && !$this->isPaymentExpiredByWindow($transaction);

                $hasInAppInstruction = !empty($transaction->payment_code) || !empty($transaction->deeplink_url);

                if ($reuseIfPending && $samePaymentMethod && $stillPending && $hasInAppInstruction) {
                    $qrisPayload = $this->resolveQrisPayload($transaction);

                    return response()->json([
                        'success' => true,
                        'message' => 'Melanjutkan pembayaran yang masih pending',
                        'data' => [
                            'order_id' => $order->id,
                            'snap_token' => null,
                            'redirect_url' => null,
                            'transaction_id' => $transaction->id,
                            'midtrans_order_id' => $transaction->midtrans_order_id,
                            'total_payment' => $transaction->total_bayar,
                            'payment_method' => $transaction->payment_method_name,
                            'payment_type' => $transaction->payment_type,
                            'transaction_created_at' => optional($transaction->transaction_time ?? $transaction->created_at)->toIso8601String(),
                            'payment_expires_at' => $this->getPaymentExpiresAt($transaction),
                            'payment_code' => $transaction->payment_code,
                            'payment_code_label' => $transaction->payment_code_label,
                            'payment_instruction' => $transaction->payment_instruction,
                            'deeplink_url' => $qrisPayload['deeplink_url'] ?? $transaction->deeplink_url,
                            'qr_code_url' => $qrisPayload['qr_code_url'],
                            'qr_string' => $qrisPayload['qr_string'],
                        ]
                    ], 200);
                }
            }

            // Generate unique midtrans order ID for a fresh payment session
            $midtransOrderId = 'MH-' . $order->id . '-' . time();

            $chargeResult = null;
            $result = null;

            // If method selected, try direct charge first so app can show VA/payment code immediately.
            if (!empty($paymentMethod)) {
                try {
                    $directParams = $this->midtransService->buildDirectChargeParams(
                        $order,
                        $user,
                        $totalAmount,
                        $midtransOrderId,
                        $paymentMethod
                    );

                    $chargeResult = $this->midtransService->createDirectCharge($directParams);
                } catch (\InvalidArgumentException $e) {
                    // Unsupported direct charge method will fallback to Snap.
                    $chargeResult = null;
                }
            }

            if ($chargeResult && ($chargeResult['success'] ?? false)) {
                $chargeData = $chargeResult['data'] ?? [];
                $paymentCodeData = $this->extractPaymentInstruction($chargeData);

                if ($transaction) {
                    $transaction->update([
                        'midtrans_order_id' => $midtransOrderId,
                        'total_bayar' => $totalAmount,
                        'status_pesanan' => 'Incomplete',
                        'payment_status' => 'pending',
                        'payment_code' => $paymentCodeData['code'],
                        'payment_code_label' => $paymentCodeData['label'],
                        'payment_instruction' => $paymentCodeData['instruction'],
                        'deeplink_url' => $this->resolveInstructionUrlForStorage(
                            $paymentCodeData,
                            $paymentMethod ?: $transaction->payment_type,
                            $transaction->deeplink_url
                        ),
                        'payment_type' => $paymentMethod ?: $transaction->payment_type,
                        'transaction_time' => now(),
                        'waktu_pembayaran' => null,
                        'fraud_status' => null,
                        'transaction_id' => $chargeData['transaction_id'] ?? $transaction->transaction_id,
                    ]);
                } else {
                    $transaction = Transaction::create([
                        'id_pesanan' => $order->id,
                        'total_bayar' => $totalAmount,
                        'status_pesanan' => 'Incomplete',
                        'payment_status' => 'pending',
                        'payment_code' => $paymentCodeData['code'],
                        'payment_code_label' => $paymentCodeData['label'],
                        'payment_instruction' => $paymentCodeData['instruction'],
                        'deeplink_url' => $this->resolveInstructionUrlForStorage(
                            $paymentCodeData,
                            $paymentMethod
                        ),
                        'midtrans_order_id' => $midtransOrderId,
                        'payment_type' => $paymentMethod,
                        'transaction_time' => now(),
                        'transaction_id' => $chargeData['transaction_id'] ?? null,
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Instruksi pembayaran berhasil dibuat',
                    'data' => [
                        'order_id' => $order->id,
                        'transaction_id' => $transaction->id,
                        'midtrans_order_id' => $midtransOrderId,
                        'total_payment' => $totalAmount,
                        'payment_method' => $transaction->payment_method_name,
                        'payment_type' => $transaction->payment_type,
                        'transaction_created_at' => optional($transaction->transaction_time)->toIso8601String(),
                        'payment_expires_at' => $this->getPaymentExpiresAt($transaction),
                        'payment_code' => $paymentCodeData['code'],
                        'payment_code_label' => $paymentCodeData['label'],
                        'payment_instruction' => $paymentCodeData['instruction'],
                        'deeplink_url' => $this->resolveInstructionUrlForStorage(
                            $paymentCodeData,
                            $transaction->payment_type
                        ),
                        'qr_code_url' => $paymentCodeData['qr_code_url'],
                        'qr_string' => $paymentCodeData['qr_string'],
                        'redirect_url' => null,
                        'snap_token' => null,
                    ]
                ], 200);
            }

            if (!empty($paymentMethod)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Metode pembayaran ini sedang tidak tersedia untuk instruksi in-app. Silakan pilih metode lain.',
                ], 422);
            }

            // Fallback to Snap when direct charge is not supported.
            $enabledPayments = null;
            if ($paymentMethod) {
                $paymentMapping = [
                    'gopay' => ['gopay'],
                    'shopeepay' => ['shopeepay'],
                    'qris' => ['gopay', 'shopeepay'],
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

            $params = $this->midtransService->buildTransactionParams(
                $order,
                $user,
                $totalAmount,
                $midtransOrderId,
                $enabledPayments
            );

            $result = $this->midtransService->createSnapToken($params);

            if (!($result['success'] ?? false)) {
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
                    'payment_status' => 'pending',
                        'payment_code' => null,
                        'payment_code_label' => null,
                        'payment_instruction' => null,
                        'deeplink_url' => null,
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
                    'payment_status' => 'pending',
                        'payment_code' => null,
                        'payment_code_label' => null,
                        'payment_instruction' => null,
                        'deeplink_url' => null,
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
                    'order_id' => $order->id,
                    'snap_token' => $result['snap_token'],
                    'redirect_url' => $result['redirect_url'],
                    'transaction_id' => $transaction->id,
                    'midtrans_order_id' => $midtransOrderId,
                    'total_payment' => $totalAmount,
                    'payment_method' => $transaction->payment_method_name,
                    'payment_type' => $transaction->payment_type,
                    'transaction_created_at' => optional($transaction->transaction_time)->toIso8601String(),
                    'client_key' => $this->midtransService->getClientKey(),
                    'snap_url' => $this->midtransService->getSnapUrl(),
                    'payment_expires_at' => $this->getPaymentExpiresAt($transaction),
                    'payment_code' => null,
                    'payment_code_label' => null,
                    'payment_instruction' => null,
                    'deeplink_url' => null,
                    'qr_code_url' => null,
                    'qr_string' => null,
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
            $paymentCodeData = $this->extractPaymentInstruction($notification);

            $transaction->update([
                'payment_status' => $newStatus,
                'status_pesanan' => $this->legacyStatusFromPaymentStatus($newStatus),
                'payment_type' => $paymentType ?? $transaction->payment_type,
                'payment_code' => $newStatus === 'pending'
                    ? ($paymentCodeData['code'] ?? $transaction->payment_code)
                    : null,
                'payment_code_label' => $newStatus === 'pending'
                    ? ($paymentCodeData['label'] ?? $transaction->payment_code_label)
                    : null,
                'payment_instruction' => $newStatus === 'pending'
                    ? ($paymentCodeData['instruction'] ?? $transaction->payment_instruction)
                    : null,
                'deeplink_url' => $newStatus === 'pending'
                    ? ($paymentCodeData['deeplink_url'] ?? $paymentCodeData['qr_code_url'] ?? $transaction->deeplink_url)
                    : null,
                'transaction_id' => $transactionId,
                'transaction_time' => $transactionTime ?? $transaction->transaction_time,
                'fraud_status' => $fraudStatus,
                'waktu_pembayaran' => $newStatus === 'paid' ? now() : null,
            ]);

            // Update order status if payment is complete
            if ($newStatus === 'paid' && $transaction->order && $this->canSetOrderToBooking($transaction->order)) {
                $transaction->order->update(['status' => 'Booking']);
            } elseif (
                $newStatus === 'expired'
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

            $paymentExpired = !$this->isTransactionPaid($transaction) && $this->isPaymentExpiredByWindow($transaction);
            if ($paymentExpired && $transaction->order && $this->canSetOrderToExpired($transaction->order)) {
                $transaction->update([
                    'payment_status' => 'expired',
                    'status_pesanan' => $this->legacyStatusFromPaymentStatus('expired'),
                ]);
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
                    'payment_status' => $this->normalizedPaymentStatus($transaction),
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
                $paymentCodeData = $this->extractPaymentInstruction($request->all());
                $transaction->update([
                    'payment_status' => $newStatus,
                    'status_pesanan' => $this->legacyStatusFromPaymentStatus($newStatus),
                    'payment_code' => $newStatus === 'pending'
                        ? ($paymentCodeData['code'] ?? $transaction->payment_code)
                        : null,
                    'payment_code_label' => $newStatus === 'pending'
                        ? ($paymentCodeData['label'] ?? $transaction->payment_code_label)
                        : null,
                    'payment_instruction' => $newStatus === 'pending'
                        ? ($paymentCodeData['instruction'] ?? $transaction->payment_instruction)
                        : null,
                    'deeplink_url' => $newStatus === 'pending'
                        ? ($paymentCodeData['deeplink_url'] ?? $paymentCodeData['qr_code_url'] ?? $transaction->deeplink_url)
                        : null,
                    'waktu_pembayaran' => $newStatus === 'paid' ? ($transaction->waktu_pembayaran ?? now()) : null,
                ]);
                
                if ($newStatus === 'paid' && $transaction->order && $this->canSetOrderToBooking($transaction->order)) {
                    $transaction->order->update(['status' => 'Booking']);
                } elseif ($newStatus === 'expired' && $transaction->order && $this->canSetOrderToExpired($transaction->order)) {
                    $transaction->order->update(['status' => 'Expired']);
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
     * Payment status endpoint used by Flutter waiting page polling.
     */
    public function paymentStatus($orderId)
    {
        try {
            $transaction = Transaction::where('id_pesanan', $orderId)
                ->orWhere('midtrans_order_id', $orderId)
                ->orWhere('id', $orderId)
                ->latest('id')
                ->first();

            if (!$transaction) {
                return response()->json([
                    'status' => 'failed',
                    'order_id' => (string) $orderId,
                    'message' => 'Transaction not found',
                ], 404);
            }

            $this->refreshMidtransStatus($transaction);
            $transaction->refresh()->loadMissing('order');

            $normalizedStatus = $this->normalizedPaymentStatus($transaction);

            if ($normalizedStatus === 'pending' && $this->isPaymentExpiredByWindow($transaction)) {
                $transaction->update([
                    'payment_status' => 'expired',
                    'status_pesanan' => $this->legacyStatusFromPaymentStatus('expired'),
                ]);

                if ($transaction->order && $this->canSetOrderToExpired($transaction->order)) {
                    $transaction->order->update(['status' => 'Expired']);
                }

                $normalizedStatus = 'expired';
            }

            $qrisPayload = $this->resolveQrisPayload($transaction);

            return response()->json([
                'status' => $normalizedStatus,
                'order_id' => (string) $transaction->id_pesanan,
                'transaction_id' => $transaction->id,
                'total_payment' => $transaction->total_bayar,
                'payment_method' => $transaction->payment_method_name,
                'payment_type' => $transaction->payment_type,
                'transaction_created_at' => optional($transaction->transaction_time ?? $transaction->created_at)->toIso8601String(),
                'payment_expires_at' => $this->getPaymentExpiresAt($transaction),
                'midtrans_order_id' => $transaction->midtrans_order_id,
                'payment_code' => $transaction->payment_code,
                'payment_code_label' => $transaction->payment_code_label,
                'payment_instruction' => $transaction->payment_instruction,
                'deeplink_url' => $qrisPayload['deeplink_url'] ?? $transaction->deeplink_url,
                'qr_code_url' => $qrisPayload['qr_code_url'],
                'qr_string' => $qrisPayload['qr_string'],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Payment Status Error', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => 'failed',
                'order_id' => (string) $orderId,
                'message' => 'Error checking payment status',
            ], 500);
        }
    }

    /**
     * Proxy QRIS image from Midtrans so Flutter can render it reliably.
     */
    public function paymentQrisImage($orderId)
    {
        try {
            $transaction = Transaction::where('id_pesanan', $orderId)
                ->orWhere('midtrans_order_id', $orderId)
                ->orWhere('id', $orderId)
                ->latest('id')
                ->first();

            if (!$transaction) {
                return response()->json([
                    'message' => 'Transaction not found',
                ], 404);
            }

            if (strtolower((string) ($transaction->payment_type ?? '')) !== 'qris') {
                return response()->json([
                    'message' => 'QR image is only available for QRIS payments',
                ], 422);
            }

            $this->refreshMidtransStatus($transaction);
            $transaction->refresh();

            $qrisPayload = $this->resolveQrisPayload($transaction);
            $qrCodeUrl = $qrisPayload['qr_code_url'] ?? null;

            if (empty($qrCodeUrl)) {
                return response()->json([
                    'message' => 'QR code URL is unavailable',
                ], 404);
            }

            $midtransResponse = Http::timeout(15)
                ->withBasicAuth(config('midtrans.server_key'), '')
                ->withHeaders([
                    'Accept' => 'image/*',
                ])
                ->get($qrCodeUrl);

            if (!$midtransResponse->successful()) {
                Log::warning('QRIS Proxy Fetch Failed', [
                    'order_id' => $orderId,
                    'status' => $midtransResponse->status(),
                ]);

                return response()->json([
                    'message' => 'Failed to fetch QR image',
                ], 502);
            }

            $contentType = $midtransResponse->header('Content-Type') ?? 'image/png';

            return response($midtransResponse->body(), 200)
                ->header('Content-Type', $contentType)
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
        } catch (\Exception $e) {
            Log::error('QRIS Proxy Error', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error generating QR image',
            ], 500);
        }
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
        $paymentCodeData = $this->extractPaymentInstruction($data);

        $transaction->update([
            'payment_status' => $newStatus,
            'status_pesanan' => $this->legacyStatusFromPaymentStatus($newStatus),
            'payment_type' => $data['payment_type'] ?? $transaction->payment_type,
            'payment_code' => $newStatus === 'pending'
                ? ($paymentCodeData['code'] ?? $transaction->payment_code)
                : null,
            'payment_code_label' => $newStatus === 'pending'
                ? ($paymentCodeData['label'] ?? $transaction->payment_code_label)
                : null,
            'payment_instruction' => $newStatus === 'pending'
                ? ($paymentCodeData['instruction'] ?? $transaction->payment_instruction)
                : null,
            'deeplink_url' => $newStatus === 'pending'
                ? $this->resolveInstructionUrlForStorage(
                    $paymentCodeData,
                    $data['payment_type'] ?? $transaction->payment_type,
                    $transaction->deeplink_url
                )
                : null,
            'transaction_id' => $data['transaction_id'] ?? $transaction->transaction_id,
            'transaction_time' => $data['transaction_time'] ?? $transaction->transaction_time,
            'fraud_status' => $data['fraud_status'] ?? $transaction->fraud_status,
            'waktu_pembayaran' => $newStatus === 'paid'
                ? ($transaction->waktu_pembayaran ?? now())
                : null,
        ]);

        $transaction->loadMissing('order');

        if ($newStatus === 'paid' && $transaction->order && $this->canSetOrderToBooking($transaction->order)) {
            $transaction->order->update(['status' => 'Booking']);
        }

        if ($newStatus === 'expired' && $transaction->order && $this->canSetOrderToExpired($transaction->order)) {
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
        $transactionStatus = strtolower((string) $transactionStatus);

        // Handle fraud status
        if ($fraudStatus === 'deny') {
            return 'failed';
        }

        // Map transaction status
        switch ($transactionStatus) {
            case 'capture':
                // For credit card, check fraud status
                if ($fraudStatus === 'challenge') {
                    return 'pending'; // Challenge still needs verification
                }
                return 'paid';
            
            case 'settlement':
                return 'paid';
            
            case 'pending':
                return 'pending';

            case 'expire':
                return 'expired';
            
            case 'deny':
            case 'cancel':
                return 'failed';
            
            case 'refund':
            case 'partial_refund':
                return 'failed';
            
            default:
                return 'pending';
        }
    }

    protected function legacyStatusFromPaymentStatus(string $status): string
    {
        return $status === 'paid' ? 'Complete' : 'Incomplete';
    }

    protected function normalizedPaymentStatus(Transaction $transaction): string
    {
        $status = strtolower((string) ($transaction->payment_status ?? ''));
        if (in_array($status, ['pending', 'paid', 'expired', 'failed'], true)) {
            return $status;
        }

        return strtolower((string) $transaction->status_pesanan) === 'complete'
            ? 'paid'
            : 'pending';
    }

    protected function isTransactionPaid(Transaction $transaction): bool
    {
        return $this->normalizedPaymentStatus($transaction) === 'paid';
    }

    protected function isTransactionPending(Transaction $transaction): bool
    {
        return $this->normalizedPaymentStatus($transaction) === 'pending';
    }

    protected function canSetOrderToBooking(Order $order): bool
    {
        return in_array($order->status, ['Booking', 'Expired'], true);
    }

    protected function canSetOrderToExpired(Order $order): bool
    {
        return in_array($order->status, ['Booking', 'Expired'], true);
    }

    protected function extractPaymentInstruction(array $chargeData): array
    {
        $paymentType = strtolower((string) ($chargeData['payment_type'] ?? ''));
        $qrString = !empty($chargeData['qr_string'])
            ? (string) $chargeData['qr_string']
            : null;

        $vaNumbers = $chargeData['va_numbers'] ?? [];
        if (is_array($vaNumbers) && !empty($vaNumbers)) {
            $firstVa = $vaNumbers[0];
            $bank = strtoupper((string) ($firstVa['bank'] ?? 'VA'));
            $number = (string) ($firstVa['va_number'] ?? '');

            return [
                'code' => $number,
                'label' => $bank . ' Virtual Account',
                'instruction' => 'Gunakan nomor VA ini untuk menyelesaikan pembayaran.',
                'deeplink_url' => null,
                'qr_code_url' => null,
                'qr_string' => $qrString,
            ];
        }

        if (!empty($chargeData['permata_va_number'])) {
            return [
                'code' => (string) $chargeData['permata_va_number'],
                'label' => 'Permata Virtual Account',
                'instruction' => 'Gunakan nomor VA Permata untuk pembayaran.',
                'deeplink_url' => null,
                'qr_code_url' => null,
                'qr_string' => $qrString,
            ];
        }

        if (!empty($chargeData['bill_key']) || !empty($chargeData['biller_code'])) {
            $billerCode = (string) ($chargeData['biller_code'] ?? '-');
            $billKey = (string) ($chargeData['bill_key'] ?? '-');

            return [
                'code' => trim('Biller ' . $billerCode . ' / BillKey ' . $billKey),
                'label' => 'Mandiri Bill Payment',
                'instruction' => 'Gunakan Biller Code dan Bill Key untuk pembayaran Mandiri.',
                'deeplink_url' => null,
                'qr_code_url' => null,
                'qr_string' => $qrString,
            ];
        }

        if (!empty($chargeData['payment_code'])) {
            $label = $paymentType === 'cstore'
                ? strtoupper((string) ($chargeData['store'] ?? 'Convenience Store'))
                : 'Payment Code';

            return [
                'code' => (string) $chargeData['payment_code'],
                'label' => $label,
                'instruction' => 'Tunjukkan kode pembayaran ini saat melakukan pembayaran.',
                'deeplink_url' => null,
                'qr_code_url' => null,
                'qr_string' => $qrString,
            ];
        }

        $actions = $chargeData['actions'] ?? [];
        if (is_array($actions) && !empty($actions)) {
            $deeplinkUrl = null;
            $qrCodeUrl = null;
            foreach ($actions as $action) {
                $name = strtolower((string) ($action['name'] ?? ''));
                $url = !empty($action['url']) ? (string) $action['url'] : null;

                if (!$url) {
                    continue;
                }

                if ($name === 'generate-qr-code') {
                    $qrCodeUrl = $url;
                    continue;
                }

                if ($name === 'deeplink-redirect') {
                    $deeplinkUrl = $url;
                    continue;
                }

                if ($deeplinkUrl === null) {
                    $deeplinkUrl = $url;
                }
            }

            return [
                'code' => null,
                'label' => strtoupper($paymentType ?: 'E-WALLET'),
                'instruction' => $paymentType === 'qris'
                    ? 'Scan QRIS berikut untuk menyelesaikan pembayaran.'
                    : 'Gunakan tautan pembayaran untuk menyelesaikan transaksi.',
                'deeplink_url' => $deeplinkUrl,
                'qr_code_url' => $qrCodeUrl,
                'qr_string' => $qrString,
            ];
        }

        return [
            'code' => null,
            'label' => strtoupper($paymentType ?: 'PAYMENT'),
            'instruction' => null,
            'deeplink_url' => null,
            'qr_code_url' => null,
            'qr_string' => $qrString,
        ];
    }

    protected function resolveInstructionUrlForStorage(
        array $paymentCodeData,
        ?string $paymentType,
        ?string $fallback = null
    ): ?string {
        $normalizedType = strtolower((string) ($paymentType ?? ''));

        if ($normalizedType === 'qris' && !empty($paymentCodeData['qr_code_url'])) {
            return (string) $paymentCodeData['qr_code_url'];
        }

        if (!empty($paymentCodeData['deeplink_url'])) {
            return (string) $paymentCodeData['deeplink_url'];
        }

        if (!empty($paymentCodeData['qr_code_url'])) {
            return (string) $paymentCodeData['qr_code_url'];
        }

        return $fallback;
    }

    protected function resolveQrisPayload(Transaction $transaction): array
    {
        if (strtolower((string) ($transaction->payment_type ?? '')) !== 'qris') {
            return [
                'deeplink_url' => $transaction->deeplink_url,
                'qr_code_url' => null,
                'qr_string' => null,
            ];
        }

        if (empty($transaction->midtrans_order_id)) {
            return [
                'deeplink_url' => $transaction->deeplink_url,
                'qr_code_url' => $this->isLikelyQrUrl($transaction->deeplink_url)
                    ? $transaction->deeplink_url
                    : null,
                'qr_string' => null,
            ];
        }

        $statusResult = $this->midtransService->getTransactionStatus($transaction->midtrans_order_id);

        if (!($statusResult['success'] ?? false)) {
            return [
                'deeplink_url' => $transaction->deeplink_url,
                'qr_code_url' => $this->isLikelyQrUrl($transaction->deeplink_url)
                    ? $transaction->deeplink_url
                    : null,
                'qr_string' => null,
            ];
        }

        $paymentCodeData = $this->extractPaymentInstruction($statusResult['data'] ?? []);

        return [
            'deeplink_url' => $paymentCodeData['deeplink_url'] ?? $transaction->deeplink_url,
            'qr_code_url' => $paymentCodeData['qr_code_url']
                ?? ($this->isLikelyQrUrl($transaction->deeplink_url)
                    ? $transaction->deeplink_url
                    : null),
            'qr_string' => $paymentCodeData['qr_string'] ?? null,
        ];
    }

    protected function isLikelyQrUrl(?string $url): bool
    {
        if (empty($url)) {
            return false;
        }

        $normalized = strtolower((string) $url);
        return str_contains($normalized, 'generate-qr-code')
            || str_contains($normalized, 'qr-code')
            || str_ends_with($normalized, '.png')
            || str_ends_with($normalized, '.jpg')
            || str_ends_with($normalized, '.jpeg')
            || str_ends_with($normalized, '.webp');
    }
}
