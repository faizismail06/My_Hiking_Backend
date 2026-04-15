<?php

namespace App\Services;

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
    protected $is3ds;
    protected $snapUrl;

    public function __construct()
    {
        $this->serverKey = config('midtrans.server_key');
        $this->clientKey = config('midtrans.client_key');
        $this->isProduction = config('midtrans.is_production');
        $this->is3ds = config('midtrans.is_3ds');
        $this->snapUrl = config('midtrans.snap_url');
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
     * Create direct charge transaction (Core API).
     * Useful when app already knows the selected payment method.
     */
    public function createDirectCharge(array $params): array
    {
        try {
            $url = $this->isProduction
                ? 'https://api.midtrans.com/v2/charge'
                : 'https://api.sandbox.midtrans.com/v2/charge';

            $response = Http::timeout(15)
                ->withBasicAuth($this->serverKey, '')
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($url, $params);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to create direct charge',
                'error' => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error('Midtrans Direct Charge Error', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
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

        // Fixed payment expiry window to stay in sync with Flutter countdown.
        $expiry = [
            'start_time' => now()->format('Y-m-d H:i:s O'),
            'unit' => 'minute',
            'duration' => 15,
        ];

        return [
            'transaction_details' => $transactionDetails,
            'item_details' => $itemDetails,
            'customer_details' => $customerDetails,
            'credit_card' => $creditCard,
            'enabled_payments' => $enabledPayments,
            'callbacks' => $callbacks,
            'expiry' => $expiry,
        ];
    }

    /**
     * Build direct charge params based on selected payment method.
     */
    public function buildDirectChargeParams($order, $user, $amount, $orderId, string $paymentMethod): array
    {
        $transactionDetails = [
            'order_id' => $orderId,
            'gross_amount' => (int) $amount,
        ];

        $customerDetails = [
            'first_name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone ? (string) $user->phone : '',
        ];

        $customExpiry = [
            'order_time' => now()->format('Y-m-d H:i:s O'),
            'expiry_duration' => 15,
            'unit' => 'minute',
        ];

        $base = [
            'transaction_details' => $transactionDetails,
            'customer_details' => $customerDetails,
            'custom_expiry' => $customExpiry,
        ];

        $method = strtolower(trim($paymentMethod));

        return match ($method) {
            'bca_va', 'bni_va', 'bri_va', 'permata_va', 'cimb_va', 'bank_transfer' =>
                $this->buildBankTransferCharge($base, $method),
            'mandiri_va' => array_merge($base, [
                'payment_type' => 'echannel',
                'echannel' => [
                    'bill_info1' => 'Payment:',
                    'bill_info2' => 'MyHiking',
                ],
            ]),
            'indomaret', 'alfamart' => array_merge($base, [
                'payment_type' => 'cstore',
                'cstore' => [
                    'store' => $method,
                    'message' => 'Pembayaran tiket pendakian MyHiking',
                ],
            ]),
            'gopay' => array_merge($base, [
                'payment_type' => 'gopay',
                'gopay' => [
                    'enable_callback' => false,
                ],
            ]),
            'shopeepay' => array_merge($base, [
                'payment_type' => 'shopeepay',
            ]),
            'qris' => array_merge($base, [
                'payment_type' => 'qris',
                'qris' => [
                    'acquirer' => 'gopay',
                ],
            ]),
            default => throw new \InvalidArgumentException('Payment method not supported for direct charge: ' . $paymentMethod),
        };
    }

    protected function buildBankTransferCharge(array $base, string $method): array
    {
        $bank = match ($method) {
            'bca_va' => 'bca',
            'bni_va' => 'bni',
            'bri_va' => 'bri',
            'permata_va' => 'permata',
            'cimb_va' => 'cimb',
            default => 'bca',
        };

        return array_merge($base, [
            'payment_type' => 'bank_transfer',
            'bank_transfer' => [
                'bank' => $bank,
            ],
        ]);
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
        return 15;
    }

    /**
     * Get payment expiry unit with safe fallback.
     */
    public function getPaymentExpiryUnit(): string
    {
        return 'minute';
    }
}
