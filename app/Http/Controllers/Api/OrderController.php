<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\Log;
use App\Models\OfflineTrackSync;
use App\Models\Order;
use App\Models\Trail;
use App\Models\Transaction;
use App\Models\User;
use App\Services\DSSService;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Exception;

class OrderController extends Controller
{
    private const MAX_GPX_CONTENT_CHARS = 1000000;

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

                $this->syncOrderLifecycleStatus($item, $transaction);
                $item->refresh();

                $transactionStatus = $transaction?->status_pesanan ?? 'Incomplete';
                $isPaid = $transactionStatus === 'Complete';
                $displayStatus = $isPaid
                    ? $item->status
                    : ($item->status === 'Expired' ? 'Expired' : 'Bayar');

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
            'tanggal_turun' => 'required|date|after_or_equal:tanggal_naik',
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

            $this->syncOrderLifecycleStatus($order, $order->transaction);
            $order->refresh();

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
            'transaction_time' => $data['transaction_time'] ?? $transaction->transaction_time,
            'fraud_status' => $fraudStatus ?? $transaction->fraud_status,
            'waktu_pembayaran' => $newStatus === 'Complete'
                ? ($transaction->waktu_pembayaran ?? now())
                : null,
        ]);

        if ($newStatus === 'Complete' && $transaction->order && $transaction->order->status === 'Expired') {
            $transaction->order->update(['status' => 'Booking']);
        } elseif (
            in_array($status, ['expire', 'cancel'], true)
            && $transaction->order
            && in_array($transaction->order->status, ['Booking', 'Expired'], true)
        ) {
            $transaction->order->update(['status' => 'Expired']);
        }
    }

    private function syncOrderLifecycleStatus(Order $order, ?Transaction $transaction = null): void
    {
        $activeTransaction = $transaction ?? $order->transaction;

        // Expire unpaid orders when payment deadline has passed.
        if ($activeTransaction && $activeTransaction->status_pesanan !== 'Complete') {
            if ($this->isPaymentWindowExpired($activeTransaction) && $order->status !== 'Expired') {
                $order->update(['status' => 'Expired']);
            }

            return;
        }

        if (!$activeTransaction || $activeTransaction->status_pesanan !== 'Complete') {
            return;
        }

        if ($order->status !== 'Booking') {
            return;
        }

        $startDate = Carbon::parse($order->tanggal_naik)->startOfDay();
        $today = now()->startOfDay();

        // If booking is already past hiking date and never checked in, mark as expired.
        if ($today->gt($startDate)) {
            $order->update(['status' => 'Expired']);
        }
    }

    private function isPaymentWindowExpired(Transaction $transaction): bool
    {
        $baseTime = $transaction->transaction_time ?? $transaction->created_at;
        if (!$baseTime) {
            return false;
        }

        $duration = $this->midtransService->getPaymentExpiryDuration();
        $unit = $this->midtransService->getPaymentExpiryUnit();

        $expiredAt = Carbon::parse($baseTime);
        $expiredAt = match ($unit) {
            'second' => $expiredAt->addSeconds($duration),
            'hour' => $expiredAt->addHours($duration),
            'day' => $expiredAt->addDays($duration),
            default => $expiredAt->addMinutes($duration),
        };

        return now()->greaterThanOrEqualTo($expiredAt);
    }

    public function offlineTrackSync(Request $request, $orderId)
    {
        $authUser = $request->user();
        if (!$authUser) {
            return response()->json([
                'success' => false,
                'code' => 'UNAUTHENTICATED',
                'message' => 'User belum login.',
            ], 401);
        }

        $validated = $request->validate([
            'client_cache_id' => 'required|string|max:191',
            'source' => 'nullable|string|max:80',
            'cached_at' => 'nullable|date',
            'point_count' => 'required|integer|min:1',
            'distance_meters' => 'required|numeric|min:0',
            'duration_seconds' => 'required|integer|min:0',
            'gpx_content' => 'required|string',
        ]);

        $order = Order::find($orderId);
        if (!$order) {
            return response()->json([
                'success' => false,
                'code' => 'ORDER_NOT_FOUND',
                'message' => 'Order tidak ditemukan.',
            ], 404);
        }

        if ((string) $order->id_user !== (string) $authUser->id) {
            Log::warning('offline_track_sync_forbidden_order_access', [
                'order_id' => (string) $orderId,
                'auth_user_id' => (string) $authUser->id,
            ]);

            return response()->json([
                'success' => false,
                'code' => 'FORBIDDEN_ORDER_ACCESS',
                'message' => 'Order bukan milik user yang sedang login.',
            ], 403);
        }

        if ($order->status !== 'Sedang Mendaki') {
            Log::warning('offline_track_sync_invalid_order_status', [
                'order_id' => (string) $order->id,
                'auth_user_id' => (string) $authUser->id,
                'order_status' => $order->status,
            ]);

            return response()->json([
                'success' => false,
                'code' => 'ORDER_STATUS_NOT_SYNCABLE',
                'message' => 'Sync track offline hanya boleh saat status order Sedang Mendaki.',
                'order_status' => $order->status,
            ], 409);
        }

        if (mb_strlen($validated['gpx_content']) > self::MAX_GPX_CONTENT_CHARS) {
            Log::warning('offline_track_sync_gpx_too_large', [
                'order_id' => (string) $order->id,
                'auth_user_id' => (string) $authUser->id,
                'client_cache_id' => $validated['client_cache_id'],
                'gpx_chars' => mb_strlen($validated['gpx_content']),
                'max_chars' => self::MAX_GPX_CONTENT_CHARS,
            ]);

            return response()->json([
                'success' => false,
                'code' => 'GPX_TOO_LARGE',
                'message' => 'Konten GPX terlalu besar. Silakan pecah track menjadi beberapa bagian.',
                'max_chars' => self::MAX_GPX_CONTENT_CHARS,
            ], 422);
        }

        $existing = OfflineTrackSync::where('order_id', $order->id)
            ->where('client_cache_id', $validated['client_cache_id'])
            ->first();

        if ($existing) {
            Log::info('offline_track_sync_duplicate', [
                'order_id' => (string) $order->id,
                'auth_user_id' => (string) $authUser->id,
                'client_cache_id' => $validated['client_cache_id'],
                'sync_id' => $existing->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Track offline sudah pernah disinkronkan.',
                'data' => [
                    'sync_id' => $existing->id,
                    'order_id' => (string) $existing->order_id,
                    'client_cache_id' => $existing->client_cache_id,
                    'sync_status' => 'duplicate',
                    'is_duplicate' => true,
                    'synced_at' => optional($existing->synced_at)->toIso8601String(),
                ],
            ], 200);
        }

        $sync = OfflineTrackSync::create([
            'order_id' => $order->id,
            'user_id' => $authUser->id,
            'client_cache_id' => $validated['client_cache_id'],
            'source' => $validated['source'] ?? 'mobile_offline_tracking',
            'cached_at' => $validated['cached_at'] ?? null,
            'point_count' => $validated['point_count'],
            'distance_meters' => $validated['distance_meters'],
            'duration_seconds' => $validated['duration_seconds'],
            'gpx_content' => $validated['gpx_content'],
            'sync_status' => 'synced',
            'synced_at' => now(),
        ]);

        Log::info('offline_track_sync_created', [
            'order_id' => (string) $order->id,
            'auth_user_id' => (string) $authUser->id,
            'client_cache_id' => $sync->client_cache_id,
            'sync_id' => $sync->id,
            'point_count' => $sync->point_count,
            'distance_meters' => $sync->distance_meters,
            'duration_seconds' => $sync->duration_seconds,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Track offline berhasil disinkronkan.',
            'data' => [
                'sync_id' => $sync->id,
                'order_id' => (string) $sync->order_id,
                'client_cache_id' => $sync->client_cache_id,
                'sync_status' => $sync->sync_status,
                'is_duplicate' => false,
                'synced_at' => optional($sync->synced_at)->toIso8601String(),
            ],
        ], 201);
    }

    public function listOfflineTrackSyncs(Request $request, $orderId)
    {
        $authUser = $request->user();
        if (!$authUser) {
            return response()->json([
                'success' => false,
                'code' => 'UNAUTHENTICATED',
                'message' => 'User belum login.',
            ], 401);
        }

        $order = Order::find($orderId);
        if (!$order) {
            return response()->json([
                'success' => false,
                'code' => 'ORDER_NOT_FOUND',
                'message' => 'Order tidak ditemukan.',
            ], 404);
        }

        if ((string) $order->id_user !== (string) $authUser->id) {
            return response()->json([
                'success' => false,
                'code' => 'FORBIDDEN_ORDER_ACCESS',
                'message' => 'Order bukan milik user yang sedang login.',
            ], 403);
        }

        $limit = (int) $request->input('limit', 50);
        if ($limit < 1) {
            $limit = 1;
        }
        if ($limit > 200) {
            $limit = 200;
        }

        $withGpx = filter_var($request->input('with_gpx', false), FILTER_VALIDATE_BOOLEAN);

        $items = OfflineTrackSync::query()
            ->where('order_id', $order->id)
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(function (OfflineTrackSync $item) use ($withGpx) {
                $data = [
                    'sync_id' => $item->id,
                    'order_id' => (string) $item->order_id,
                    'user_id' => (string) $item->user_id,
                    'client_cache_id' => $item->client_cache_id,
                    'source' => $item->source,
                    'cached_at' => optional($item->cached_at)->toIso8601String(),
                    'point_count' => $item->point_count,
                    'distance_meters' => $item->distance_meters,
                    'duration_seconds' => $item->duration_seconds,
                    'sync_status' => $item->sync_status,
                    'synced_at' => optional($item->synced_at)->toIso8601String(),
                    'created_at' => optional($item->created_at)->toIso8601String(),
                ];

                if ($withGpx) {
                    $data['gpx_content'] = $item->gpx_content;
                }

                return $data;
            })
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Daftar offline track sync berhasil diambil.',
            'data' => $items,
            'meta' => [
                'order_id' => (string) $order->id,
                'count' => $items->count(),
                'limit' => $limit,
                'with_gpx' => $withGpx,
            ],
        ], 200);
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
