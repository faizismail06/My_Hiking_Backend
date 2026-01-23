<?php

namespace App\Http\Controllers;

use App\Models\DistrictWeb;
use App\Models\GunungWeb;
use App\Models\JalurWeb;
use App\Models\ProvinceWeb;
use App\Models\RegencyWeb;
use App\Models\VillageWeb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class JalurController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $jalur = JalurWeb::with(['gunung', 'province', 'regency'])
            ->when($search, function ($query, $search) {
                return $query->where('nama', 'like', '%' . $search . '%');
            })
            ->get();

        return view('jalur.index', compact('jalur'));
    }

    public function create()
    {
        $pegunungan = GunungWeb::all();
        $province_id = ProvinceWeb::all();
        $regency_id = RegencyWeb::all();
        $district_id = DistrictWeb::all();
        $village_id = VillageWeb::all();

        return view('jalur.create', compact('province_id', 'regency_id', 'district_id', 'village_id', 'pegunungan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'province_id' => 'required|integer|exists:reg_provinces,id',
            'regency_id' => 'required|integer|exists:reg_regencies,id',
            'district_id' => 'required|integer|exists:reg_districts,id',
            'village_id' => 'required|integer|exists:reg_villages,id',
            'jarak' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string|max:1000',
            'map_basecamp' => 'nullable|string|max:255',
            'gambar_jalur' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'biaya' => 'required|numeric|min:0',
        ]);

        $gambarName = null;
        if ($request->hasFile('gambar_jalur')) {
            $file = $request->file('gambar_jalur');
            $gambarName = $file->getClientOriginalName();
            $file->storeAs('images', $gambarName, 'public');
        }

        JalurWeb::create([
            'nama' => $request->nama,
            'id_gunung' => $request->id_gunung,
            'province_id' => $request->province_id,
            'regency_id' => $request->regency_id,
            'district_id' => $request->district_id,
            'village_id' => $request->village_id,
            'jarak' => $request->jarak,
            'deskripsi' => $request->deskripsi,
            'map_basecamp' => $request->map_basecamp,
            'gambar_jalur' => $gambarName,
            'biaya' => $request->biaya,
        ]);

        return redirect()->route('jalur.index')->with('success', 'Jalur berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'province_id' => 'required|integer|exists:reg_provinces,id',
            'regency_id' => 'required|integer|exists:reg_regencies,id',
            'district_id' => 'required|integer|exists:reg_districts,id',
            'village_id' => 'required|integer|exists:reg_villages,id',
            'jarak' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string|max:1000',
            'map_basecamp' => 'nullable|string|max:255',
            'gambar_jalur' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'biaya' => 'required|numeric|min:0',
        ]);

        $jalur = JalurWeb::findOrFail($id);

        $gambarName = $jalur->gambar_jalur;
        if ($request->hasFile('gambar_jalur')) {
            if ($gambarName) {
                Storage::disk('public')->delete('images/' . $gambarName);
            }

            $file = $request->file('gambar_jalur');
            $gambarName = $file->getClientOriginalName();
            $file->storeAs('images', $gambarName, 'public');
        }

        $jalur->update([
            'nama' => $request->nama,
            'id_gunung' => $request->id_gunung,
            'province_id' => $request->province_id,
            'regency_id' => $request->regency_id,
            'district_id' => $request->district_id,
            'village_id' => $request->village_id,
            'jarak' => $request->jarak,
            'deskripsi' => $request->deskripsi,
            'map_basecamp' => $request->map_basecamp,
            'gambar_jalur' => $gambarName,
            'biaya' => $request->biaya,
        ]);

        return redirect()->route('jalur.index')->with('success', 'Jalur berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $jalur = JalurWeb::findOrFail($id);

        // Hapus data terkait di tabel pesanan
        $jalur->pesanan()->delete(); // Asumsikan ada relasi hasMany ke tabel pesanan

        // Hapus gambar jika ada
        if ($jalur->gambar_jalur) {
            Storage::disk('public')->delete('images/' . $jalur->gambar_jalur);
        }

        // Hapus jalur
        $jalur->delete();

        return redirect()->route('jalur.index')->with('success', 'Jalur berhasil dihapus!');
    }

    public function edit($id)
    {
        // Ambil data jalur berdasarkan ID
        $jalur = JalurWeb::findOrFail($id);
        
        // Ambil data untuk dropdown
        $pegunungan = GunungWeb::all();
        $provinces = ProvinceWeb::all();
        $regencies = RegencyWeb::where('province_id', $jalur->province_id)->get();
        $districts = DistrictWeb::where('regency_id', $jalur->regency_id)->get();
        $villages = VillageWeb::where('district_id', $jalur->district_id)->get();

        // Kembalikan view edit dengan data yang diperlukan
        return view('jalur.edit', compact('jalur', 'pegunungan', 'provinces', 'regencies', 'districts', 'villages'));
    }

}
