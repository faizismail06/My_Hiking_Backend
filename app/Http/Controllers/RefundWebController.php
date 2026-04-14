<?php

namespace App\Http\Controllers;

use App\Models\RefundRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RefundWebController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $status = trim((string) $request->query('status', ''));

        $refundRequests = RefundRequest::with([
            'order:id,id_user,id_gunung,id_jalur,tanggal_naik,total_harga_tiket,status',
            'order.mountain:id,nama',
            'order.trail:id,nama',
            'user:id,name,email',
        ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('id', 'like', "%{$search}%")
                        ->orWhere('order_id', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status !== '', function ($query) use ($status) {
                $query->where('refund_status', $status);
            })
            ->orderByDesc('requested_at')
            ->paginate(15);

        $refundRequests->appends($request->query());

        $summary = [
            'pending' => RefundRequest::where('refund_status', 'pending')->count(),
            'approved' => RefundRequest::where('refund_status', 'approved')->count(),
            'rejected' => RefundRequest::where('refund_status', 'rejected')->count(),
            'refunded' => RefundRequest::where('refund_status', 'refunded')->count(),
        ];

        return view('admin.refunds.index', compact('refundRequests', 'summary'));
    }

    public function show(int $id)
    {
        $refundRequest = RefundRequest::with([
            'order:id,id_user,id_gunung,id_jalur,tanggal_naik,tanggal_turun,total_harga_tiket,status',
            'order.mountain:id,nama',
            'order.trail:id,nama',
            'user:id,name,email',
        ])->findOrFail($id);

        $penalty = (float) $refundRequest->penalty_amount;
        $refund = (float) $refundRequest->refund_amount;

        $distribution = [
            'admin_penalty_share' => round($penalty * 0.10, 2),
            'ranger_penalty_share' => round($penalty * 0.90, 2),
            'admin_refund_share' => round($refund * 0.10, 2),
            'ranger_refund_share' => round($refund * 0.90, 2),
        ];

        return view('admin.refunds.show', compact('refundRequest', 'distribution'));
    }

    public function approve(int $id)
    {
        $refundRequest = RefundRequest::findOrFail($id);

        if ($refundRequest->refund_status !== 'pending') {
            return redirect()->back()->with('error', 'Hanya request pending yang dapat di-approve.');
        }

        $refundRequest->update([
            'refund_status' => 'approved',
        ]);

        return redirect()->back()->with('success', 'Refund request berhasil di-approve.');
    }

    public function reject(int $id)
    {
        $refundRequest = RefundRequest::with('order')->findOrFail($id);

        if (!in_array($refundRequest->refund_status, ['pending', 'approved'], true)) {
            return redirect()->back()->with('error', 'Request ini tidak dapat ditolak pada status saat ini.');
        }

        DB::transaction(function () use ($refundRequest) {
            $refundRequest->update([
                'refund_status' => 'rejected',
                'processed_at' => now('Asia/Jakarta'),
            ]);

            if ($refundRequest->order) {
                $refundRequest->order->update(['status' => 'Booking']);
            }
        });

        return redirect()->back()->with('success', 'Refund request berhasil ditolak.');
    }

    public function markRefunded(Request $request, int $id)
    {
        $request->validate([
            'proof_of_transfer' => 'required|image|mimes:jpg,jpeg,png|max:4096',
        ]);

        $refundRequest = RefundRequest::with('order')->findOrFail($id);

        if (!in_array($refundRequest->refund_status, ['pending', 'approved'], true)) {
            return redirect()->back()->with('error', 'Request ini tidak dapat ditandai refunded.');
        }

        $proofPath = $request->file('proof_of_transfer')->store('refund_proofs', 'public');

        DB::transaction(function () use ($refundRequest, $proofPath) {
            $refundRequest->update([
                'refund_status' => 'refunded',
                'proof_of_transfer' => $proofPath,
                'processed_at' => now('Asia/Jakarta'),
            ]);

            if ($refundRequest->order) {
                $refundRequest->order->update(['status' => 'Cancelled']);
            }
        });

        return redirect()->back()->with('success', 'Refund request berhasil ditandai sebagai refunded.');
    }
}
