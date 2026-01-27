<?php

namespace App\Http\Controllers;

use App\Models\DistrictWeb;
use App\Models\GunungWeb;
use App\Models\JalurWeb;
use App\Models\ProvinceWeb;
use App\Models\RegencyWeb;
use App\Models\VillageWeb;
use App\Models\UserWeb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

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
            'id_gunung' => 'required|integer|exists:gunung,id',
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
            // Validasi untuk data penjaga jalur
            'penjaga_name' => 'required|string|max:255',
            'penjaga_email' => 'required|email|unique:users,email',
            'penjaga_phone' => 'required|numeric|unique:users,phone',
            'penjaga_address' => 'nullable|string|max:255',
        ]);

        $gambarName = null;
        if ($request->hasFile('gambar_jalur')) {
            $file = $request->file('gambar_jalur');
            $gambarName = $file->getClientOriginalName();
            $file->storeAs('images', $gambarName, 'public');
        }

        // Buat akun user untuk penjaga jalur (level 2)
        $penjaga = UserWeb::create([
            'name' => $request->penjaga_name,
            'email' => $request->penjaga_email,
            'phone' => $request->penjaga_phone,
            'address' => $request->penjaga_address,
            'password' => Hash::make('password123'), // Password default
            'level' => 2, // Level 2 untuk penjaga jalur
        ]);

        JalurWeb::create([
            'nama' => $request->nama,
            'id_gunung' => $request->id_gunung,
            'user_id' => $penjaga->id, // Simpan ID penjaga
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
        $jalur = JalurWeb::findOrFail($id);

        $request->validate([
            'id_gunung' => 'required|integer|exists:gunung,id',
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
            // Validasi untuk data penjaga jalur (opsional saat edit)
            'penjaga_name' => 'nullable|string|max:255',
            'penjaga_email' => 'nullable|email|unique:users,email,' . $jalur->user_id,
            'penjaga_phone' => 'nullable|numeric|unique:users,phone,' . $jalur->user_id,
            'penjaga_address' => 'nullable|string|max:255',
        ]);

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

        // Update data penjaga jika ada
        if ($jalur->penjaga && $request->filled('penjaga_name')) {
            $jalur->penjaga->update([
                'name' => $request->penjaga_name,
                'email' => $request->penjaga_email,
                'phone' => $request->penjaga_phone,
                'address' => $request->penjaga_address,
            ]);
        }

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
        // Ambil data jalur berdasarkan ID dengan relasi penjaga
        $jalur = JalurWeb::with('penjaga')->findOrFail($id);

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
