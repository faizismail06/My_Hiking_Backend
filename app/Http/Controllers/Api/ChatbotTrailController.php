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
 *
 * Lokasi wilayah dikirim AI sebagai string nama (kabupaten, kecamatan, desa, provinsi).
 * resolveRegionIds mengubahnya jadi ID numerik secara bertahap (cascade scoping).
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
            'id_gunung'         => 'required|integer|exists:mountains,id',
            'nama'              => 'required|string|max:255',
            'province_id'       => 'nullable|integer|exists:reg_provinces,id',
            'regency_id'        => 'nullable|integer|exists:reg_regencies,id',
            'district_id'       => 'nullable|integer|exists:reg_districts,id',
            'village_id'        => 'nullable|integer|exists:reg_villages,id',
            'jarak'             => 'required|numeric|min:0',
            'elevasi'           => 'nullable|numeric|min:0',
            'durasi'            => 'nullable|numeric|min:0',
            'tingkat_kesulitan' => 'nullable|in:mudah,sedang,sulit,sangat_sulit',
            'deskripsi'         => 'nullable|string|max:1000',
            'map_basecamp'      => 'nullable|string|max:255',
            'biaya'             => 'required|numeric|min:0',
            'daily_hiker_limit' => 'nullable|integer|min:1',
            'is_refund_allowed' => 'nullable|boolean',
            'latitude'          => 'nullable|numeric|between:-90,90',
            'longitude'         => 'nullable|numeric|between:-180,180',
            'panorama_score'    => 'nullable|integer|min:1|max:5',
            'fasilitas_score'   => 'nullable|integer|min:1|max:5',
            'safety_score'      => 'nullable|integer|min:1|max:5',
            'crowd_level'       => 'nullable|integer|min:1|max:5',
            'popularity_score'  => 'nullable|numeric|min:0',
        ]);

        $this->resolveRegionIds($request, $validated);

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
            'id_gunung'         => 'sometimes|required|integer|exists:mountains,id',
            'nama'              => 'sometimes|required|string|max:255',
            'province_id'       => 'nullable|integer|exists:reg_provinces,id',
            'regency_id'        => 'nullable|integer|exists:reg_regencies,id',
            'district_id'       => 'nullable|integer|exists:reg_districts,id',
            'village_id'        => 'nullable|integer|exists:reg_villages,id',
            'jarak'             => 'sometimes|required|numeric|min:0',
            'elevasi'           => 'nullable|numeric|min:0',
            'durasi'            => 'nullable|numeric|min:0',
            'tingkat_kesulitan' => 'nullable|in:mudah,sedang,sulit,sangat_sulit',
            'deskripsi'         => 'nullable|string|max:1000',
            'map_basecamp'      => 'nullable|string|max:255',
            'biaya'             => 'sometimes|required|numeric|min:0',
            'daily_hiker_limit' => 'nullable|integer|min:1',
            'is_refund_allowed' => 'nullable|boolean',
            'latitude'          => 'nullable|numeric|between:-90,90',
            'longitude'         => 'nullable|numeric|between:-180,180',
            'panorama_score'    => 'nullable|integer|min:1|max:5',
            'fasilitas_score'   => 'nullable|integer|min:1|max:5',
            'safety_score'      => 'nullable|integer|min:1|max:5',
            'crowd_level'       => 'nullable|integer|min:1|max:5',
            'popularity_score'  => 'nullable|numeric|min:0',
        ]);

        $this->resolveRegionIds($request, $validated);

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
