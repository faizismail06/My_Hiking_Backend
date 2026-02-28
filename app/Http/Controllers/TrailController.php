<?php

namespace App\Http\Controllers;

use App\Models\DistrictWeb;
use App\Models\MountainWeb;
use App\Models\TrailWeb;
use App\Models\ProvinceWeb;
use App\Models\RegencyWeb;
use App\Models\VillageWeb;
use App\Models\UserWeb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class TrailController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $trails = TrailWeb::with(['mountain', 'province', 'regency'])
            ->when($search, function ($query, $search) {
                return $query->where('nama', 'like', '%' . $search . '%');
            })
            ->get();

        return view('trails.index', compact('trails'));
    }

    public function create()
    {
        $mountains = MountainWeb::all();
        $province_id = ProvinceWeb::all();
        $regency_id = RegencyWeb::all();
        $district_id = DistrictWeb::all();
        $village_id = VillageWeb::all();

        return view('trails.create', compact('province_id', 'regency_id', 'district_id', 'village_id', 'mountains'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_gunung' => 'required|integer|exists:mountains,id',
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
            // Validation for trail guard data
            'penjaga_name' => 'required|string|max:255',
            'penjaga_email' => 'required|email|unique:users,email',
            'penjaga_phone' => 'required|numeric|unique:users,phone',
            'penjaga_address' => 'nullable|string|max:255',
        ]);

        $imageName = null;
        if ($request->hasFile('gambar_jalur')) {
            $file = $request->file('gambar_jalur');
            $imageName = $file->getClientOriginalName();
            $file->storeAs('images', $imageName, 'public');
        }

        // Create user account for trail guard (level 2)
        $guard = UserWeb::create([
            'name' => $request->penjaga_name,
            'email' => $request->penjaga_email,
            'phone' => $request->penjaga_phone,
            'address' => $request->penjaga_address,
            'password' => Hash::make('password123'),
            'level' => 2,
        ]);

        TrailWeb::create([
            'nama' => $request->nama,
            'id_gunung' => $request->id_gunung,
            'user_id' => $guard->id,
            'province_id' => $request->province_id,
            'regency_id' => $request->regency_id,
            'district_id' => $request->district_id,
            'village_id' => $request->village_id,
            'jarak' => $request->jarak,
            'deskripsi' => $request->deskripsi,
            'map_basecamp' => $request->map_basecamp,
            'gambar_jalur' => $imageName,
            'biaya' => $request->biaya,
        ]);

        return redirect()->route('trails.index')->with('success', 'Trail added successfully!');
    }

    public function update(Request $request, $id)
    {
        $trail = TrailWeb::findOrFail($id);

        $request->validate([
            'id_gunung' => 'required|integer|exists:mountains,id',
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
            // Validation for trail guard data (optional when editing)
            'penjaga_name' => 'nullable|string|max:255',
            'penjaga_email' => 'nullable|email|unique:users,email,' . $trail->user_id,
            'penjaga_phone' => 'nullable|numeric|unique:users,phone,' . $trail->user_id,
            'penjaga_address' => 'nullable|string|max:255',
        ]);

        $imageName = $trail->gambar_jalur;
        if ($request->hasFile('gambar_jalur')) {
            if ($imageName) {
                Storage::disk('public')->delete('images/' . $imageName);
            }

            $file = $request->file('gambar_jalur');
            $imageName = $file->getClientOriginalName();
            $file->storeAs('images', $imageName, 'public');
        }

        $trail->update([
            'nama' => $request->nama,
            'id_gunung' => $request->id_gunung,
            'province_id' => $request->province_id,
            'regency_id' => $request->regency_id,
            'district_id' => $request->district_id,
            'village_id' => $request->village_id,
            'jarak' => $request->jarak,
            'deskripsi' => $request->deskripsi,
            'map_basecamp' => $request->map_basecamp,
            'gambar_jalur' => $imageName,
            'biaya' => $request->biaya,
        ]);

        // Update guard data if exists
        if ($trail->trailGuard && $request->filled('penjaga_name')) {
            $trail->trailGuard->update([
                'name' => $request->penjaga_name,
                'email' => $request->penjaga_email,
                'phone' => $request->penjaga_phone,
                'address' => $request->penjaga_address,
            ]);
        }

        return redirect()->route('trails.index')->with('success', 'Trail updated successfully!');
    }

    public function destroy($id)
    {
        $trail = TrailWeb::findOrFail($id);

        // Delete related data in orders table
        $trail->orders()->delete();

        // Delete image if exists
        if ($trail->gambar_jalur) {
            Storage::disk('public')->delete('images/' . $trail->gambar_jalur);
        }

        $trail->delete();

        return redirect()->route('trails.index')->with('success', 'Trail deleted successfully!');
    }

    public function edit($id)
    {
        $trail = TrailWeb::with('trailGuard')->findOrFail($id);

        $mountains = MountainWeb::all();
        $provinces = ProvinceWeb::all();
        $regencies = RegencyWeb::where('province_id', $trail->province_id)->get();
        $districts = DistrictWeb::where('regency_id', $trail->regency_id)->get();
        $villages = VillageWeb::where('district_id', $trail->district_id)->get();

        return view('trails.edit', compact('trail', 'mountains', 'provinces', 'regencies', 'districts', 'villages'));
    }
}
