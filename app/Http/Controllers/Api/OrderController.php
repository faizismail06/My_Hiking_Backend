<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\Trail;
use App\Models\Transaction;
use App\Models\User;
use App\Services\DSSService;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderController extends Controller
{
    protected MidtransService $midtransService;
    protected DSSService $dssService;

    public function __construct(MidtransService $midtransService, DSSService $dssService)
    {
        $this->midtransService = $midtransService;
        $this->dssService = $dssService;
    }

    /**
     * Display a listing of orders.
     */
    public function index()
    {
        try {
            $orders = Order::with("mountain", "trail", "booker", "transaction")
                ->orderBy('id')
                ->get()
                ->map(function ($item) {
                $transaction = $item->transaction;

                if ($transaction) {
                    $this->syncTransactionStatusFromMidtrans($transaction);
                    $transaction->refresh();
                }

                $transactionStatus = $transaction?->status_pesanan ?? 'Incomplete';
                $isPaid = $transactionStatus === 'Complete';
                $displayStatus = $isPaid ? $item->status : 'Bayar';

                return [
                    "id" => (string) $item->id,
                    "id_gunung" => $item->id_gunung,
                    "id_jalur" => $item->id_jalur,
                    "id_user" => $item->id_user,
                    "tanggal_naik" => $item->tanggal_naik,
                    "tanggal_turun" => $item->tanggal_turun,
                    "total_harga_tiket" => $item->total_harga_tiket,
                    "status" => $displayStatus,
                    "order_status" => $item->status,
                    "transaction_status" => $transactionStatus,
                    "is_paid" => $isPaid,
                    "gunung" => $item->mountain->nama,
                    "jalur" => $item->trail->nama,
                    "user" => $item->booker->name,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Successfully get data on orders',
                'data' => $orders,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get data on orders',
                'data' => $e->getMessage(),
            ], 500);
        }
    }


    // Create new order and add members
    public function createOrder(Request $request)
    {
        Log::info('Request received:', $request->all());
        $authUser = $request->user();

        $request->validate([
            'id_gunung' => 'required|exists:mountains,id',
            'id_jalur' => 'required|exists:routes,id',
            'id_user' => 'nullable|exists:users,id',
            'tanggal_naik' => 'required|date',
            'tanggal_turun' => 'required|date',
            'total_harga_tiket' => 'required|numeric',
            'anggota_ids' => 'array',
            'anggota_ids.*' => 'exists:users,id',
            'force_continue' => 'nullable|boolean',
        ]);

        if ($authUser && $request->filled('id_user') && (string) $request->id_user !== (string) $authUser->id) {
            return response()->json([
                'message' => 'Anda tidak dapat membuat pesanan untuk user lain.',
            ], 403);
        }

        $bookerId = $authUser?->id ?? $request->id_user;
        $booker = $authUser ?: User::find($bookerId);
        $trail = Trail::where('id', $request->id_jalur)
            ->where('id_gunung', $request->id_gunung)
            ->first();

        if (!$booker) {
            return response()->json([
                'success' => false,
                'message' => 'User pemesan tidak ditemukan.',
            ], 404);
        }

        if (!$trail) {
            return response()->json([
                'success' => false,
                'message' => 'Jalur tidak ditemukan untuk gunung yang dipilih.',
            ], 422);
        }

        $dssEvaluation = null;
        $forceContinue = filter_var($request->input('force_continue', false), FILTER_VALIDATE_BOOLEAN);

        if ((int) $booker->level === 1) {
            $dssEvaluation = $this->dssService->evaluateRoute($booker, $trail);

            if (($dssEvaluation['risk_level'] ?? null) === 'high_risk' && !$forceContinue) {
                return response()->json([
                    'success' => false,
                    'code' => 'HIGH_RISK_CONFIRMATION_REQUIRED',
                    'message' => 'Rute ini berisiko tinggi untuk tingkat pengalaman Anda. Konfirmasi force_continue untuk melanjutkan.',
                    'dss' => $dssEvaluation,
                ], 409);
            }
        }

        try {
            $order = DB::transaction(function () use ($request, $bookerId) {
                // Create order
                $order = Order::create([
                    'id_gunung' => $request->id_gunung,
                    'id_jalur' => $request->id_jalur,
                    'id_user' => $bookerId,
                    'tanggal_naik' => $request->tanggal_naik,
                    'tanggal_turun' => $request->tanggal_turun,
                    'total_harga_tiket' => $request->total_harga_tiket,
                ]);

                // Normalize member IDs: unique, valid, and exclude the booker itself.
                $memberIds = collect($request->input('anggota_ids', []))
                    ->filter(fn ($id) => is_numeric($id))
                    ->map(fn ($id) => (int) $id)
                    ->filter(fn ($id) => $id > 0 && $id !== (int) $bookerId)
                    ->unique()
                    ->values()
                    ->all();

                if (!empty($memberIds)) {
                    // Avoid duplicate pivot rows if endpoint is retried.
                    $order->members()->syncWithoutDetaching($memberIds);
                }

                return $order;
            });

            return response()->json([
                'message' => 'Order created successfully!',
                'order' => $order->load('members'),
                'dss' => $dssEvaluation,
                'warning' => $this->buildDssWarning($dssEvaluation),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the order.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function buildDssWarning(?array $dssEvaluation): ?array
    {
        if (!$dssEvaluation) {
            return null;
        }

        $riskLevel = $dssEvaluation['risk_level'] ?? null;

        if ($riskLevel === 'caution') {
            return [
                'type' => 'caution',
                'message' => $dssEvaluation['message'] ?? 'Rute butuh pertimbangan ekstra.',
            ];
        }

        if ($riskLevel === 'high_risk') {
            return [
                'type' => 'high_risk',
                'message' => $dssEvaluation['message'] ?? 'Rute berisiko tinggi.',
            ];
        }

        return null;
    }

    // Add members to existing order
    public function addMembers(Request $request, $orderId)
    {
        $request->validate([
            'anggota_ids' => 'required|array',
            'anggota_ids.*' => 'exists:users,id',
        ]);

        try {
            $order = Order::findOrFail($orderId);

            // Add members to order
            $order->members()->attach($request->anggota_ids);

            return response()->json([
                'message' => 'Members added to order successfully.',
                'order' => $order,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while adding members.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // View order details
    public function viewOrder($orderId)
    {
        try {
            $order = Order::with(
                'mountain:id,nama',
                'trail:id,nama',
                'booker:id,name',
                'members:id,name',
                'transaction:id,id_pesanan,status_pesanan,waktu_pembayaran,midtrans_order_id'
            )->findOrFail($orderId);

            if ($order->transaction) {
                $this->syncTransactionStatusFromMidtrans($order->transaction);
                $order->transaction->refresh();
            }

            $canPrintTicket = $order->transaction?->status_pesanan === 'Complete';

            return response()->json([
                'order' => $order,
                'can_print_ticket' => $canPrintTicket,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Order not found.',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    private function syncTransactionStatusFromMidtrans(Transaction $transaction): void
    {
        if (empty($transaction->midtrans_order_id)) {
            return;
        }

        if ($transaction->status_pesanan === 'Complete') {
            return;
        }

        $result = $this->midtransService->getTransactionStatus($transaction->midtrans_order_id);
        if (!($result['success'] ?? false)) {
            return;
        }

        $data = $result['data'] ?? [];
        $status = $data['transaction_status'] ?? 'pending';
        $fraudStatus = $data['fraud_status'] ?? null;

        $newStatus = match ($status) {
            'capture' => $fraudStatus === 'challenge' ? 'Incomplete' : 'Complete',
            'settlement' => 'Complete',
            default => 'Incomplete',
        };

        $transaction->update([
            'status_pesanan' => $newStatus,
            'payment_type' => $data['payment_type'] ?? $transaction->payment_type,
            'transaction_id' => $data['transaction_id'] ?? $transaction->transaction_id,
            'fraud_status' => $fraudStatus ?? $transaction->fraud_status,
            'waktu_pembayaran' => $newStatus === 'Complete'
                ? ($transaction->waktu_pembayaran ?? now())
                : null,
        ]);

        if ($newStatus === 'Complete' && $transaction->order && $transaction->order->status !== 'Booking') {
            $transaction->order->update(['status' => 'Booking']);
        }
    }
    
    public function getOrderDetail($orderId)
    {
        try {
            $order = Order::with(['booker:id,name', 'members', 'transaction'])
                ->findOrFail($orderId);

            if (!$order->transaction) {
                throw new \Exception('Transaction not found for this order.');
            }

            $orderDetail = [
                'id_pesanan' => $order->id,
                'tanggal_pesanan' => $order->tanggal_naik,
                'nama_pemesan' => $order->booker->name,
                'total_anggota' => $order->members->count() + 1, // +1 for the main booker
                'total_harga' => $order->transaction->total_bayar,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Successfully retrieved order detail',
                'data' => $orderDetail,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve order detail',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            // Find order by ID
            $order = Order::findOrFail($id);

            // Find related transaction based on id_pesanan
            $transaction = Transaction::where('id_pesanan', $order->id)->first();

            // Delete transaction if exists
            if ($transaction) {
                $transaction->delete();
            }

            // Delete order
            $order->delete();

            return response()->json([
                'message' => 'Order and related transaction deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while deleting the order.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
