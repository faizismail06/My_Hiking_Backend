<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Services\DSSService;
use Illuminate\Http\Request;
use App\Models\Trail;
use App\Models\Mountain;

class MountainTrailDetailController extends Controller
{
    public function __construct(private DSSService $dssService)
    {
    }

    public function index(Request $request, $id_gunung, $id_jalur)
    {
        // Find trail based on trail ID and ensure relation with mountain
        $trail = Trail::with(['mountain', 'village', 'district', 'regency', 'province'])
            ->where('id', $id_jalur)
            ->where('id_gunung', $id_gunung)
            ->first();

        // Check if trail found
        if (!$trail) {
            return response()->json([
                'status' => false,
                'message' => 'Trail not found or not associated with the specified mountain',
            ], 404);
        }
        $imageUrl = url('api/images/' . $trail->gambar_jalur);

        // Create formatted result array
        $result = [
            'id' => $trail->id,
            'nama' => $trail->nama,
            'deskripsi' => $trail->deskripsi,
            'map_basecamp' => $trail->map_basecamp,
            'village' => $trail->village ? $trail->village->name : null,
            'district' => $trail->district ? $trail->district->name : null,
            'regency' => $trail->regency ? $trail->regency->name : null,
            'province' => $trail->province ? $trail->province->name : null,
            'jarak' => $trail->jarak,
            'elevasi' => $trail->elevasi,
            'durasi' => $trail->durasi,
            'tingkat_kesulitan' => $trail->tingkat_kesulitan,
            'gambar' => $imageUrl,
            'biaya' => $trail->biaya,
            'latitude' => $trail->latitude,
            'longitude' => $trail->longitude,
            'mountain' => [
                'id' => $trail->mountain->id,
                'nama' => $trail->mountain->nama,
                'ketinggian' => $trail->mountain->ketinggian,
                'province' => $trail->mountain->province ? $trail->mountain->province->name : null,
                'latitude' => $trail->mountain->latitude,
                'longitude' => $trail->mountain->longitude,
            ],
        ];

        $dssEvaluation = null;
        if ($request->user() && (int) $request->user()->level === 1 && !empty($request->user()->tier)) {
            $dssEvaluation = $this->dssService->evaluateRoute($request->user(), $trail);
        }

        // Return JSON response with trail data
        return response()->json([
            'status' => true,
            'message' => 'Trail details fetched successfully',
            'trail' => $result,
            'dss' => $dssEvaluation,
        ]);
    }

    public function trailBooking(Request $request, $id_gunung, $id_jalur)
    {
        // Find trail based on trail ID and ensure relation with mountain
        $trail = Trail::with(['mountain', 'village', 'district', 'regency', 'province'])
            ->where('id', $id_jalur)
            ->where('id_gunung', $id_gunung)
            ->first();

        // Check if trail found
        if (!$trail) {
            return response()->json([
                'status' => false,
                'message' => 'Trail not found or not associated with the specified mountain',
            ], 404);
        }
        $imageUrl = url('api/images/' . $trail->gambar_jalur);

        // Create formatted result array
        $result = [
            'id' => $trail->id,
            'nama' => $trail->nama,
            'village' => $trail->village ? $trail->village->name : null,
            'district' => $trail->district ? $trail->district->name : null,
            'regency' => $trail->regency ? $trail->regency->name : null,
            'province' => $trail->province ? $trail->province->name : null,
            'jarak' => $trail->jarak,
            'elevasi' => $trail->elevasi,
            'durasi' => $trail->durasi,
            'tingkat_kesulitan' => $trail->tingkat_kesulitan,
            'gambar' => $imageUrl,
            'biaya' => $trail->biaya,
            'mountain' => [
                'id' => $trail->mountain->id,
                'nama' => $trail->mountain->nama,
                'ketinggian' => $trail->mountain->ketinggian,
                'province' => $trail->mountain->province ? $trail->mountain->province->name : null,
            ],
        ];

        $dssEvaluation = null;
        if ($request->user() && (int) $request->user()->level === 1 && !empty($request->user()->tier)) {
            $dssEvaluation = $this->dssService->evaluateRoute($request->user(), $trail);
        }

        // Return JSON response with trail data
        return response()->json([
            'status' => true,
            'message' => 'Trail details fetched successfully',
            'trail' => $result,
            'dss' => $dssEvaluation,
        ]);
    }
}
