<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\Log;
use App\Models\Pesanan;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;

class PesananController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     try{
    //         $pesanan = Pesanan::all();

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Successfully get data on pesanan',
    //             'data' => $pesanan,
    //         ], 200);
    //     }catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to get data on pesanan',
    //             'data' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function index()
    {
        try {
            // Mengambil semua data transaksi dari database
            $pesanan = Pesanan::with("gunung", "jalur", "pemesan")->get()->map(function ($item) {
                // $status = 'Booking';
                // if ($item->status == 'Lunas') {
                //     $status = 'Selesai';
                // }

                return [
                    "id" => (string) $item->id,
                    "id_gunung" => $item->id_gunung,
                    "id_jalur" => $item->id_jalur,
                    "id_user" => $item->id_user,
                    "tanggal_naik" => $item->tanggal_naik,
                    "tanggal_turun" => $item->tanggal_turun,
                    "total_harga_tiket" => $item->total_harga_tiket,
                    "status" => $item->status,
                    "gunung" => $item->gunung->nama,
                    "jalur" => $item->jalur->nama,
                    "user" => $item->pemesan->name,
                ];
            });

            // Mengembalikan response dalam format JSON
            return response()->json([
                'success' => true,
                'message' => 'Successfully get data on pesanan',
                'data' => $pesanan,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get data on pesanan',
                'data' => $e->getMessage(),
            ], 500);
        }
    }


    // Membuat pesanan baru dan menambahkan anggota
    public function buatPesanan(Request $request)
    {
        Log::info('Permintaan diterima:', $request->all());
        $request->validate([
            'id_gunung' => 'required|exists:gunung,id',
            'id_jalur' => 'required|exists:jalur,id',
            'id_user' => 'required|exists:users,id', // ID user pemesan utama
            'tanggal_naik' => 'required|date', // Tanggal pemesanan
            'tanggal_turun' => 'required|date', // Tanggal pemesanan
            'total_harga_tiket' => 'required|numeric',    // Total harga
            'anggota_ids' => 'array',              // ID anggota yang akan ditambahkan
            'anggota_ids.*' => 'exists:users,id',  // Validasi setiap ID anggota
        ]);

        try {
            // Membuat pesanan
            $pesanan = Pesanan::create([
                'id_gunung' => $request->id_gunung,
                'id_jalur' => $request->id_jalur,
                'id_user' => $request->id_user,
                'tanggal_naik' => $request->tanggal_naik,
                'tanggal_turun' => $request->tanggal_turun,
                'total_harga_tiket' => $request->total_harga_tiket,
            ]);

            // Menambahkan anggota jika ada
            if (!empty($request->anggota_ids)) {
                $pesanan->anggota()->attach($request->anggota_ids);
            }

            return response()->json([
                'message' => 'Pesanan berhasil dibuat!',
                'pesanan' => $pesanan->load('anggota'),
                // 'anggota' => $pesanan->anggota,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat membuat pesanan.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Menambahkan anggota ke pesanan yang sudah ada
    public function tambahAnggota(Request $request, $pesananId)
    {
        $request->validate([
            'anggota_ids' => 'required|array',
            'anggota_ids.*' => 'exists:users,id',
        ]);

        try {
            $pesanan = Pesanan::findOrFail($pesananId);

            // Tambahkan anggota ke pesanan
            $pesanan->anggota()->attach($request->anggota_ids);

            return response()->json([
                'message' => 'Anggota berhasil ditambahkan ke pesanan.',
                'pesanan' => $pesanan,
                // 'anggota' => $pesanan->anggota,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat menambahkan anggota.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Melihat detail pesanan beserta anggota
    public function lihatPesanan($pesananId)
    {
        try {
            $pesanan = Pesanan::with('gunung:id,nama', 'jalur:id,nama', 'pemesan:id,name', 'anggota')->findOrFail($pesananId);

            return response()->json([
                'pesanan' => $pesanan,
                // 'anggota' => $pesanan->anggota,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Pesanan tidak ditemukan.',
                'error' => $e->getMessage(),
            ], 404);
        }
    }
    public function getDetailPesanan($pesananId)
    {
        try {
            $pesanan = Pesanan::with(['pemesan:id,name', 'anggota', 'transaksi'])
                ->findOrFail($pesananId);

            if (!$pesanan->transaksi) {
                throw new \Exception('Transaksi tidak ditemukan untuk pesanan ini.');
            }

            $detailPesanan = [
                'id_pesanan' => $pesanan->id,
                'tanggal_pesanan' => $pesanan->tanggal_naik,
                'nama_pemesan' => $pesanan->pemesan->name,
                'total_anggota' => $pesanan->anggota->count() + 1, // +1 for the main booker
                'total_harga' => $pesanan->transaksi->total_bayar,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mengambil detail pesanan',
                'data' => $detailPesanan,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail pesanan',
                'error' => $e->getMessage(),
            ], 500);
        }
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
    public function show(Pesanan $pesanan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pesanan $pesanan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pesanan $pesanan)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            // Cari pesanan berdasarkan ID
            $pesanan = Pesanan::findOrFail($id);

            // Cari transaksi terkait berdasarkan id_pesanan
            $transaksi = Transaksi::where('id_pesanan', $pesanan->id)->first();

            // Hapus transaksi jika ada
            if ($transaksi) {
                $transaksi->delete();
            }

            // Hapus pesanan
            $pesanan->delete();

            return response()->json([
                'message' => 'Pesanan dan transaksi terkait berhasil dihapus.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat menghapus pesanan.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
