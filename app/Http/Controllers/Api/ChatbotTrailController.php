<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trail;
use Illuminate\Http\Request;

/**
 * ChatbotTrailController
 *
 * Menyediakan endpoint CRUD jalur berbasis JSON untuk chatbot admin (My_Hiking_Python/tools.py).
 * Endpoint ini tidak memerlukan file upload; semua field dikirim sebagai JSON.
 * Diproteksi middleware chatbot.secret agar hanya bisa diakses oleh service chatbot.
 *
 * Catatan: tabel jalur bernama 'routes' di database (lihat Trail model).
 *          tools.py memanggil endpoint dengan prefix /api/routes.
 */
class ChatbotTrailController extends Controller
{
    /**
     * POST /api/routes
     * Tambah jalur baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_gunung'        => 'required|integer|exists:mountains,id',
            'nama'             => 'required|string|max:255',
            'province_id'      => 'nullable|integer|exists:reg_provinces,id',
            'regency_id'       => 'nullable|integer|exists:reg_regencies,id',
            'district_id'      => 'nullable|integer|exists:reg_districts,id',
            'village_id'       => 'nullable|integer|exists:reg_villages,id',
            'jarak'            => 'required|numeric|min:0',
            'elevasi'          => 'nullable|numeric|min:0',
            'durasi'           => 'nullable|numeric|min:0',
            'tingkat_kesulitan'=> 'nullable|in:mudah,sedang,sulit,sangat_sulit',
            'deskripsi'        => 'nullable|string|max:1000',
            'map_basecamp'     => 'nullable|string|max:255',
            'biaya'            => 'required|numeric|min:0',
            'daily_hiker_limit'=> 'nullable|integer|min:1',
            'is_refund_allowed'=> 'nullable|boolean',
            'latitude'         => 'nullable|numeric|between:-90,90',
            'longitude'        => 'nullable|numeric|between:-180,180',
            'panorama_score'   => 'nullable|integer|min:1|max:5',
            'fasilitas_score'  => 'nullable|integer|min:1|max:5',
            'safety_score'     => 'nullable|integer|min:1|max:5',
            'crowd_level'      => 'nullable|integer|min:1|max:5',
            'popularity_score' => 'nullable|numeric|min:0',
        ]);

        // Set default DSS status supaya jalur langsung aktif setelah dibuat chatbot
        $validated['dss_status'] = 'approved';

        $trail = Trail::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Jalur berhasil ditambahkan.',
            'data'    => $trail,
        ], 201);
    }

    /**
     * PUT /api/routes/{id}
     * Update data jalur.
     */
    public function update(Request $request, $id)
    {
        $trail = Trail::findOrFail($id);

        $validated = $request->validate([
            'id_gunung'        => 'sometimes|required|integer|exists:mountains,id',
            'nama'             => 'sometimes|required|string|max:255',
            'province_id'      => 'nullable|integer|exists:reg_provinces,id',
            'regency_id'       => 'nullable|integer|exists:reg_regencies,id',
            'district_id'      => 'nullable|integer|exists:reg_districts,id',
            'village_id'       => 'nullable|integer|exists:reg_villages,id',
            'jarak'            => 'sometimes|required|numeric|min:0',
            'elevasi'          => 'nullable|numeric|min:0',
            'durasi'           => 'nullable|numeric|min:0',
            'tingkat_kesulitan'=> 'nullable|in:mudah,sedang,sulit,sangat_sulit',
            'deskripsi'        => 'nullable|string|max:1000',
            'map_basecamp'     => 'nullable|string|max:255',
            'biaya'            => 'sometimes|required|numeric|min:0',
            'daily_hiker_limit'=> 'nullable|integer|min:1',
            'is_refund_allowed'=> 'nullable|boolean',
            'latitude'         => 'nullable|numeric|between:-90,90',
            'longitude'        => 'nullable|numeric|between:-180,180',
            'panorama_score'   => 'nullable|integer|min:1|max:5',
            'fasilitas_score'  => 'nullable|integer|min:1|max:5',
            'safety_score'     => 'nullable|integer|min:1|max:5',
            'crowd_level'      => 'nullable|integer|min:1|max:5',
            'popularity_score' => 'nullable|numeric|min:0',
        ]);

        $trail->update($validated);

        return response()->json([
            'success' => true,
            'message' => "Jalur ID {$id} berhasil diupdate.",
            'data'    => $trail->fresh(),
        ]);
    }

    /**
     * DELETE /api/routes/{id}
     * Hapus jalur.
     */
    public function destroy($id)
    {
        $trail = Trail::findOrFail($id);
        $trail->delete();

        return response()->json([
            'success' => true,
            'message' => "Jalur ID {$id} berhasil dihapus.",
        ]);
    }
}
