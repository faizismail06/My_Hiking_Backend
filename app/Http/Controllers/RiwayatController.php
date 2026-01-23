<?php

namespace App\Http\Controllers;


use App\Models\PesananWeb;
use Illuminate\Http\Request;

class RiwayatController extends Controller
{
    // Menampilkan daftar riwayat pesanan
    public function index(Request $request)
{
    $query = PesananWeb::with(['user:id,name', 'anggotaPesanan.user']) // Memuat relasi user dan anggotaPesanan
        ->select('id', 'tanggal_naik', 'tanggal_turun', 'status', 'id_user'); // Menambahkan 'id_user' dalam query

    // Filter berdasarkan ID pesanan
    if ($request->filled('search')) {
        $query->where('id', $request->input('search'));
    }

    // Filter berdasarkan status
    if ($request->filled('status')) {
        $query->where('status', $request->input('status'));
    }

    $pesanan = $query->get();

    return view('riwayat.index', ['pesanan' => $pesanan]);
}


    // Menampilkan detail pesanan dan anggota pesanan
    public function show($id)
{
    $pesanan = PesananWeb::with(['gunung:id,nama', 'jalur:id,nama', 'anggotaPesanan.user', 'user:id,name']) // Memuat relasi user
        ->findOrFail($id);

    return view('riwayat.show', compact('pesanan'));
}


    // Mengupdate status pesanan
    public function updateStatus(Request $request, $id)
    {
        $pesanan = PesananWeb::findOrFail($id);
        $pesanan->status = $request->input('status'); // Ambil status baru dari tombol
        $pesanan->save();

        return redirect()->route('riwayat.show', $id)->with('success', 'Status pesanan berhasil diperbarui!');
    }

    // Scanner untuk update status pesanan
    public function scan(Request $request)
    {
        \Log::info('Scan request received', $request->all());

        $id = $request->input('id');
        $pesanan = PesananWeb::find($id);

        if (!$pesanan) {
            \Log::error("Pesanan dengan ID {$id} tidak ditemukan");
            return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan']);
        }

        \Log::info("Pesanan ditemukan dengan status {$pesanan->status}");

        switch ($pesanan->status) {
            case 'Booking':
                $pesanan->status = 'Sedang Mendaki';
                break;
            case 'Sedang Mendaki':
                $pesanan->status = 'Selesai';
                break;
            default:
                \Log::warning("Status {$pesanan->status} tidak dikenali");
                return response()->json(['success' => false, 'message' => 'Status tidak dapat diperbarui lebih lanjut']);
        }

        $pesanan->save();
        \Log::info("Status pesanan berhasil diperbarui menjadi {$pesanan->status}");

        return response()->json(['success' => true]);
    }
}
