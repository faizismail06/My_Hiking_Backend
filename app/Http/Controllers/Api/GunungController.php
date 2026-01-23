<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Gunung;
use Illuminate\Http\Request;

class GunungController extends Controller
{
    public function index()
    {
        // $gunung = Gunung::all();
        // return response()->json($gunung);
        $gunungList = Gunung::with('province')->get();

        // Format response
        $result = $gunungList->map(function ($gunung) {
            // Menggabungkan URL dasar dengan nama gambar, asumsikan gambar disimpan dalam folder 'images'
            $imageUrl = url('storage/images/' . $gunung->gambar_gunung); // Sesuaikan dengan tempat penyimpanan gambar Anda

            return [
                'id' => $gunung->id,
                'nama' => $gunung->nama,
                'gambar' => $imageUrl,
                // Mengambil nama provinsi jika tersedia
                'province' => $gunung->province ? ['id' => $gunung->province->id, 'name' => $gunung->province->name] : null,
            ];
        });

        return response()->json($result);
    }
}

