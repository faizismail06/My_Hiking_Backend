<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Midtrans Payment Service
 * 
 * Service class untuk menangani integrasi Midtrans Payment Gateway.
 * Menggunakan Snap API untuk proses pembayaran.
 */
class MidtransService
{
    protected $serverKey;
    protected $clientKey;
    protected $isProduction;
    protected $isSanitized;
    protected $is3ds;
    protected $snapUrl;
    protected $apiUrl;

    public function __construct()
    {
        $this->serverKey = config('midtrans.server_key');
        $this->clientKey = config('midtrans.client_key');
        $this->isProduction = config('midtrans.is_production');
        $this->isSanitized = config('midtrans.is_sanitized');
        $this->is3ds = config('midtrans.is_3ds');
        $this->snapUrl = config('midtrans.snap_url');
        $this->apiUrl = config('midtrans.api_url');
    }

    /**
     * Create Snap Token untuk pembayaran
     * 
     * @param array $params Parameter transaksi
     * @return array
     */
    public function createSnapToken(array $params)
    {
        try {
            $url = $this->isProduction 
                ? 'https://app.midtrans.com/snap/v1/transactions'
                : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

            $response = Http::timeout(15) // 15 second timeout
                ->withBasicAuth($this->serverKey, '')
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($url, $params);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'snap_token' => $data['token'] ?? null,
                    'redirect_url' => $data['redirect_url'] ?? null,
                ];
            }

            Log::error('Midtrans Snap Token Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create snap token',
                'error' => $response->json()
            ];

        } catch (\Exception $e) {
            Log::error('Midtrans Exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Build transaction parameters
     * 
     * @param object $order Order model
     * @param object $user User model  
     * @param int $amount Total amount
     * @param string $orderId Unique order ID
     * @param array|null $enabledPayments Specific payment methods to enable (optional)
     * @return array
     */
    public function buildTransactionParams($order, $user, $amount, $orderId, $enabledPayments = null)
    {
        // Item details
        $itemDetails = [];
        
        // Get member count
        $memberCount = $order->members ? $order->members->count() + 1 : 1;
        $pricePerPerson = $order->total_harga_tiket;
        
        $itemDetails[] = [
            'id' => 'TICKET-' . $order->trail->id,
            'price' => (int) $pricePerPerson,
            'quantity' => $memberCount,
            'name' => substr('Tiket Pendakian ' . $order->mountain->nama . ' - ' . $order->trail->nama, 0, 50),
        ];

        // Customer details
        $customerDetails = [
            'first_name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone ? (string) $user->phone : '',
        ];

        // Transaction details
        $transactionDetails = [
            'order_id' => $orderId,
            'gross_amount' => (int) $amount,
        ];

        // Credit card options
        $creditCard = [
            'secure' => $this->is3ds,
        ];

        // If specific payment methods provided, use them; otherwise use all
        if ($enabledPayments === null) {
            $enabledPayments = [
                'credit_card',
                'gopay',
                'shopeepay', 
                'bank_transfer',
                'echannel',    // Mandiri Bill
                'bca_klikpay',
                'bca_va',
                'bni_va',
                'bri_va',
                'permata_va',
                'other_va',
                'indomaret',
                'alfamart',
                'akulaku',
                'kredivo',
            ];
        }

        // Callbacks
        $callbacks = [
            'finish' => config('app.url') . '/api/midtrans/finish',
        ];

        // Enforce payment expiry window so pending payments do not live forever.
        $customExpiry = [
            'order_time' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s O'),
            'expiry_duration' => $this->getPaymentExpiryDuration(),
            'unit' => $this->getPaymentExpiryUnit(),
        ];

        return [
            'transaction_details' => $transactionDetails,
            'item_details' => $itemDetails,
            'customer_details' => $customerDetails,
            'credit_card' => $creditCard,
            'enabled_payments' => $enabledPayments,
            'callbacks' => $callbacks,
            'custom_expiry' => $customExpiry,
        ];
    }

    /**
     * Get transaction status from Midtrans
     * 
     * @param string $orderId Midtrans order ID
     * @return array
     */
    public function getTransactionStatus($orderId)
    {
        try {
            $url = $this->isProduction
                ? "https://api.midtrans.com/v2/{$orderId}/status"
                : "https://api.sandbox.midtrans.com/v2/{$orderId}/status";

            $response = Http::timeout(10) // 10 second timeout
                ->withBasicAuth($this->serverKey, '')
                ->get($url);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to get transaction status',
                'error' => $response->json()
            ];

        } catch (\Exception $e) {
            Log::error('Midtrans Status Check Error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Verify notification signature
     * 
     * @param array $notification Notification data from Midtrans
     * @return bool
     */
    public function verifySignature($notification)
    {
        $orderId = $notification['order_id'] ?? '';
        $statusCode = $notification['status_code'] ?? '';
        $grossAmount = $notification['gross_amount'] ?? '';
        $signatureKey = $notification['signature_key'] ?? '';

        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);

        return $signatureKey === $expectedSignature;
    }

    /**
     * Get client key for frontend
     * 
     * @return string
     */
    public function getClientKey()
    {
        return $this->clientKey;
    }

    /**
     * Get snap URL for frontend
     * 
     * @return string
     */
    public function getSnapUrl()
    {
        return $this->snapUrl;
    }

    /**
     * Check if production mode
     * 
     * @return bool
     */
    public function isProduction()
    {
        return $this->isProduction;
    }

    /**
     * Build hosted payment page URL from snap token.
     */
    public function buildRedirectUrlFromSnapToken(?string $snapToken): ?string
    {
        if (empty($snapToken)) {
            return null;
        }

        $base = $this->isProduction
            ? 'https://app.midtrans.com/snap/v2/vtweb/'
            : 'https://app.sandbox.midtrans.com/snap/v2/vtweb/';

        return $base . $snapToken;
    }

    /**
     * Get payment expiry duration.
     */
    public function getPaymentExpiryDuration(): int
    {
        $duration = (int) config('midtrans.payment_expiry_duration', 60);
        return $duration > 0 ? $duration : 60;
    }

    /**
     * Get payment expiry unit with safe fallback.
     */
    public function getPaymentExpiryUnit(): string
    {
        $unit = strtolower((string) config('midtrans.payment_expiry_unit', 'minute'));
        $allowed = ['second', 'minute', 'hour', 'day'];

        return in_array($unit, $allowed, true) ? $unit : 'minute';
    }
}
