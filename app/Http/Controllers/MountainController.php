<?php

namespace App\Http\Controllers;

use App\Models\MountainWeb;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\ProvinceWeb;
use App\Models\RegencyWeb;
use App\Models\DistrictWeb;
use App\Models\VillageWeb;

class MountainController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()->level !== 3) {
            abort(403, 'Unauthorized');
        }
        $search = $request->input('search');

        $mountains = MountainWeb::with(['province', 'regency', 'district', 'village'])
            ->when($search, function ($query, $search) {
                return $query->where('nama', 'like', '%' . $search . '%')
                    ->orWhere('ketinggian', 'like', '%' . $search . '%');
            })
            ->get();

        return view('mountains.index', compact('mountains'));
    }

    public function create()
    {
        $province_id = ProvinceWeb::query()->select(['id', 'name'])->orderBy('name')->get();
        $regency_id = collect();
        $district_id = collect();
        $village_id = collect();

        return view('mountains.create', compact('province_id', 'regency_id', 'district_id', 'village_id'));
    }

    public function store(Request $request)
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
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $imageName = null;
        if ($request->hasFile('gambar_gunung')) {
            $file = $request->file('gambar_gunung');
            $imageName = $file->getClientOriginalName();
            $file->storeAs('images', $imageName, 'public');
        }

        MountainWeb::create([
            'nama' => $request->nama,
            'province_id' => $request->province_id,
            'regency_id' => $request->regency_id,
            'district_id' => $request->district_id,
            'village_id' => $request->village_id,
            'ketinggian' => $request->ketinggian,
            'deskripsi' => $request->deskripsi,
            'gambar_gunung' => $imageName,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return redirect()->route('mountains.index')->with('success', 'Mountain added successfully!');
    }

    public function edit($id)
    {
        $mountain = MountainWeb::findOrFail($id);

        $province_id = ProvinceWeb::query()->select(['id', 'name'])->orderBy('name')->get();
        
        $regency_id = $mountain->province_id
            ? RegencyWeb::query()->select(['id', 'name', 'province_id'])->where('province_id', $mountain->province_id)->orderBy('name')->get()
            : collect();
            
        $district_id = $mountain->regency_id
            ? DistrictWeb::query()->select(['id', 'name', 'regency_id'])->where('regency_id', $mountain->regency_id)->orderBy('name')->get()
            : collect();
            
        $village_id = $mountain->district_id
            ? VillageWeb::query()->select(['id', 'name', 'district_id'])->where('district_id', $mountain->district_id)->orderBy('name')->get()
            : collect();

        return view('mountains.edit', compact('mountain', 'province_id', 'regency_id', 'district_id', 'village_id'));
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
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $mountain = MountainWeb::findOrFail($id);

        $imageName = $mountain->gambar_gunung;
        if ($request->hasFile('gambar_gunung')) {
            if ($imageName) {
                Storage::disk('public')->delete('images/' . $imageName);
            }

            $file = $request->file('gambar_gunung');
            $imageName = $file->getClientOriginalName();
            $file->storeAs('images', $imageName, 'public');
        }

        $mountain->update([
            'nama' => $request->nama,
            'province_id' => $request->province_id,
            'regency_id' => $request->regency_id,
            'district_id' => $request->district_id,
            'village_id' => $request->village_id,
            'ketinggian' => $request->ketinggian,
            'deskripsi' => $request->deskripsi,
            'gambar_gunung' => $imageName,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return redirect()->route('mountains.index')->with('success', 'Mountain updated successfully!');
    }

    public function destroy($id)
    {
        $mountain = MountainWeb::findOrFail($id);

        if ($mountain->gambar_gunung) {
            Storage::disk('public')->delete('images/' . $mountain->gambar_gunung);
        }

        $mountain->delete();

        return redirect()->route('mountains.index')->with('success', 'Mountain deleted successfully!');
    }
    
    public function show($id)
    {
        $mountain = MountainWeb::with(['province', 'regency', 'district', 'village'])
            ->findOrFail($id);

        return view('mountains.show', compact('mountain'));
    }
}
