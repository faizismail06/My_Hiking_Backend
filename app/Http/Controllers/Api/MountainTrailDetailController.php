<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\DSSService;
use Illuminate\Http\Request;
use App\Models\Trail;
use App\Models\Mountain;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

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
            'jarak' => (float) $trail->jarak,
            'elevasi' => $trail->elevasi,
            'durasi' => $trail->durasi,
            'tingkat_kesulitan' => $trail->tingkat_kesulitan,
            'gambar' => $imageUrl,
            'biaya' => $trail->biaya,
            'daily_hiker_limit' => $trail->daily_hiker_limit !== null ? (int) $trail->daily_hiker_limit : null,
            'latitude' => $trail->latitude,
            'longitude' => $trail->longitude,
            'route_preview' => $this->buildRoutePreview($trail),
            'posts' => $this->serializePosts($trail),
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
        $request->validate([
            'tanggal_naik' => 'nullable|date',
            'tanggal_turun' => 'nullable|date|after_or_equal:tanggal_naik',
        ]);

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
            'jarak' => (float) $trail->jarak,
            'elevasi' => $trail->elevasi,
            'durasi' => $trail->durasi,
            'tingkat_kesulitan' => $trail->tingkat_kesulitan,
            'gambar' => $imageUrl,
            'biaya' => $trail->biaya,
            'daily_hiker_limit' => $trail->daily_hiker_limit !== null ? (int) $trail->daily_hiker_limit : null,
            'route_preview' => $this->buildRoutePreview($trail),
            'posts' => $this->serializePosts($trail),
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

        $slotAvailability = $this->buildSlotAvailability(
            $trail,
            $request->input('tanggal_naik'),
            $request->input('tanggal_turun')
        );

        // Return JSON response with trail data
        return response()->json([
            'status' => true,
            'message' => 'Trail details fetched successfully',
            'trail' => $result,
            'slot_availability' => $slotAvailability,
            'dss' => $dssEvaluation,
        ]);
    }

    private function buildSlotAvailability(Trail $trail, ?string $tanggalNaik, ?string $tanggalTurun): array
    {
        $limit = $trail->daily_hiker_limit !== null ? (int) $trail->daily_hiker_limit : null;

        if ($limit === null || $limit <= 0) {
            return [
                'is_limited' => false,
                'daily_hiker_limit' => null,
                'range' => null,
                'days' => [],
            ];
        }

        if (!$tanggalNaik || !$tanggalTurun) {
            return [
                'is_limited' => true,
                'daily_hiker_limit' => $limit,
                'range' => null,
                'days' => [],
                'note' => 'Kirim tanggal_naik dan tanggal_turun untuk melihat sisa slot per tanggal.',
            ];
        }

        $startDate = Carbon::parse($tanggalNaik)->startOfDay();
        $endDate = Carbon::parse($tanggalTurun)->startOfDay();

        $existingOrders = Order::where('id_jalur', $trail->id)
            ->whereNotIn('status', ['Cancelled', 'Expired'])
            ->whereDate('tanggal_naik', '<=', $endDate->toDateString())
            ->whereDate('tanggal_turun', '>=', $startDate->toDateString())
            ->withCount('members')
            ->get(['id', 'tanggal_naik', 'tanggal_turun']);

        $dailyOccupancy = [];
        foreach ($existingOrders as $order) {
            $orderStart = Carbon::parse($order->tanggal_naik)->startOfDay();
            $orderEnd = Carbon::parse($order->tanggal_turun)->startOfDay();

            if ($orderEnd->lt($startDate) || $orderStart->gt($endDate)) {
                continue;
            }

            $loopDate = $orderStart->copy()->greaterThan($startDate) ? $orderStart->copy() : $startDate->copy();
            $loopEnd = $orderEnd->copy()->lessThan($endDate) ? $orderEnd->copy() : $endDate->copy();
            $orderParticipants = ((int) $order->members_count) + 1;

            while ($loopDate->lte($loopEnd)) {
                $key = $loopDate->toDateString();
                $dailyOccupancy[$key] = ($dailyOccupancy[$key] ?? 0) + $orderParticipants;
                $loopDate->addDay();
            }
        }

        $days = [];
        $cursor = $startDate->copy();
        while ($cursor->lte($endDate)) {
            $dateKey = $cursor->toDateString();
            $currentHikers = (int) ($dailyOccupancy[$dateKey] ?? 0);
            $remainingSlots = max(0, $limit - $currentHikers);

            $days[] = [
                'date' => $dateKey,
                'current_hikers' => $currentHikers,
                'remaining_slots' => $remainingSlots,
                'is_full' => $remainingSlots <= 0,
            ];

            $cursor->addDay();
        }

        return [
            'is_limited' => true,
            'daily_hiker_limit' => $limit,
            'range' => [
                'tanggal_naik' => $startDate->toDateString(),
                'tanggal_turun' => $endDate->toDateString(),
            ],
            'days' => $days,
        ];
    }

    public function preview($id_gunung, $id_jalur)
    {
        $trail = Trail::where('id', $id_jalur)
            ->where('id_gunung', $id_gunung)
            ->first();

        if (!$trail) {
            return response()->json([
                'status' => false,
                'message' => 'Trail not found or not associated with the specified mountain',
            ], 404);
        }

        $preview = $this->buildRoutePreview($trail);

        if ($preview === null) {
            return response()->json([
                'status' => true,
                'message' => 'Route preview is not available yet for this trail',
                'trail_id' => (int) $trail->id,
                'route_preview' => null,
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Route preview fetched successfully',
            'trail_id' => (int) $trail->id,
            'route_preview' => $preview,
            'posts' => $this->serializePosts($trail),
        ]);
    }

    private function serializePosts(Trail $trail): array
    {
        return $trail->posts()
            ->get()
            ->map(function ($post) {
                return [
                    'id' => (int) $post->id,
                    'name' => (string) $post->name,
                    'sequence' => (int) $post->sequence,
                    'lat' => (float) $post->latitude,
                    'lng' => (float) $post->longitude,
                    'elevation' => $post->elevation !== null ? (float) $post->elevation : null,
                    'icon_type' => (string) ($post->icon_type ?: 'signpost'),
                    'description' => $post->description,
                ];
            })
            ->values()
            ->all();
    }

    private function buildRoutePreview(Trail $trail): ?array
    {
        $rawPoints = $trail->route_points;
        if (!is_array($rawPoints) || empty($rawPoints)) {
            return null;
        }

        $points = collect($rawPoints)
            ->map(function ($point) {
                if (is_array($point)) {
                    // Supports both lat/lng and latitude/longitude key variants.
                    $latValue = $point['lat'] ?? $point['latitude'] ?? null;
                    $lngValue = $point['lng'] ?? $point['lon'] ?? $point['longitude'] ?? null;

                    if ($latValue === null || $lngValue === null) {
                        return null;
                    }

                    $lat = (float) $latValue;
                    $lng = (float) $lngValue;

                    if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
                        return null;
                    }

                    return [
                        'lat' => $lat,
                        'lng' => $lng,
                        'ele' => isset($point['ele']) ? (float) $point['ele'] : null,
                        'time' => isset($point['time']) ? (string) $point['time'] : null,
                    ];
                }

                return null;
            })
            ->filter()
            ->values();

        if ($points->isEmpty()) {
            return null;
        }

        $totalPoints = $points->count();
        $maxRenderedPoints = 1500;

        $displayPoints = $points;
        if ($totalPoints > $maxRenderedPoints) {
            $stride = (int) ceil($totalPoints / $maxRenderedPoints);
            $displayPoints = $points
                ->filter(function ($_, int $index) use ($stride) {
                    return $index % $stride === 0;
                })
                ->values();

            if ($displayPoints->last() !== $points->last()) {
                $displayPoints->push($points->last());
            }
        }

        $bbox = $this->calculateBoundingBox($points);

        return [
            'source' => $trail->route_source ?: 'manual',
            'total_points' => $totalPoints,
            'display_points' => $displayPoints->count(),
            'start' => $points->first(),
            'end' => $points->last(),
            'bbox' => $bbox,
            'points' => $displayPoints->all(),
            'updated_at' => optional($trail->updated_at)->toIso8601String(),
        ];
    }

    private function calculateBoundingBox(Collection $points): array
    {
        $lats = $points->pluck('lat');
        $lngs = $points->pluck('lng');

        return [
            'min_lat' => (float) $lats->min(),
            'max_lat' => (float) $lats->max(),
            'min_lng' => (float) $lngs->min(),
            'max_lng' => (float) $lngs->max(),
        ];
    }
}
