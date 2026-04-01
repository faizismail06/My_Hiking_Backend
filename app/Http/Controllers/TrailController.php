<?php

namespace App\Http\Controllers;

use App\Models\DistrictWeb;
use App\Models\MountainWeb;
use App\Models\TrailWeb;
use App\Models\ProvinceWeb;
use App\Models\RegencyWeb;
use App\Models\VillageWeb;
use App\Models\UserWeb;
use App\Services\GpxRouteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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

    public function store(Request $request, GpxRouteService $gpxRouteService)
    {
        $request->validate([
            'id_gunung' => 'required|integer|exists:mountains,id',
            'nama' => 'required|string|max:255',
            'province_id' => 'required|integer|exists:reg_provinces,id',
            'regency_id' => 'required|integer|exists:reg_regencies,id',
            'district_id' => 'required|integer|exists:reg_districts,id',
            'village_id' => 'required|integer|exists:reg_villages,id',
            'jarak' => 'required|numeric|min:0',
            'elevasi' => 'nullable|numeric|min:0',
            'durasi' => 'nullable|numeric|min:0',
            'tingkat_kesulitan' => 'nullable|in:mudah,sedang,sulit,sangat_sulit',
            'deskripsi' => 'nullable|string|max:1000',
            'map_basecamp' => 'nullable|string|max:255',
            'gambar_jalur' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gpx_file' => 'nullable|file|mimes:gpx,xml|max:10240',
            'route_source' => 'nullable|string|max:50',
            'route_points_json' => 'nullable|string',
            'trail_posts_json' => 'nullable|string',
            'biaya' => 'required|numeric|min:0',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
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

        $routePoints = null;
        $routeSource = $request->input('route_source', 'manual');
        $elevasiValue = $request->filled('elevasi')
            ? (float) $request->input('elevasi')
            : 0;
        $durasiValue = $request->filled('durasi')
            ? (float) $request->input('durasi')
            : 0;
        $tingkatKesulitanValue = $request->filled('tingkat_kesulitan')
            ? $request->input('tingkat_kesulitan')
            : 'sedang';
        if ($request->hasFile('gpx_file')) {
            try {
                $parsedRoute = $gpxRouteService->parseFromUploadedFile($request->file('gpx_file'), 1500);
                $routePoints = $parsedRoute['points'];
            } catch (\Throwable $e) {
                return redirect()->back()->withErrors(['gpx_file' => $e->getMessage()])->withInput();
            }
        }

        if ($request->filled('route_points_json') && !$request->hasFile('gpx_file')) {
            try {
                $routePoints = $this->parseRoutePointsJson($request->input('route_points_json'));
                $routeSource = 'manual';
            } catch (ValidationException $e) {
                return redirect()->back()->withErrors($e->errors())->withInput();
            }
        }

        $trail = TrailWeb::create([
            'nama' => $request->nama,
            'id_gunung' => $request->id_gunung,
            'user_id' => $guard->id,
            'province_id' => $request->province_id,
            'regency_id' => $request->regency_id,
            'district_id' => $request->district_id,
            'village_id' => $request->village_id,
            'jarak' => $request->jarak,
            'elevasi' => $elevasiValue,
            'durasi' => $durasiValue,
            'tingkat_kesulitan' => $tingkatKesulitanValue,
            'deskripsi' => $request->deskripsi,
            'map_basecamp' => $request->map_basecamp,
            'gambar_jalur' => $imageName,
            'biaya' => $request->biaya,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'route_points' => $routePoints,
            'route_source' => $routeSource,
        ]);

        if ($request->filled('trail_posts_json')) {
            try {
                $this->syncTrailPosts($trail, $request->input('trail_posts_json'));
            } catch (ValidationException $e) {
                return redirect()->back()->withErrors($e->errors())->withInput();
            }
        }

        return redirect()->route('trails.index')->with('success', 'Trail added successfully!');
    }

    public function update(Request $request, $id, GpxRouteService $gpxRouteService)
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
            'elevasi' => 'nullable|numeric|min:0',
            'durasi' => 'nullable|numeric|min:0',
            'tingkat_kesulitan' => 'nullable|in:mudah,sedang,sulit,sangat_sulit',
            'deskripsi' => 'nullable|string|max:1000',
            'map_basecamp' => 'nullable|string|max:255',
            'gambar_jalur' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gpx_file' => 'nullable|file|mimes:gpx,xml|max:10240',
            'route_source' => 'nullable|string|max:50',
            'route_points_json' => 'nullable|string',
            'trail_posts_json' => 'nullable|string',
            'biaya' => 'required|numeric|min:0',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
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

        $elevasiValue = $request->filled('elevasi')
            ? (float) $request->input('elevasi')
            : ($trail->elevasi ?? 0);
        $durasiValue = $request->filled('durasi')
            ? (float) $request->input('durasi')
            : ($trail->durasi ?? 0);
        $tingkatKesulitanValue = $request->filled('tingkat_kesulitan')
            ? $request->input('tingkat_kesulitan')
            : ($trail->tingkat_kesulitan ?: 'sedang');

        $updateData = [
            'nama' => $request->nama,
            'id_gunung' => $request->id_gunung,
            'province_id' => $request->province_id,
            'regency_id' => $request->regency_id,
            'district_id' => $request->district_id,
            'village_id' => $request->village_id,
            'jarak' => $request->jarak,
            'elevasi' => $elevasiValue,
            'durasi' => $durasiValue,
            'tingkat_kesulitan' => $tingkatKesulitanValue,
            'deskripsi' => $request->deskripsi,
            'map_basecamp' => $request->map_basecamp,
            'gambar_jalur' => $imageName,
            'biaya' => $request->biaya,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ];

        if ($request->hasFile('gpx_file')) {
            try {
                $parsedRoute = $gpxRouteService->parseFromUploadedFile($request->file('gpx_file'), 1500);
                $updateData['route_points'] = $parsedRoute['points'];
                $updateData['route_source'] = $request->input('route_source', 'manual');
            } catch (\Throwable $e) {
                return redirect()->back()->withErrors(['gpx_file' => $e->getMessage()])->withInput();
            }
        }

        if ($request->filled('route_points_json') && !$request->hasFile('gpx_file')) {
            try {
                $updateData['route_points'] = $this->parseRoutePointsJson($request->input('route_points_json'));
                $updateData['route_source'] = 'manual';
            } catch (ValidationException $e) {
                return redirect()->back()->withErrors($e->errors())->withInput();
            }
        }

        $trail->update($updateData);

        if ($request->has('trail_posts_json')) {
            try {
                $this->syncTrailPosts($trail, $request->input('trail_posts_json'));
            } catch (ValidationException $e) {
                return redirect()->back()->withErrors($e->errors())->withInput();
            }
        }

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
        $trail = TrailWeb::with(['trailGuard', 'posts'])->findOrFail($id);

        $mountains = MountainWeb::all();
        $provinces = ProvinceWeb::all();
        $regencies = RegencyWeb::where('province_id', $trail->province_id)->get();
        $districts = DistrictWeb::where('regency_id', $trail->regency_id)->get();
        $villages = VillageWeb::where('district_id', $trail->district_id)->get();

        return view('trails.edit', compact('trail', 'mountains', 'provinces', 'regencies', 'districts', 'villages'));
    }

    /**
     * @return array<int, array{lat: float, lng: float}>
     */
    private function parseRoutePointsJson(?string $payload): array
    {
        if ($payload === null || trim($payload) === '') {
            return [];
        }

        $decoded = json_decode($payload, true);
        if (!is_array($decoded)) {
            throw ValidationException::withMessages([
                'route_points_json' => 'Format titik jalur tidak valid.',
            ]);
        }

        $normalized = [];
        foreach ($decoded as $index => $point) {
            if (!is_array($point)) {
                continue;
            }

            $lat = $point['lat'] ?? $point['latitude'] ?? null;
            $lng = $point['lng'] ?? $point['lon'] ?? $point['longitude'] ?? null;

            if (!is_numeric($lat) || !is_numeric($lng)) {
                throw ValidationException::withMessages([
                    'route_points_json' => 'Titik jalur ke-' . ($index + 1) . ' tidak valid.',
                ]);
            }

            $latValue = (float) $lat;
            $lngValue = (float) $lng;
            if ($latValue < -90 || $latValue > 90 || $lngValue < -180 || $lngValue > 180) {
                throw ValidationException::withMessages([
                    'route_points_json' => 'Koordinat titik jalur di luar batas yang diizinkan.',
                ]);
            }

            $normalized[] = [
                'lat' => $latValue,
                'lng' => $lngValue,
            ];
        }

        if (!empty($normalized) && count($normalized) < 2) {
            throw ValidationException::withMessages([
                'route_points_json' => 'Minimal 2 titik dibutuhkan untuk membentuk jalur.',
            ]);
        }

        return $normalized;
    }

    private function syncTrailPosts(TrailWeb $trail, ?string $payload): void
    {
        if ($payload === null || trim($payload) === '') {
            return;
        }

        $decoded = json_decode($payload, true);
        if (!is_array($decoded)) {
            throw ValidationException::withMessages([
                'trail_posts_json' => 'Format data pos tidak valid.',
            ]);
        }

        $rows = [];
        foreach ($decoded as $index => $item) {
            if (!is_array($item)) {
                continue;
            }

            $lat = $item['lat'] ?? $item['latitude'] ?? null;
            $lng = $item['lng'] ?? $item['lon'] ?? $item['longitude'] ?? null;
            $name = trim((string) ($item['name'] ?? ''));

            if (!is_numeric($lat) || !is_numeric($lng)) {
                throw ValidationException::withMessages([
                    'trail_posts_json' => 'Koordinat pos ke-' . ($index + 1) . ' tidak valid.',
                ]);
            }

            $latValue = (float) $lat;
            $lngValue = (float) $lng;

            if ($latValue < -90 || $latValue > 90 || $lngValue < -180 || $lngValue > 180) {
                throw ValidationException::withMessages([
                    'trail_posts_json' => 'Koordinat pos di luar batas yang diizinkan.',
                ]);
            }

            $rows[] = [
                'name' => $name !== '' ? $name : 'Pos ' . ($index + 1),
                'sequence' => $index + 1,
                'latitude' => $latValue,
                'longitude' => $lngValue,
                'elevation' => isset($item['elevation']) && is_numeric($item['elevation'])
                    ? (float) $item['elevation']
                    : null,
                'description' => isset($item['description']) ? trim((string) $item['description']) : null,
                'icon_type' => isset($item['icon_type']) && trim((string) $item['icon_type']) !== ''
                    ? trim((string) $item['icon_type'])
                    : 'signpost',
            ];
        }

        $trail->posts()->delete();
        if (!empty($rows)) {
            $trail->posts()->createMany($rows);
        }
    }
}
