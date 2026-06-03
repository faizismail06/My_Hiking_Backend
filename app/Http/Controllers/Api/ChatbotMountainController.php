<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mountain;
use Illuminate\Http\Request;

/**
 * ChatbotMountainController
 *
 * Menyediakan endpoint CRUD gunung berbasis JSON untuk chatbot admin (My_Hiking_Python/tools.py).
 * Endpoint ini tidak memerlukan file upload; semua field dikirim sebagai JSON.
 * Diproteksi middleware chatbot.secret agar hanya bisa diakses oleh service chatbot.
 */
class ChatbotMountainController extends Controller
{
    /**
     * POST /api/mountains
     * Tambah gunung baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'        => 'required|string|max:255',
            'ketinggian'  => 'required|numeric|min:0',
            'deskripsi'   => 'nullable|string|max:1000',
            'province_id' => 'nullable|integer|exists:reg_provinces,id',
            'regency_id'  => 'nullable|integer|exists:reg_regencies,id',
            'district_id' => 'nullable|integer|exists:reg_districts,id',
            'village_id'  => 'nullable|integer|exists:reg_villages,id',
            'latitude'    => 'nullable|numeric|between:-90,90',
            'longitude'   => 'nullable|numeric|between:-180,180',
        ]);

        $mountain = Mountain::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Gunung berhasil ditambahkan.',
            'data'    => $mountain,
        ], 201);
    }

    /**
     * PUT /api/mountains/{id}
     * Update data gunung.
     */
    public function update(Request $request, $id)
    {
        $mountain = Mountain::findOrFail($id);

        $validated = $request->validate([
            'nama'        => 'sometimes|required|string|max:255',
            'ketinggian'  => 'sometimes|required|numeric|min:0',
            'deskripsi'   => 'nullable|string|max:1000',
            'province_id' => 'nullable|integer|exists:reg_provinces,id',
            'regency_id'  => 'nullable|integer|exists:reg_regencies,id',
            'district_id' => 'nullable|integer|exists:reg_districts,id',
            'village_id'  => 'nullable|integer|exists:reg_villages,id',
            'latitude'    => 'nullable|numeric|between:-90,90',
            'longitude'   => 'nullable|numeric|between:-180,180',
        ]);

        $mountain->update($validated);

        return response()->json([
            'success' => true,
            'message' => "Gunung ID {$id} berhasil diupdate.",
            'data'    => $mountain->fresh(),
        ]);
    }

    /**
     * DELETE /api/mountains/{id}
     * Hapus gunung.
     */
    public function destroy($id)
    {
        $mountain = Mountain::findOrFail($id);
        $mountain->delete();

        return response()->json([
            'success' => true,
            'message' => "Gunung ID {$id} berhasil dihapus.",
        ]);
    }
}
