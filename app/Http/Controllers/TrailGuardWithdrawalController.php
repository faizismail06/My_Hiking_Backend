<?php

namespace App\Http\Controllers;

use App\Models\WithdrawalRequest;
use App\Models\AdminFeeSettings;
use App\Services\EarningsCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TrailGuardWithdrawalController extends Controller
{
    protected $earningsService;

    /**
     * Create a new controller instance.
     */
    public function __construct(EarningsCalculationService $earningsService)
    {
        $this->middleware('auth');
        $this->middleware('penjaga');
        $this->earningsService = $earningsService;
    }

    /**
     * Ensure guard balance columns are in sync with latest paid transactions.
     */
    private function syncGuardBalance($user): void
    {
        $this->earningsService->updateEarningsForUser($user);
        $user->refresh();
    }

    /**
     * Get fee settings, create default when table has no row yet.
     */
    private function getFeeSettings(): AdminFeeSettings
    {
        return AdminFeeSettings::firstOrCreate(
            ['id' => 1],
            [
                'fee_percentage' => 5.00,
                'fixed_fee' => 0,
                'fee_type' => 'percentage',
                'description' => 'Default admin fee setting - 5% of withdrawal amount',
                'updated_by' => null,
            ]
        );
    }

    /**
     * Show withdrawal request form
     */
    public function create()
    {
        $user = Auth::user();
        $this->syncGuardBalance($user);

        $adminFeeSettings = $this->getFeeSettings();

        $data = [
            'user' => $user,
            'adminFeeSettings' => $adminFeeSettings,
            'availableBalance' => $user->available_balance,
        ];

        return view('trail-guard.withdrawal.create', $data);
    }

    /**
     * Store withdrawal request
     */
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10000',
            'withdrawal_method' => 'required|in:bank_transfer,e_wallet',
            'bank_name' => 'required_if:withdrawal_method,bank_transfer|nullable|string|max:100',
            'account_number' => 'required_if:withdrawal_method,bank_transfer|nullable|string|max:50',
            'account_holder' => 'required_if:withdrawal_method,bank_transfer|nullable|string|max:100',
            'e_wallet_type' => 'required_if:withdrawal_method,e_wallet|nullable|in:gcash,grab,linkaja,ovo,dana',
            'e_wallet_number' => 'required_if:withdrawal_method,e_wallet|nullable|string|max:50',
        ]);

        $user = Auth::user();
        $this->syncGuardBalance($user);
        $amount = $request->amount;

        // Validate available balance
        if ($amount > $user->available_balance) {
            return back()->with('error', 'Jumlah melebihi saldo yang tersedia.')->withInput();
        }

        // Calculate admin fee
        $this->getFeeSettings();
        $adminFee = AdminFeeSettings::calculateFee($amount);
        $netAmount = $amount - $adminFee;

        if ($netAmount < 10000) {
            return back()->with('error', 'Jumlah bersih setelah biaya admin harus minimal Rp 10.000.')->withInput();
        }

        // Create withdrawal request
        $withdrawalRequest = WithdrawalRequest::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'admin_fee' => $adminFee,
            'net_amount' => $netAmount,
            'withdrawal_method' => $request->withdrawal_method,
            'bank_name' => $request->bank_name,
            'account_number' => $request->account_number,
            'account_holder' => $request->account_holder,
            'e_wallet_type' => $request->e_wallet_type,
            'e_wallet_number' => $request->e_wallet_number,
            'status' => 'pending',
        ]);

        return redirect()->route('trail-guard.withdrawal.index')->with('success', 'Request penarikan saldo telah dikirim. Harap tunggu persetujuan dari admin.');
    }

    /**
     * Show withdrawal requests history
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $this->syncGuardBalance($user);
        $query = WithdrawalRequest::where('user_id', $user->id);

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

        $withdrawalRequests = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('trail-guard.withdrawal.index', [
            'withdrawalRequests' => $withdrawalRequests,
            'user' => $user,
            'filters' => $request->all(),
        ]);
    }

    /**
     * Show withdrawal request detail
     */
    public function show($id)
    {
        $this->syncGuardBalance(Auth::user());
        $withdrawalRequest = WithdrawalRequest::where('user_id', Auth::id())->findOrFail($id);

        return view('trail-guard.withdrawal.show', [
            'withdrawalRequest' => $withdrawalRequest,
        ]);
    }

    /**
     * Cancel withdrawal request (only if pending)
     */
    public function cancel($id)
    {
        $withdrawalRequest = WithdrawalRequest::where('user_id', Auth::id())->findOrFail($id);

        if ($withdrawalRequest->status !== 'pending') {
            return back()->with('error', 'Hanya request dengan status pending yang dapat dibatalkan.');
        }

        DB::transaction(function () use ($withdrawalRequest) {
            $withdrawalRequest->delete();
        });

        return back()->with('success', 'Request penarikan saldo telah dibatalkan.');
    }
}
