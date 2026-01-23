<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    public function index()
    {
        try {
            $payments = Payment::with('transaksi')->get();

            return response()->json([
                'success' => true,
                'message' => 'Successfully fetched all payments.',
                'data' => $payments,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payments.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_pembayaran' => 'required|string|max:100|unique:payments,nama_pembayaran',
            'nomor_pembayaran' => 'required|string|max:50|unique:payments,nomor_pembayaran',
            'gambar_pembayaran' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $path = null;
            if ($request->hasFile('gambar_pembayaran')) {
                $path = $request->file('gambar_pembayaran')->store('payments', 'public');
            }

            $payment = Payment::create([
                'nama_pembayaran' => $request->nama_pembayaran,
                'nomor_pembayaran' => $request->nomor_pembayaran,
                'gambar_pembayaran' => $path,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment successfully created.',
                'data' => $payment,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama_pembayaran' => 'sometimes|string|max:100|unique:payments,nama_pembayaran,' . $id,
            'nomor_pembayaran' => 'sometimes|string|max:50|unique:payments,nomor_pembayaran,' . $id,
            'gambar_pembayaran' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $payment = Payment::findOrFail($id);

            if ($request->hasFile('gambar_pembayaran')) {
                // Hapus gambar lama jika ada
                if ($payment->gambar_pembayaran) {
                    Storage::disk('public')->delete($payment->gambar_pembayaran);
                }

                // Simpan gambar baru
                $path = $request->file('gambar_pembayaran')->store('payments', 'public');
                $payment->update(['gambar_pembayaran' => $path]);
            }

            $payment->update($request->only(['nama_pembayaran', 'nomor_pembayaran']));

            return response()->json([
                'success' => true,
                'message' => 'Payment successfully updated.',
                'data' => $payment,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $payment = Payment::findOrFail($id);

            if ($payment->gambar_pembayaran) {
                Storage::disk('public')->delete($payment->gambar_pembayaran);
            }

            $payment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Payment successfully deleted.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete payment.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
