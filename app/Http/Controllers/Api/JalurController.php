<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Jalur;
use App\Models\Gunung;
use Illuminate\Http\Request;

class JalurController extends Controller
{
    public function index($id_gunung)
    {
        $gunung = Gunung::with(['jalur'])->findOrFail($id_gunung);
        $imageUrl = url('storage/images/' . $gunung->gambar_gunung); // Sesuaikan dengan tempat penyimpanan gambar Anda

        return response()->json([
            'status' => true,
            'message' => 'Jalur fetched successfully',
            'gunung' => [
                'id' => $gunung->id,
                'nama' => $gunung->nama,
                'gambar' => $imageUrl,
                'ketinggian' => $gunung->ketinggian,
                'province' => $gunung->province->name ?: null,
                'data' => $gunung->jalur->map(function ($jalur) {
                    return [
                        'id' => $jalur->id,
                        'nama' => $jalur->nama,
                        'deskripsi' => $jalur->deskripsi,
                        'map_basecamp' => $jalur->map_basecamp,
                        'village' => $jalur->village ? $jalur->village->name : null, // Desa
                        'district' => $jalur->district ? $jalur->district->name : null, // Distrik
                        'regency' => $jalur->regency ? $jalur->regency->name : null, // Kabupaten
                        'province' => $jalur->province->name ?: null,
                        'jarak' => $jalur->jarak,
                        'biaya' => $jalur->biaya,
                    ];
                }),
            ]
        ]);
    }

    // public function jalur($id_gunung, $id_jalur)
    // {
    //     // Mencari jalur berdasarkan ID jalur dan memastikan relasi dengan gunung
    //     $jalur = Jalur::with(['gunung', 'village', 'district', 'regency', 'province'])
    //         ->where('id', $id_jalur)
    //         ->where('id_gunung', $id_gunung)
    //         ->first();

    //     // Mengecek apakah jalur ditemukan
    //     if (!$jalur) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Jalur not found or not associated with the specified gunung',
    //         ], 404); // Mengembalikan status 404 jika jalur tidak ditemukan
    //     }

    //     // Membuat array hasil yang diformat
    //     $result = [
    //         'id' => $jalur->id,
    //         'nama' => $jalur->nama,
    //         'deskripsi' => $jalur->deskripsi,
    //         'map_basecamp' => $jalur->map_basecamp,
    //         'village' => $jalur->village ? $jalur->village->name : null,
    //         'district' => $jalur->district ? $jalur->district->name : null,
    //         'regency' => $jalur->regency ? $jalur->regency->name : null,
    //         'province' => $jalur->province ? $jalur->province->name : null,
    //         'jarak' => $jalur->jarak,
    //         'biaya' => $jalur->biaya,
    //         'gunung' => [
    //             'id' => $jalur->gunung->id,
    //             'nama' => $jalur->gunung->nama,
    //             'ketinggian' => $jalur->gunung->ketinggian,
    //             'province' => $jalur->gunung->province ? $jalur->gunung->province->name : null,
    //         ],
    //     ];

    //     // Mengembalikan response JSON dengan data jalur
    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Jalur details fetched successfully',
    //         'jalur' => $result
    //     ]);
    // }

}
