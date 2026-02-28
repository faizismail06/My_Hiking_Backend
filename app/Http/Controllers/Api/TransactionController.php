<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class TransactionController extends Controller
{
    public function index()
    {
        try {
            // Get all transaction data with related relationships
            $transactions = Transaction::with("order.mountain", "order.trail", "order.members:id", "order.booker", "payment")->get()->map(function ($item) {
                return [
                    "id" => (string) $item->id,
                    "id_pesanan" => $item->id_pesanan,
                    "payment" => $item->payment->nama_pembayaran,
                    "total_bayar" => $item->total_bayar,
                    "status" => $item->status_pesanan,
                    "waktu_pembayaran" => $item->waktu_pembayaran,
                    "bukti" => $item->bukti,
                    "gunung" => $item->order->mountain->nama,
                    "jalur" => $item->order->trail->nama,
                    "pemesan" => $item->order->booker->id,
                    "anggota" => $item->order->members
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


    public function store(Request $request)
    {
        // Validate input data
        $validator = Validator::make($request->all(), [
            'id_pesanan' => 'required|exists:orders,id',
            'payment_id' => 'required|exists:payments,id',
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
                'payment_id' => $request->payment_id,
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
    
    public function getTransactionWithPayment($transactionId)
    {
        // Get transaction by ID with payment relation
        $transaction = Transaction::with('payment')->find($transactionId);

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found',
            ], 404);
        }

        // Combine transaction and payment data
        $data = [
            'id' => $transaction->id,
            'id_pesanan' => $transaction->id_pesanan,
            'total_bayar' => $transaction->total_bayar,
            'status_pesanan' => $transaction->status_pesanan,
            'waktu_pembayaran' => $transaction->waktu_pembayaran,
            'bukti' => $transaction->bukti,
            'payment' => [
                'id' => $transaction->payment->id,
                'nama_pembayaran' => $transaction->payment->nama_pembayaran,
                'gambar_pembayaran' => $transaction->payment->gambar_pembayaran,
                'nomor_pembayaran' => $transaction->payment->nomor_pembayaran,
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
        ], 200);
    }
}
