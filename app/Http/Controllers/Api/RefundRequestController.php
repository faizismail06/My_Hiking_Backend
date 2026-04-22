<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\RefundRequest;
use App\Services\RefundCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RefundRequestController extends Controller
{
    public function __construct(private readonly RefundCalculationService $refundCalculationService)
    {
    }

    public function preview(Request $request, int $orderId)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $order = Order::with(['transaction', 'trail:id,is_refund_allowed'])->findOrFail($orderId);

        if ((string) $order->id_user !== (string) $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke pesanan ini.',
            ], 403);
        }

        if ($order->status === 'Cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan sudah dibatalkan.',
            ], 422);
        }

        if ($order->status === 'Cancel Requested') {
            return response()->json([
                'success' => false,
                'message' => 'Permintaan pembatalan untuk pesanan ini sudah diajukan.',
            ], 422);
        }

        if ($order->transaction?->status_pesanan !== 'Complete') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya pesanan yang sudah lunas yang dapat diajukan refund.',
            ], 422);
        }

        if ($order->trail && !$order->trail->is_refund_allowed) {
            return response()->json([
                'success' => false,
                'message' => 'Refund tiket untuk basecamp ini saat ini tidak diizinkan oleh penjaga gunung.',
            ], 422);
        }

        $calculation = $this->refundCalculationService->calculate($order);

        return response()->json([
            'success' => true,
            'message' => 'Preview refund berhasil diambil.',
            'data' => array_merge($calculation, [
                'order_id' => $order->id,
                'order_status' => $order->status,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'cancel_reason' => 'required|string|max:500',
            'refund_method' => 'required|in:Bank Transfer,DANA,GoPay',
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:100',
            'account_holder' => 'nullable|string|max:100',
            'phone_number' => 'nullable|string|max:30',
        ]);

        $order = Order::with(['transaction', 'trail:id,is_refund_allowed'])->findOrFail($validated['order_id']);

        if ((string) $order->id_user !== (string) $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke pesanan ini.',
            ], 403);
        }

        if ($order->status === 'Cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan sudah dibatalkan.',
            ], 422);
        }

        if ($order->status === 'Cancel Requested') {
            return response()->json([
                'success' => false,
                'message' => 'Permintaan pembatalan untuk pesanan ini sudah diajukan.',
            ], 422);
        }

        if ($order->transaction?->status_pesanan !== 'Complete') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya pesanan yang sudah lunas yang dapat diajukan refund.',
            ], 422);
        }

        if ($order->trail && !$order->trail->is_refund_allowed) {
            return response()->json([
                'success' => false,
                'message' => 'Refund tiket untuk basecamp ini saat ini tidak diizinkan oleh penjaga gunung.',
            ], 422);
        }

        $calculation = $this->refundCalculationService->calculate($order);

        $refundMethod = $validated['refund_method'];
        $bankName = null;
        $accountNumber = null;
        $accountHolder = null;

        if ($refundMethod === 'Bank Transfer') {
            if (empty($validated['bank_name']) || empty($validated['account_number']) || empty($validated['account_holder'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bank name, account number, dan account holder wajib diisi untuk metode Bank Transfer.',
                ], 422);
            }

            $bankName = $validated['bank_name'];
            $accountNumber = $validated['account_number'];
            $accountHolder = $validated['account_holder'];
        } else {
            if (empty($validated['phone_number'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nomor telepon wajib diisi untuk metode e-wallet.',
                ], 422);
            }

            $bankName = $refundMethod;
            $accountNumber = $validated['phone_number'];
            $accountHolder = $validated['account_holder'] ?? $user->name;
        }

        $refundRequest = DB::transaction(function () use ($order, $user, $validated, $refundMethod, $bankName, $accountNumber, $accountHolder, $calculation) {
            $refundRequest = RefundRequest::create([
                'order_id' => $order->id,
                'user_id' => $user->id,
                'cancel_reason' => $validated['cancel_reason'],
                'refund_method' => $refundMethod,
                'bank_name' => $bankName,
                'account_number' => $accountNumber,
                'account_holder' => $accountHolder,
                'refund_amount' => $calculation['refund_amount'],
                'penalty_amount' => $calculation['penalty_amount'],
                'refund_status' => 'pending',
                'requested_at' => now('Asia/Jakarta'),
            ]);

            $order->update(['status' => 'Cancel Requested']);

            return $refundRequest;
        });

        return response()->json([
            'success' => true,
            'message' => $calculation['refund_amount'] > 0
                ? 'Permintaan pembatalan dan refund berhasil diajukan.'
                : 'Pembatalan berhasil, tetapi dana tidak dapat dikembalikan karena sudah Hari-H pendakian.',
            'data' => [
                'refund_request' => $refundRequest,
                'calculation' => $calculation,
            ],
        ], 201);
    }

    public function byOrder(Request $request, int $orderId)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $order = Order::findOrFail($orderId);
        $isAdmin = (int) $user->level === 3;

        if (!$isAdmin && (string) $order->id_user !== (string) $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke pesanan ini.',
            ], 403);
        }

        $refundRequest = RefundRequest::with([
            'order:id,id_user,id_gunung,id_jalur,tanggal_naik,tanggal_turun,total_harga_tiket,status',
            'order.mountain:id,nama',
            'order.trail:id,nama',
            'user:id,name,email',
        ])
            ->where('order_id', $orderId)
            ->orderByDesc('requested_at')
            ->first();

        if (!$refundRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Refund request untuk order ini belum ditemukan.',
            ], 404);
        }

        $proofUrl = $refundRequest->proof_of_transfer
            ? url('storage/' . $refundRequest->proof_of_transfer)
            : null;

        $statusMessage = match ($refundRequest->refund_status) {
            'pending' => 'Permintaan pembatalan sedang menunggu proses admin.',
            'approved' => 'Permintaan telah disetujui. Menunggu transfer dari admin.',
            'rejected' => 'Permintaan refund ditolak oleh admin.',
            'refunded' => (float) $refundRequest->refund_amount > 0
                ? 'Refund telah ditransfer oleh admin.'
                : 'Pembatalan berhasil, tetapi dana tidak dapat dikembalikan karena sudah Hari-H pendakian.',
            default => 'Status refund tidak dikenal.',
        };

        $penalty = (float) $refundRequest->penalty_amount;
        $adminPenaltyShare = round($penalty * 0.10, 2);
        $rangerPenaltyShare = round($penalty - $adminPenaltyShare, 2);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $refundRequest->id,
                'order_id' => $refundRequest->order_id,
                'refund_status' => $refundRequest->refund_status,
                'status_message' => $statusMessage,
                'cancel_reason' => $refundRequest->cancel_reason,
                'refund_method' => $refundRequest->refund_method,
                'bank_name' => $refundRequest->bank_name,
                'account_number' => $refundRequest->account_number,
                'account_holder' => $refundRequest->account_holder,
                'refund_amount' => (float) $refundRequest->refund_amount,
                'penalty_amount' => $penalty,
                'admin_penalty_share' => $adminPenaltyShare,
                'ranger_penalty_share' => $rangerPenaltyShare,
                'proof_of_transfer' => $refundRequest->proof_of_transfer,
                'proof_url' => $proofUrl,
                'requested_at' => optional($refundRequest->requested_at)->toIso8601String(),
                'processed_at' => optional($refundRequest->processed_at)->toIso8601String(),
                'order' => [
                    'status' => $refundRequest->order?->status,
                    'tanggal_naik' => $refundRequest->order?->tanggal_naik,
                    'mountain_name' => $refundRequest->order?->mountain?->nama,
                    'trail_name' => $refundRequest->order?->trail?->nama,
                ],
            ],
        ]);
    }

    public function adminIndex(Request $request)
    {
        $adminCheck = $this->ensureAdmin($request);
        if ($adminCheck !== null) {
            return $adminCheck;
        }

        $status = $request->query('status');

        $query = RefundRequest::with([
            'order:id,id_user,id_gunung,id_jalur,tanggal_naik,tanggal_turun,total_harga_tiket,status',
            'user:id,name,email',
        ])->orderByDesc('requested_at');

        if (!empty($status)) {
            $query->where('refund_status', $status);
        }

        return response()->json([
            'success' => true,
            'data' => $query->get(),
        ]);
    }

    public function approve(Request $request, int $id)
    {
        $adminCheck = $this->ensureAdmin($request);
        if ($adminCheck !== null) {
            return $adminCheck;
        }

        $refundRequest = RefundRequest::with('order')->findOrFail($id);

        if ($refundRequest->refund_status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya request berstatus pending yang dapat di-approve.',
            ], 422);
        }

        $refundRequest->update([
            'refund_status' => 'approved',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Refund request berhasil di-approve.',
            'data' => $refundRequest->fresh(),
        ]);
    }

    public function reject(Request $request, int $id)
    {
        $adminCheck = $this->ensureAdmin($request);
        if ($adminCheck !== null) {
            return $adminCheck;
        }

        $refundRequest = RefundRequest::with('order')->findOrFail($id);

        if (!in_array($refundRequest->refund_status, ['pending', 'approved'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Request ini tidak dapat ditolak pada status saat ini.',
            ], 422);
        }

        DB::transaction(function () use ($refundRequest) {
            $refundRequest->update([
                'refund_status' => 'rejected',
                'processed_at' => now('Asia/Jakarta'),
            ]);

            $refundRequest->order?->update([
                'status' => 'Booking',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Refund request berhasil ditolak.',
            'data' => $refundRequest->fresh(),
        ]);
    }

    public function markRefunded(Request $request, int $id)
    {
        $adminCheck = $this->ensureAdmin($request);
        if ($adminCheck !== null) {
            return $adminCheck;
        }

        $request->validate([
            'proof_of_transfer' => 'required|image|mimes:jpg,jpeg,png|max:4096',
        ]);

        $refundRequest = RefundRequest::with('order')->findOrFail($id);

        if (!in_array($refundRequest->refund_status, ['approved', 'pending'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Request ini tidak dapat ditandai refunded.',
            ], 422);
        }

        $proofPath = $request->file('proof_of_transfer')->store('refund_proofs', 'public');

        DB::transaction(function () use ($refundRequest, $proofPath) {
            $refundRequest->update([
                'refund_status' => 'refunded',
                'proof_of_transfer' => $proofPath,
                'processed_at' => now('Asia/Jakarta'),
            ]);

            $refundRequest->order?->update([
                'status' => 'Cancelled',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Refund request berhasil ditandai sebagai refunded.',
            'data' => $refundRequest->fresh(),
        ]);
    }

    private function ensureAdmin(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        if ((int) $user->level !== 3) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Hanya admin yang dapat melakukan aksi ini.',
            ], 403);
        }

        return null;
    }
}
