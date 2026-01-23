<?php

namespace App\Http\Controllers\Api;

use App\Models\AnggotaPesanan;
use App\Models\Pesanan;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AnggotaPesananController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    // Menambah anggota ke pesanan
    public function tambahAnggota(Request $request, $pesananId)
    {
        $request->validate([
            'id_user' => 'required|exists:users,id', // ID anggota yang akan ditambahkan
        ]);

        try {
            $pesanan = Pesanan::findOrFail($pesananId);

            // Tambahkan anggota
            $pesanan->anggota()->attach($request->id_user);

            return response()->json([
                'message' => 'Anggota berhasil ditambahkan.',
                'anggota' => $pesanan->anggota,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Menghapus anggota dari pesanan
    public function hapusAnggota($pesananId, $userId)
    {
        try {
            $pesanan = Pesanan::findOrFail($pesananId);

            // Memastikan pesanan tidak dalam status selesai
            if ($pesanan->status == 'Selesai') {
                return response()->json([
                    'message' => 'Anggota tidak dapat dihapus karena pesanan telah selesai.',
                ], 400);
            }

            // Hapus anggota dari pesanan
            $pesanan->anggota()->detach($userId);

            return response()->json([
                'message' => 'Anggota berhasil dihapus.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat menghapus anggota.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    // Melihat daftar anggota pesanan
    public function daftarAnggota($pesananId)
    {
        try {
            $pesanan = Pesanan::with('anggota')->findOrFail($pesananId);

            return response()->json([
                // 'pesanan' => $pesanan,
                'anggota' => $pesanan->anggota,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Pesanan tidak ditemukan.',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(AnggotaPesanan $anggotaPesanan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AnggotaPesanan $anggotaPesanan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AnggotaPesanan $anggotaPesanan)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AnggotaPesanan $anggotaPesanan)
    {
        //
    }
}
