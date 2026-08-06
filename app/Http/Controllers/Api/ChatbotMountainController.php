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
 *
 * Lokasi wilayah dikirim AI sebagai string nama (kabupaten, kecamatan, desa, provinsi).
 * resolveRegionIds mengubahnya jadi ID numerik secara bertahap (cascade scoping).
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
            'latitude'    => 'nullable|numeric|between:-90,90',
            'longitude'   => 'nullable|numeric|between:-180,180',
        ]);

        $this->resolveRegionIds($request, $validated);

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
            'latitude'    => 'nullable|numeric|between:-90,90',
            'longitude'   => 'nullable|numeric|between:-180,180',
        ]);

        $this->resolveRegionIds($request, $validated);

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

    /**
     * Mengkonversi nama string wilayah (provinsi)
     * menjadi ID numerik.
     *
     * Field yang diterima dari request (string, optional):
     *   provinsi, province
     */
    private function resolveRegionIds(Request $request, array &$validated): void
    {
        // Resolve Province
        if (empty($validated['province_id'])) {
            $name = $request->input('provinsi') ?? $request->input('province');
            if ($name) {
                $row = \App\Models\Province::whereRaw('LOWER(name) = ?', [strtolower(trim($name))])->first()
                    ?? \App\Models\Province::where('name', 'LIKE', '%' . trim($name) . '%')->first();
                if ($row) {
                    $validated['province_id'] = $row->id;
                }
            }
        }
    }
}
