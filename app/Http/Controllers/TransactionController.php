<?php

namespace App\Http\Controllers;

use App\Models\TransactionWeb;
use App\Models\OrderWeb;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    // Display all transactions
    public function index(Request $request)
    {
        $search = $request->get('search');
        $transactions = TransactionWeb::query()
            ->with('payment')
            ->when($search, function ($query, $search) {
                return $query->where('id_pesanan', 'LIKE', "%{$search}%")
                    ->orWhereHas('payment', function ($q) use ($search) {
                        $q->where('nama_pembayaran', 'LIKE', "%{$search}%");
                    })
                    ->orWhere('status_pesanan', 'LIKE', "%{$search}%");
            })
            ->get();

        return view('transactions.index', compact('transactions'));
    }

    // Display transaction details
    public function show($id)
    {
        $transaction = TransactionWeb::with('payment')->findOrFail($id);
        return view('transactions.show', compact('transaction'));
    }

    // Verify transaction
    public function verify($id)
    {
        $transaction = TransactionWeb::findOrFail($id);

        if ($transaction->status_pesanan === 'Unverified') {
            $transaction->status_pesanan = 'Verified';
            $transaction->save();

            return redirect()->route('transactions.index')->with('success', 'Transaction verified successfully');
        }

        return redirect()->route('transactions.index')->with('error', 'Transaction cannot be verified');
    }

    // Unverify transaction
    public function unverify($id)
    {
        $transaction = TransactionWeb::findOrFail($id);

        if ($transaction->status_pesanan === 'Verified') {
            $transaction->status_pesanan = 'Unverified';
            $transaction->save();

            return redirect()->route('transactions.index')->with('success', 'Transaction unverified successfully');
        }

        return redirect()->route('transactions.index')->with('error', 'Transaction cannot be unverified');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_pesanan' => 'required|exists:orders,id',
            'payment_id' => 'required|exists:payments,id',
            'total_bayar' => 'required|integer',
            'waktu_pembayaran' => 'nullable|date',
            'bukti' => 'nullable|string',
        ]);

        $status = 'Incomplete';
        if (!empty($validatedData['bukti']) && !empty($validatedData['waktu_pembayaran'])) {
            $status = 'Unverified';
        }

        $transaction = TransactionWeb::create([
            'id_pesanan' => $validatedData['id_pesanan'],
            'payment_id' => $validatedData['payment_id'],
            'total_bayar' => $validatedData['total_bayar'],
            'waktu_pembayaran' => $validatedData['waktu_pembayaran'],
            'bukti' => $validatedData['bukti'],
            'status_pesanan' => $status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaction created successfully',
            'data' => $transaction,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $transaction = TransactionWeb::findOrFail($id);

        $validatedData = $request->validate([
            'id_pesanan' => 'nullable|exists:orders,id',
            'payment_id' => 'nullable|exists:payments,id',
            'total_bayar' => 'nullable|integer',
            'waktu_pembayaran' => 'nullable|date',
            'bukti' => 'nullable|string',
        ]);

        $transaction->update($validatedData);

        if (empty($transaction->bukti) || empty($transaction->waktu_pembayaran)) {
            $transaction->status_pesanan = 'Incomplete';
        } else if ($transaction->status_pesanan != 'Verified') {
            $transaction->status_pesanan = 'Unverified';
        }

        $transaction->save();

        return response()->json([
            'success' => true,
            'message' => 'Transaction updated successfully',
            'data' => $transaction,
        ], 200);
    }
}
