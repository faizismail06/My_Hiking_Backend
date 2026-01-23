<?php

namespace App\Http\Controllers;

use App\Models\GunungWeb;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\ProvinceWeb;
use App\Models\RegencyWeb;
use App\Models\DistrictWeb;
use App\Models\VillageWeb;

class GunungController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()->level !== 3) {
            abort(403, 'Unauthorized');
        }
        $search = $request->input('search');

        $gunungs = GunungWeb::with(['province', 'regency', 'district', 'village'])
            ->when($search, function ($query, $search) {
                return $query->where('nama', 'like', '%' . $search . '%')
                    ->orWhere('ketinggian', 'like', '%' . $search . '%');
            })
            ->get();

        return view('gunung.index', compact('gunungs'));
    }

    public function create()
    {
        $province_id = ProvinceWeb::all();
        $regency_id = RegencyWeb::all();
        $district_id = DistrictWeb::all();
        $village_id = VillageWeb::all();

        return view('gunung.create', compact('province_id', 'regency_id', 'district_id', 'village_id'));
    }

    public function store(Request $request)
    {
        // Validasi input form
        $request->validate([
            'nama' => 'required|string|max:255',
            'province_id' => 'required|integer|exists:reg_provinces,id',
            'regency_id' => 'required|integer|exists:reg_regencies,id',
            'district_id' => 'required|integer|exists:reg_districts,id',
            'village_id' => 'required|integer|exists:reg_villages,id',
            'ketinggian' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string|max:1000',
            'gambar_gunung' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Upload gambar jika ada
        $gambarName = null;
        if ($request->hasFile('gambar_gunung')) {
            $file = $request->file('gambar_gunung');
            $gambarName = $file->getClientOriginalName(); // Ambil nama file asli
            $file->storeAs('images', $gambarName, 'public'); // Simpan di direktori 'images'
        }

        // Simpan data ke database
        GunungWeb::create([
            'nama' => $request->nama,
            'province_id' => $request->province_id,
            'regency_id' => $request->regency_id,
            'district_id' => $request->district_id,
            'village_id' => $request->village_id,
            'ketinggian' => $request->ketinggian,
            'deskripsi' => $request->deskripsi,
            'gambar_gunung' => $gambarName,
        ]);

        return redirect()->route('gunung.index')->with('success', 'Gunung berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $gunung = GunungWeb::findOrFail($id);

        $province_id = ProvinceWeb::all();
        $regency_id = RegencyWeb::all();
        $district_id = DistrictWeb::all();
        $village_id = VillageWeb::all();

        return view('gunung.edit', compact('gunung', 'province_id', 'regency_id', 'district_id', 'village_id'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'province_id' => 'required|integer|exists:reg_provinces,id',
            'regency_id' => 'required|integer|exists:reg_regencies,id',
            'district_id' => 'required|integer|exists:reg_districts,id',
            'village_id' => 'required|integer|exists:reg_villages,id',
            'ketinggian' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string|max:1000',
            'gambar_gunung' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $gunung = GunungWeb::findOrFail($id);

        $gambarName = $gunung->gambar_gunung; // Pertahankan gambar lama
        if ($request->hasFile('gambar_gunung')) {
            if ($gambarName) {
                Storage::disk('public')->delete('images/' . $gambarName); // Hapus gambar lama
            }

            $file = $request->file('gambar_gunung');
            $gambarName = $file->getClientOriginalName(); // Ambil nama file asli
            $file->storeAs('images', $gambarName, 'public'); // Simpan di direktori 'images'
        }

        $gunung->update([
            'nama' => $request->nama,
            'province_id' => $request->province_id,
            'regency_id' => $request->regency_id,
            'district_id' => $request->district_id,
            'village_id' => $request->village_id,
            'ketinggian' => $request->ketinggian,
            'deskripsi' => $request->deskripsi,
            'gambar_gunung' => $gambarName,
        ]);

        return redirect()->route('gunung.index')->with('success', 'Gunung berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $gunung = GunungWeb::findOrFail($id);

        if ($gunung->gambar_gunung) {
            Storage::disk('public')->delete('images/' . $gunung->gambar_gunung);
        }

        $gunung->delete();

        return redirect()->route('gunung.index')->with('success', 'Gunung berhasil dihapus!');
    }
    public function show($id)
    {
        $gunung = GunungWeb::with(['province', 'regency', 'district', 'village'])
            ->findOrFail($id);

        return view('gunung.show', compact('gunung'));
    }
}
