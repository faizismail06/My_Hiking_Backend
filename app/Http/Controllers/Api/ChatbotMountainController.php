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
            'regency_id'  => 'nullable|integer|exists:reg_regencies,id',
            'district_id' => 'nullable|integer|exists:reg_districts,id',
            'village_id'  => 'nullable|integer|exists:reg_villages,id',
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
            'regency_id'  => 'nullable|integer|exists:reg_regencies,id',
            'district_id' => 'nullable|integer|exists:reg_districts,id',
            'village_id'  => 'nullable|integer|exists:reg_villages,id',
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
     * Mengkonversi nama string wilayah (provinsi/kabupaten/kecamatan/desa)
     * menjadi ID numerik secara bertahap (cascade scoping).
     *
     * Urutan resolusi: provinsi → kabupaten → kecamatan (di-scope by kabupaten) → desa (di-scope by kecamatan).
     * Hal ini mencegah mismatched region seperti "Selo" jadi "Selomerto" di kabupaten lain.
     *
     * Field yang diterima dari request (string, optional):
     *   provinsi, province, kabupaten, regency, kecamatan, district, desa, village
     */
    private function resolveRegionIds(Request $request, array &$validated): void
    {
        // 1. Resolve Province
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

        // 2. Resolve Regency — scoped by province jika tersedia
        if (empty($validated['regency_id'])) {
            $name = $request->input('kabupaten') ?? $request->input('regency');
            if ($name) {
                $query = \App\Models\Regency::query();
                if (!empty($validated['province_id'])) {
                    $query->where('province_id', $validated['province_id']);
                }
                $row = (clone $query)->whereRaw('LOWER(name) = ?', [strtolower(trim($name))])->first()
                    ?? (clone $query)->where('name', 'LIKE', '%' . trim($name) . '%')->first();

                // Fallback tanpa scope province jika tidak ketemu
                if (!$row) {
                    $row = \App\Models\Regency::whereRaw('LOWER(name) = ?', [strtolower(trim($name))])->first()
                        ?? \App\Models\Regency::where('name', 'LIKE', '%' . trim($name) . '%')->first();
                }
                if ($row) {
                    $validated['regency_id'] = $row->id;
                    if (empty($validated['province_id'])) {
                        $validated['province_id'] = $row->province_id;
                    }
                }
            }
        }

        // 3. Resolve District — WAJIB di-scope by regency jika tersedia (cegah mismatched!)
        if (empty($validated['district_id'])) {
            $name = $request->input('kecamatan') ?? $request->input('district');
            if ($name) {
                $query = \App\Models\District::query();
                if (!empty($validated['regency_id'])) {
                    $query->where('regency_id', $validated['regency_id']);
                }
                $row = (clone $query)->whereRaw('LOWER(name) = ?', [strtolower(trim($name))])->first()
                    ?? (clone $query)->where('name', 'LIKE', '%' . trim($name) . '%')->first();
                if ($row) {
                    $validated['district_id'] = $row->id;
                }
            }
        }

        // 4. Resolve Village — WAJIB di-scope by district jika tersedia (cegah mismatched!)
        if (empty($validated['village_id'])) {
            $name = $request->input('desa') ?? $request->input('village');
            if ($name) {
                $query = \App\Models\Village::query();
                if (!empty($validated['district_id'])) {
                    $query->where('district_id', $validated['district_id']);
                }
                $row = (clone $query)->whereRaw('LOWER(name) = ?', [strtolower(trim($name))])->first()
                    ?? (clone $query)->where('name', 'LIKE', '%' . trim($name) . '%')->first();
                if ($row) {
                    $validated['village_id'] = $row->id;
                }
            }
        }
    }
}
