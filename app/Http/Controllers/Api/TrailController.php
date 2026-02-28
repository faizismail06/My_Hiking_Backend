<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trail;
use App\Models\Mountain;
use Illuminate\Http\Request;

class TrailController extends Controller
{
    public function index($id_gunung)
    {
        $mountain = Mountain::with(['trails'])->findOrFail($id_gunung);
        $imageUrl = url('api/images/' . $mountain->gambar_gunung);

        return response()->json([
            'status' => true,
            'message' => 'Trails fetched successfully',
            'mountain' => [
                'id' => $mountain->id,
                'nama' => $mountain->nama,
                'gambar' => $imageUrl,
                'ketinggian' => $mountain->ketinggian,
                'province' => $mountain->province->name ?: null,
                'latitude' => $mountain->latitude,
                'longitude' => $mountain->longitude,
                'data' => $mountain->trails->map(function ($trail) {
                    return [
                        'id' => $trail->id,
                        'nama' => $trail->nama,
                        'deskripsi' => $trail->deskripsi,
                        'map_basecamp' => $trail->map_basecamp,
                        'village' => $trail->village ? $trail->village->name : null,
                        'district' => $trail->district ? $trail->district->name : null,
                        'regency' => $trail->regency ? $trail->regency->name : null,
                        'province' => $trail->province->name ?: null,
                        'jarak' => $trail->jarak,
                        'biaya' => $trail->biaya,
                        'latitude' => $trail->latitude,
                        'longitude' => $trail->longitude,
                    ];
                }),
            ]
        ]);
    }
}
