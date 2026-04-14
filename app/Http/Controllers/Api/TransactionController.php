<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Order;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class TransactionController extends Controller
{
    protected MidtransService $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    public function index()
    {
        try {
            // Get all transaction data with related relationships
            $transactions = Transaction::with("order.mountain", "order.trail", "order.members:id", "order.booker")
                ->orderBy('id')
                ->get()
                ->map(function ($item) {
                $this->syncTransactionStatusFromMidtrans($item);
                $item->refresh();

                return [
                    "id" => (string) $item->id,
                    "id_pesanan" => $item->id_pesanan,
                    "payment_type" => $item->payment_type,
                    "payment_method" => $item->payment_method_name, // From accessor
                    "payment_method_name" => $item->payment_method_name,
                    "total_bayar" => $item->total_bayar,
                    "status" => $item->status_pesanan,
                    "waktu_pembayaran" => $item->waktu_pembayaran,
                    "bukti" => $item->bukti,
                    "gunung" => $item->order->mountain->nama ?? null,
                    "jalur" => $item->order->trail->nama ?? null,
                    "pemesan" => $item->order->booker->id ?? null,
                    "anggota" => $item->order->members ?? []
                ];
            });

            return response()->json([

    
                'success' => true,
                'message' => 'Successfully get data on transactions',
                'data' => $transactions,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get data on transactions',
                'data' => $e->getMessage(),
            ], 500);
        }
    }
private function syncTransactionStatusFromMidtrans(Transaction $transaction): void
    {
        if (empty($transaction->midtrans_order_id)) {
            return;
        }

        if ($transaction->status_pesanan === 'Complete') {
            return;
        }

        $result = $this->midtransService->getTransactionStatus($transaction->midtrans_order_id);
        if (!($result['success'] ?? false)) {
            return;
        }

        $data = $result['data'] ?? [];
        $status = $data['transaction_status'] ?? 'pending';
        $fraudStatus = $data['fraud_status'] ?? null;

        $newStatus = match ($status) {
            'capture' => $fraudStatus === 'challenge' ? 'Incomplete' : 'Complete',
            'settlement' => 'Complete',
            default => 'Incomplete',
        };

        $transaction->update([
            'status_pesanan' => $newStatus,
            'payment_type' => $data['payment_type'] ?? $transaction->payment_type,
            'transaction_id' => $data['transaction_id'] ?? $transaction->transaction_id,
            'transaction_time' => $data['transaction_time'] ?? $transaction->transaction_time,
            'fraud_status' => $fraudStatus ?? $transaction->fraud_status,
            'waktu_pembayaran' => $newStatus === 'Complete'
                ? ($transaction->waktu_pembayaran ?? now())
                : null,
        ]);

        if ($newStatus === 'Complete' && $transaction->order && $transaction->order->status !== 'Booking') {
            $transaction->order->update(['status' => 'Booking']);
        } elseif (in_array($status, ['expire', 'cancel'], true) && $transaction->order) {
            $transaction->order->update(['status' => 'Expired']);
        }
    }

    public function store(Request $request)
    {
        // Validate input data - only id_pesanan required, payment handled by Midtrans
        $validator = Validator::make($request->all(), [
            'id_pesanan' => 'required|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Get order data based on id_pesanan
            $order = Order::with('members')->findOrFail($request->id_pesanan);

            // Calculate member count (including booker)
            $memberCount = count($order->members) + 1;

            // Calculate total payment
            $totalPayment = $memberCount * $order->total_harga_tiket;

            // Create initial transaction
            $transaction = Transaction::create([
                'id_pesanan' => $request->id_pesanan,
                'total_bayar' => $totalPayment,
                'status_pesanan' => 'Incomplete',
                'waktu_pembayaran' => null,
                'bukti' => null,
            ]);

            return response()->json([
                'message' => 'Transaction created successfully.',
                'transaction' => $transaction,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the transaction.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function updatePayment(Request $request, $id)
    {
        // Validate input data
        $validator = Validator::make($request->all(), [
            'bukti' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'waktu_pembayaran' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Find transaction by ID
            $transaction = Transaction::findOrFail($id);

            // Save payment proof file
            $filePath = $request->file('bukti')->store('bukti_pembayaran', 'public');

            // Update transaction data
            $transaction->update([
                'bukti' => $filePath,
                'waktu_pembayaran' => now(),
                'status_pesanan' => 'Unverified',
            ]);

            return response()->json([
                'message' => 'Payment updated successfully.',
                'transaction' => $transaction,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating payment.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    public function getTransactionDetail($transactionId)
    {
        // Get transaction by ID with order relation
        $transaction = Transaction::with('order.mountain', 'order.trail')->find($transactionId);

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found',
            ], 404);
        }

        // Combine transaction data with Midtrans payment info
        $data = [
            'id' => $transaction->id,
            'id_pesanan' => $transaction->id_pesanan,
            'total_bayar' => $transaction->total_bayar,
            'status_pesanan' => $transaction->status_pesanan,
            'waktu_pembayaran' => $transaction->waktu_pembayaran,
            'bukti' => $transaction->bukti,
            'payment_type' => $transaction->payment_type,
            'payment_method' => $transaction->payment_method_name,
            'midtrans_order_id' => $transaction->midtrans_order_id,
            'transaction_id' => $transaction->transaction_id,
            'order' => [
                'gunung' => $transaction->order->mountain->nama ?? null,
                'jalur' => $transaction->order->trail->nama ?? null,
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
        ], 200);
    }

    /**
     * Get available Midtrans payment methods
     */
    public function getPaymentMethods()
    {
        $paymentMethods = [
            [
                'id' => 'gopay',
                'name' => 'GoPay',
                'type' => 'e_wallet',
                'icon' => 'gopay.png',
                'description' => 'Bayar dengan GoPay',
            ],
            [
                'id' => 'shopeepay',
                'name' => 'ShopeePay',
                'type' => 'e_wallet',
                'icon' => 'shopeepay.png',
                'description' => 'Bayar dengan ShopeePay',
            ],
            [
                'id' => 'qris',
                'name' => 'QRIS',
                'type' => 'e_wallet',
                'icon' => 'qris.png',
                'description' => 'Scan QR untuk bayar',
            ],
            [
                'id' => 'bca_va',
                'name' => 'BCA Virtual Account',
                'type' => 'bank_transfer',
                'icon' => 'bca.png',
                'description' => 'Transfer via BCA Virtual Account',
            ],
            [
                'id' => 'bni_va',
                'name' => 'BNI Virtual Account',
                'type' => 'bank_transfer',
                'icon' => 'bni.png',
                'description' => 'Transfer via BNI Virtual Account',
            ],
            [
                'id' => 'bri_va',
                'name' => 'BRI Virtual Account',
                'type' => 'bank_transfer',
                'icon' => 'bri.png',
                'description' => 'Transfer via BRI Virtual Account',
            ],
            [
                'id' => 'mandiri_va',
                'name' => 'Mandiri Virtual Account',
                'type' => 'bank_transfer',
                'icon' => 'mandiri.png',
                'description' => 'Transfer via Mandiri Virtual Account',
            ],
            [
                'id' => 'permata_va',
                'name' => 'Permata Virtual Account',
                'type' => 'bank_transfer',
                'icon' => 'permata.png',
                'description' => 'Transfer via Permata Virtual Account',
            ],
            [
                'id' => 'cimb_va',
                'name' => 'CIMB Virtual Account',
                'type' => 'bank_transfer',
                'icon' => 'cimb.png',
                'description' => 'Transfer via CIMB Virtual Account',
            ],
            [
                'id' => 'indomaret',
                'name' => 'Indomaret',
                'type' => 'cstore',
                'icon' => 'indomaret.png',
                'description' => 'Bayar di Indomaret terdekat',
            ],
            [
                'id' => 'alfamart',
                'name' => 'Alfamart',
                'type' => 'cstore',
                'icon' => 'alfamart.png',
                'description' => 'Bayar di Alfamart terdekat',
            ],
            [
                'id' => 'credit_card',
                'name' => 'Kartu Kredit/Debit',
                'type' => 'credit_card',
                'icon' => 'credit_card.png',
                'description' => 'Visa, Mastercard, JCB',
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $paymentMethods,
        ], 200);
    }
}
