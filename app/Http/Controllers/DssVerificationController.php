<?php

namespace App\Http\Controllers;

use App\Models\DssPendingSubmission;
use App\Notifications\DssApprovedNotification;
use App\Notifications\DssRejectedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DssVerificationController extends Controller
{
    public function index(Request $request)
    {
        $submissions = DssPendingSubmission::with(['route', 'submitter'])
            ->where('status', 'pending')
            ->orderByDesc('updated_at')
            ->paginate(15);

        return view('admin.dss-verifications.index', [
            'submissions' => $submissions,
            'pendingCount' => DssPendingSubmission::where('status', 'pending')->count(),
        ]);
    }

    public function approve(int $id): RedirectResponse
    {
        $submission = DssPendingSubmission::with(['route', 'submitter'])
            ->where('status', 'pending')
            ->findOrFail($id);

        DB::transaction(function () use ($submission) {
            $route = $submission->route;
            if (!$route) {
                return;
            }

            $route->fill([
                'panorama_score' => (float) $submission->panorama_score_pending,
                'fasilitas_score' => (float) $submission->fasilitas_score_pending,
                'safety_score' => (float) $submission->safety_score_pending,
                'crowd_level' => (float) $submission->crowd_level_pending,
                'popularity_score' => (float) ($submission->popularity_score_pending ?? $route->popularity_score ?? 0),
                'dss_status' => 'approved',
            ]);
            $route->applyDssDefaults();
            $route->save();

            if ($submission->submitter) {
                $submission->submitter->notify(new DssApprovedNotification((string) $route->nama));
            }

            // Pending record is no longer needed after decision.
            $submission->delete();

            // Keep data quality warning hook on active DSS columns.
            \App\Models\TrailWeb::checkDssConsistency();
        });

        return redirect()->route('admin.dss-verifications.index')
            ->with('success', 'Pengajuan DSS disetujui dan data aktif jalur telah diperbarui.');
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ], [
            'reason.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $submission = DssPendingSubmission::with(['route', 'submitter'])
            ->where('status', 'pending')
            ->findOrFail($id);

        DB::transaction(function () use ($submission, $validated) {
            $route = $submission->route;
            $trailName = (string) ($route->nama ?? '-');

            if ($route) {
                // Keep currently active DSS values and reopen route to recommendation.
                $route->dss_status = 'approved';
                $route->save();
            }

            if ($submission->submitter) {
                $submission->submitter->notify(
                    new DssRejectedNotification($trailName, (string) $validated['reason'])
                );
            }

            // Requirement: rejected pending row is removed.
            $submission->delete();
        });

        return redirect()->route('admin.dss-verifications.index')
            ->with('success', 'Pengajuan DSS ditolak dan notifikasi sudah dikirim ke penjaga jalur.');
    }
}
