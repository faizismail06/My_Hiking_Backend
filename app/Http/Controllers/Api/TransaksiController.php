<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\Pesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class TransaksiController extends Controller
{

    public function index()
    {
        try {
            // Mengambil semua data transaksi dengan relasi terkait
            $transaksi = Transaksi::with("pesanan.gunung", "pesanan.jalur", "pesanan.anggota:id", "pesanan.pemesan", "payment")->get()->map(function ($item) {
                return [
                    "id" => (string) $item->id,
                    "id_pesanan" => $item->id_pesanan,
                    "payment" => $item->payment->nama_pembayaran, // Nama payment dari relasi
                    "total_bayar" => $item->total_bayar,
                    "status" => $item->status_pesanan,
                    "waktu_pembayaran" => $item->waktu_pembayaran,
                    "bukti" => $item->bukti,
                    "gunung" => $item->pesanan->gunung->nama,
                    "jalur" => $item->pesanan->jalur->nama,
                    "pemesan" => $item->pesanan->pemesan->id,
                    "anggota" => $item->pesanan->anggota
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Successfully get data on transaksi',
                'data' => $transaksi,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get data on transaksi',
                'data' => $e->getMessage(),
            ], 500);
        }
    }


    public function store(Request $request)
    {
        // Validasi data input
        $validator = Validator::make($request->all(), [
            'id_pesanan' => 'required|exists:pesanan,id',
            'payment_id' => 'required|exists:payments,id', // Validasi payment_id
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Ambil data pesanan berdasarkan id_pesanan
            $pesanan = Pesanan::with('anggota')->findOrFail($request->id_pesanan);

            // Hitung jumlah anggota (termasuk pemesan)
            $jumlahAnggota = count($pesanan->anggota) + 1;

            // Hitung total bayar
            $totalBayar = $jumlahAnggota * $pesanan->total_harga_tiket;

            // Tambahkan data transaksi awal
            $transaksi = Transaksi::create([
                'id_pesanan' => $request->id_pesanan,
                'payment_id' => $request->payment_id,
                'total_bayar' => $totalBayar,
                'status_pesanan' => 'Incomplete',
                'waktu_pembayaran' => null,
                'bukti' => null,
            ]);

            return response()->json([
                'message' => 'Transaksi berhasil dibuat.',
                'transaksi' => $transaksi,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat membuat transaksi.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function updatePembayaran(Request $request, $id)
    {
        // Validasi data input
        $validator = Validator::make($request->all(), [
            'bukti' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Maksimal ukuran 2 MB
            'waktu_pembayaran' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Cari transaksi berdasarkan ID
            $transaksi = Transaksi::findOrFail($id);

            // Simpan file bukti pembayaran
            $filePath = $request->file('bukti')->store('bukti_pembayaran', 'public');

            // Update data transaksi
            $transaksi->update([
                'bukti' => $filePath,
                'waktu_pembayaran' => now(),
                'status_pesanan' => 'Unverified', // Ubah status menjadi Verified
            ]);

            return response()->json([
                'message' => 'Pembayaran berhasil diperbarui.',
                'transaksi' => $transaksi,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui pembayaran.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function getTransactionWithPayment($transactionId)
    {
        // Ambil transaksi berdasarkan ID dan relasi dengan payment
        $transaction = Transaksi::with('payment')->find($transactionId);

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found',
            ], 404);
        }

        // Gabungkan data transaksi dan payment
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
