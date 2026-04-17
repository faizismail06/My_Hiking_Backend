<?php

namespace App\Http\Controllers;

use App\Models\WithdrawalRequest;
use App\Models\AdminFeeSettings;
use App\Models\Trail;
use App\Models\User;
use App\Services\EarningsCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminEarningsController extends Controller
{
    protected $earningsService;

    /**
     * Create a new controller instance.
     */
    public function __construct(EarningsCalculationService $earningsService)
    {
        $this->middleware('auth');
        $this->earningsService = $earningsService;
    }

    /**
     * Show earnings dashboard
     */
    public function index()
    {
        // Update all trail guard earnings from paid orders
        $this->earningsService->updateAllEarnings();

        // Get all trail guards (users dengan status penjaga jalur)
        $trailGuards = User::where('level', 'penjaga_jalur')
            ->orWhereHas('trails')
            ->select('id', 'name', 'email', 'phone', 'total_earnings', 'withdrawn_amount', 'available_balance', 'transaction_count')
            ->get();

        // Calculate total pending and approved requests
        $pendingRequests = WithdrawalRequest::where('status', 'pending')->count();
        $approvedRequests = WithdrawalRequest::where('status', 'approved')->count();
        $totalWithdrawn = WithdrawalRequest::where('status', 'completed')->sum('net_amount');

        // Calculate total earnings
        $totalEarnings = $trailGuards->sum('total_earnings');

        // Get current admin fee settings
        $adminFeeSettings = AdminFeeSettings::getCurrent();

        $data = [
            'trailGuards' => $trailGuards,
            'totalEarnings' => $totalEarnings,
            'pendingRequests' => $pendingRequests,
            'approvedRequests' => $approvedRequests,
            'totalWithdrawn' => $totalWithdrawn,
            'adminFeeSettings' => $adminFeeSettings,
        ];

        return view('admin.earnings.index', $data);
    }

    /**
     * Show withdrawal requests management page
     */
    public function withdrawalRequests(Request $request)
    {
        $query = WithdrawalRequest::with('user', 'approvedByAdmin');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $withdrawalRequests = $query->orderBy('created_at', 'desc')->paginate(20);
        $trailGuards = User::where('level', 'penjaga_jalur')->get();

        return view('admin.earnings.withdrawal-requests', [
            'withdrawalRequests' => $withdrawalRequests,
            'trailGuards' => $trailGuards,
            'filters' => $request->all(),
        ]);
    }

    /**
     * Show withdrawal request detail
     */
    public function showWithdrawalRequest($id)
    {
        $withdrawalRequest = WithdrawalRequest::with('user', 'approvedByAdmin')->findOrFail($id);

        return view('admin.earnings.withdrawal-request-detail', [
            'withdrawalRequest' => $withdrawalRequest,
        ]);
    }

    /**
     * Approve withdrawal request
     */
    public function approveWithdrawalRequest(Request $request, $id)
    {
        $withdrawalRequest = WithdrawalRequest::findOrFail($id);

        if ($withdrawalRequest->status !== 'pending') {
            return back()->with('error', 'Request sudah diproses sebelumnya.');
        }

        $withdrawalRequest->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Request withdrawal telah disetujui.');
    }

    /**
     * Reject withdrawal request
     */
    public function rejectWithdrawalRequest(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $withdrawalRequest = WithdrawalRequest::findOrFail($id);

        if ($withdrawalRequest->status !== 'pending') {
            return back()->with('error', 'Request sudah diproses sebelumnya.');
        }

        $withdrawalRequest->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Request withdrawal telah ditolak.');
    }

    /**
     * Mark withdrawal request as completed
     */
    public function completeWithdrawalRequest(Request $request, $id)
    {
        $request->validate([
            'transfer_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ]);

        $withdrawalRequest = WithdrawalRequest::findOrFail($id);

        if ($withdrawalRequest->status !== 'approved') {
            return back()->with('error', 'Request harus dalam status approved sebelum dapat diselesaikan.');
        }

        $transferProofPath = $request->file('transfer_proof')
            ? $request->file('transfer_proof')->store('withdrawal-proofs', 'public')
            : null;

        DB::transaction(function () use ($withdrawalRequest, $transferProofPath) {
            $updateData = [
                'status' => 'completed',
                'completed_at' => now(),
            ];

            if ($transferProofPath) {
                $updateData['transfer_proof_path'] = $transferProofPath;
            }

            $withdrawalRequest->update($updateData);

            // Update user's withdrawn amount
            $user = $withdrawalRequest->user;
            $user->update([
                'withdrawn_amount' => $user->withdrawn_amount + $withdrawalRequest->net_amount,
                'available_balance' => $user->available_balance - $withdrawalRequest->net_amount,
            ]);
        });

        return back()->with('success', 'Request withdrawal telah diselesaikan.');
    }

    /**
     * Show admin fee settings page
     */
    public function adminFeeSettings()
    {
        $adminFeeSettings = AdminFeeSettings::getCurrent();

        return view('admin.earnings.admin-fee-settings', [
            'adminFeeSettings' => $adminFeeSettings,
        ]);
    }

    /**
     * Update admin fee settings
     */
    public function updateAdminFeeSettings(Request $request)
    {
        $request->validate([
            'fee_percentage' => 'nullable|numeric|min:0|max:100',
            'fixed_fee' => 'nullable|numeric|min:0',
            'fee_type' => 'required|in:percentage,fixed,both',
            'description' => 'nullable|string|max:500',
        ]);

        $settings = AdminFeeSettings::getCurrent();

        $settings->update([
            'fee_percentage' => $request->fee_percentage ?? 0,
            'fixed_fee' => $request->fixed_fee ?? 0,
            'fee_type' => $request->fee_type,
            'description' => $request->description,
            'updated_by' => Auth::id(),
        ]);

        return back()->with('success', 'Pengaturan biaya admin telah diperbarui.');
    }

    /**
     * Get earning statistics
     */
    public function getEarningStatistics()
    {
        $totalEarnings = User::where('level', 'penjaga_jalur')->sum('total_earnings');
        $totalWithdrawn = WithdrawalRequest::where('status', 'completed')->sum('net_amount');
        $adminFeeCollected = WithdrawalRequest::where('status', 'completed')->sum('admin_fee');
        $pendingAmount = WithdrawalRequest::where('status', 'pending')->sum('net_amount');

        return [
            'total_earnings' => $totalEarnings,
            'total_withdrawn' => $totalWithdrawn,
            'admin_fee_collected' => $adminFeeCollected,
            'pending_amount' => $pendingAmount,
            'available_balance' => $totalEarnings - $totalWithdrawn - $pendingAmount,
        ];
    }
}
