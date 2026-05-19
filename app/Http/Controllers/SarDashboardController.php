<?php

namespace App\Http\Controllers;

use App\Models\PanicRequest;
use App\Models\TrailWeb;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SarDashboardController extends Controller
{
    /**
     * Show the SAR Dashboard for trail guards (level 2)
     */
    public function index(Request $request)
    {
        // Check if user is level 2 (trail guard/SAR)
        if (auth()->user()->level !== 2) {
            abort(403, 'Unauthorized - Hanya untuk penjaga jalur');
        }

        $user = auth()->user();

        // Get the trail(s) managed by this guard
        $managedTrails = TrailWeb::where('user_id', $user->id)->pluck('id');

        $filter = $request->get('filter', 'all');

        // Get panic requests for trails managed by this guard
        $panicQuery = PanicRequest::with(['user', 'order.trail', 'order.mountain', 'responder'])
            ->whereHas('order', function ($query) use ($managedTrails) {
                $query->whereIn('id_jalur', $managedTrails);
            });

        // Apply status filter
        if ($filter === 'pending') {
            $panicQuery->where('status', 'pending');
        } elseif ($filter === 'responding') {
            $panicQuery->where('status', 'responding');
        } elseif ($filter === 'resolved') {
            $panicQuery->where('status', 'resolved');
        }

        $panicRequests = $panicQuery->orderBy('created_at', 'desc')->get();

        // Count by status
        $countPending = PanicRequest::whereHas('order', function ($q) use ($managedTrails) {
            $q->whereIn('id_jalur', $managedTrails);
        })->where('status', 'pending')->count();

        $countResponding = PanicRequest::whereHas('order', function ($q) use ($managedTrails) {
            $q->whereIn('id_jalur', $managedTrails);
        })->where('status', 'responding')->count();

        $countResolved = PanicRequest::whereHas('order', function ($q) use ($managedTrails) {
            $q->whereIn('id_jalur', $managedTrails);
        })->where('status', 'resolved')->count();

        return view('sar-dashboard.index', compact(
            'panicRequests',
            'countPending',
            'countResponding',
            'countResolved',
            'filter'
        ));
    }

    /**
     * Show detail of a panic request
     */
    public function show($id)
    {
        if (auth()->user()->level !== 2) {
            abort(403, 'Unauthorized - Hanya untuk penjaga jalur');
        }

        $panicRequest = PanicRequest::with(['user', 'order.trail', 'order.mountain', 'order.members', 'responder'])
            ->findOrFail($id);

        return view('sar-dashboard.show', compact('panicRequest'));
    }

    /**
     * Respond to a panic request (change status to responding)
     */
    public function respond($id)
    {
        if (auth()->user()->level !== 2) {
            abort(403, 'Unauthorized - Hanya untuk penjaga jalur');
        }

        $panicRequest = PanicRequest::findOrFail($id);

        if ($panicRequest->status !== 'pending') {
            return redirect()->back()->with('error', 'Permintaan ini sudah direspons');
        }

        $panicRequest->update([
            'status' => 'responding',
            'responded_by' => auth()->id(),
            'responded_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Anda telah merespons permintaan darurat ini');
    }

    /**
     * Resolve a panic request
     */
    public function resolve($id)
    {
        if (auth()->user()->level !== 2) {
            abort(403, 'Unauthorized - Hanya untuk penjaga jalur');
        }

        $panicRequest = PanicRequest::findOrFail($id);

        if ($panicRequest->status === 'resolved') {
            return redirect()->back()->with('error', 'Permintaan ini sudah diselesaikan');
        }

        $panicRequest->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'responded_by' => $panicRequest->responded_by ?? auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Permintaan darurat telah diselesaikan');
    }

    /**
     * Check for new pending panic requests (polling endpoint).
     * Returns only panics for the trails managed by the authenticated guard,
     * and only those with an id greater than the last_seen_id sent by the client.
     */
    public function checkNewPanics(Request $request): JsonResponse
    {
        if (auth()->user()->level !== 2) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $user = auth()->user();

        // Get the trail(s) managed by this guard only
        $managedTrails = TrailWeb::where('user_id', $user->id)->pluck('id');

        if ($managedTrails->isEmpty()) {
            return response()->json(['panics' => [], 'count' => 0]);
        }

        // The client sends the last panic id it has already seen/alerted.
        // We only return panics with a higher id, so we never re-alert.
        $lastSeenId = (int) $request->get('last_seen_id', 0);

        $newPanics = PanicRequest::with(['user', 'order.trail', 'order.mountain'])
            ->whereHas('order', function ($query) use ($managedTrails) {
                $query->whereIn('id_jalur', $managedTrails);
            })
            ->where('status', 'pending')
            ->where('id', '>', $lastSeenId)
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($panic) {
                return [
                    'id'             => $panic->id,
                    'emergency_type' => $panic->emergency_type,
                    'description'    => $panic->description,
                    'hiker_name'     => $panic->user->name ?? 'N/A',
                    'trail_name'     => optional(optional($panic->order)->trail)->nama_jalur ?? 'N/A',
                    'mountain_name'  => optional(optional($panic->order)->mountain)->nama_gunung ?? 'N/A',
                    'created_at'     => $panic->created_at->format('d/m/Y H:i:s'),
                    'detail_url'     => route('guards.sar.show', $panic->id),
                    'respond_url'    => route('guards.sar.respond', $panic->id),
                ];
            });

        return response()->json([
            'panics' => $newPanics,
            'count'  => $newPanics->count(),
        ]);
    }
}
