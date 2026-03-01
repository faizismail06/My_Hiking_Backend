<?php

namespace App\Http\Controllers;

use App\Models\PanicRequest;
use App\Models\TrailWeb;
use Illuminate\Http\Request;

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
}
