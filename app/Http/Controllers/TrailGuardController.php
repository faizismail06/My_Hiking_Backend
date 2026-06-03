<?php

namespace App\Http\Controllers;


use App\Models\TrailWeb;
use App\Models\OrderWeb;
use App\Models\TransactionWeb;
use App\Models\User;
use App\Services\GpxRouteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class TrailGuardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Main dashboard for trail guard
    public function dashboard()
    {
        $user = Auth::user();

        // Get trail managed by this guard
        $trail = TrailWeb::where('user_id', $user->id)->first();

        if (!$trail) {
            return view('guards.no-trail', ['user' => $user]);
        }

        $paidStatuses = $this->paidTransactionStatuses();

        // Today's paid visitors = booker + additional members from paid orders.
        $today = Carbon::today();
        $paidOrdersToday = OrderWeb::where('id_jalur', $trail->id)
            ->whereDate('created_at', $today)
            ->whereHas('transaction', function ($query) use ($paidStatuses) {
                $query->whereIn('status_pesanan', $paidStatuses);
            })
            ->withCount('orderMembers')
            ->get();
        $visitorsToday = $paidOrdersToday->sum(function ($order) {
            return ((int) $order->order_members_count) + 1;
        });

        // Total paid visitors this month.
        $paidOrdersThisMonth = OrderWeb::where('id_jalur', $trail->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->whereHas('transaction', function ($query) use ($paidStatuses) {
                $query->whereIn('status_pesanan', $paidStatuses);
            })
            ->withCount('orderMembers')
            ->get();
        $visitorsThisMonth = $paidOrdersThisMonth->sum(function ($order) {
            return ((int) $order->order_members_count) + 1;
        });

        // Revenue this month
        $revenueThisMonth = TransactionWeb::whereHas('order', function ($query) use ($trail) {
            $query->where('id_jalur', $trail->id);
        })
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->whereIn('status_pesanan', $paidStatuses)
            ->sum('total_bayar');

        // Recent orders
        $recentOrders = OrderWeb::where('id_jalur', $trail->id)
            ->with(['user', 'orderMembers'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Overdue orders: status 'Sedang Mendaki' dan tanggal_turun sudah lewat
        $overdueOrders = OrderWeb::where('id_jalur', $trail->id)
            ->where('status', 'Sedang Mendaki')
            ->whereDate('tanggal_turun', '<', Carbon::today())
            ->with(['user'])
            ->orderBy('tanggal_turun', 'asc')
            ->get();
        $overdueCount = $overdueOrders->count();

        return view('guards.dashboard', compact(
            'trail',
            'visitorsToday',
            'visitorsThisMonth',
            'revenueThisMonth',
            'recentOrders',
            'overdueOrders',
            'overdueCount'
        ));
    }

    // Trail management
    public function trailManagement()
    {
        $user = Auth::user();
        $trail = TrailWeb::with(['mountain', 'province', 'regency', 'district', 'village', 'posts'])
            ->where('user_id', $user->id)
            ->first();

        if (!$trail) {
            return redirect()->back()->with('error', 'You have not been assigned to manage a trail.');
        }

        return view('guards.trail', compact('trail'));
    }

    // Update trail info
    public function updateTrail(Request $request, GpxRouteService $gpxRouteService)
    {
        $user  = Auth::user();
        $trail = TrailWeb::where('user_id', $user->id)->first();

        if (!$trail) {
            return redirect()->back()->with('error', 'You do not have access to update this trail.');
        }

        // ── Validation ──────────────────────────────────────────────────────
        $request->validate([
            // Existing fields
            'deskripsi'          => 'nullable|string|max:1000',
            'map_basecamp'       => 'nullable|string|max:255',
            'latitude'           => 'nullable|numeric|between:-90,90',
            'longitude'          => 'nullable|numeric|between:-180,180',
            'daily_hiker_limit'  => 'nullable|integer|min:1|max:100000',
            'is_refund_allowed'  => 'required|boolean',
            'gambar_jalur'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gpx_file'           => 'nullable|file|mimes:gpx,xml|max:10240',
            'route_source'       => 'nullable|string|max:50',
            'route_points_json'  => 'nullable|string',
            'trail_posts_json'   => 'nullable|string',

        ]);

        // ── Build data array (non-DSS fields) ───────────────────────────
        $data = [
            'deskripsi'          => $request->deskripsi,
            'map_basecamp'       => $request->map_basecamp,
            'latitude'           => $request->latitude,
            'longitude'          => $request->longitude,
            'daily_hiker_limit'  => $request->filled('daily_hiker_limit') ? (int) $request->daily_hiker_limit : null,
            'is_refund_allowed'  => $request->boolean('is_refund_allowed'),
        ];

        // ── Image upload ─────────────────────────────────────────────────
        if ($request->hasFile('gambar_jalur')) {
            if ($trail->gambar_jalur) {
                Storage::disk('public')->delete('images/' . $trail->gambar_jalur);
            }
            $file      = $request->file('gambar_jalur');
            $imageName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('images', $imageName, 'public');
            $data['gambar_jalur'] = $imageName;
        }

        // ── GPX file ─────────────────────────────────────────────────────
        if ($request->hasFile('gpx_file')) {
            try {
                $parsedRoute       = $gpxRouteService->parseFromUploadedFile($request->file('gpx_file'), 1500);
                $data['route_points'] = $parsedRoute['points'];
                $data['route_source'] = $request->input('route_source', 'manual');
            } catch (\Throwable $e) {
                return redirect()->back()->withErrors(['gpx_file' => $e->getMessage()])->withInput();
            }
        }

        // ── Manual route points ───────────────────────────────────────────
        if ($request->filled('route_points_json') && !$request->hasFile('gpx_file')) {
            try {
                $data['route_points'] = $this->parseRoutePointsJson($request->input('route_points_json'));
                $data['route_source'] = 'manual';
            } catch (ValidationException $e) {
                return redirect()->back()->withErrors($e->errors())->withInput();
            }
        }

        // ── Persist route update + DSS pending submission ─────────────────
        try {
            DB::transaction(function () use ($trail, $data, $request, $user) {
                $trail->fill($data);
                $trail->save();



                // ── Trail posts ───────────────────────────────────────────
                if ($request->has('trail_posts_json')) {
                    $this->syncTrailPosts($trail, $request->input('trail_posts_json'));
                }
            });
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('guards.trail')->with('success', 'Informasi jalur diperbarui.');
    }


    // Visitor history
    public function visitorHistory(Request $request)
    {
        $user = Auth::user();
        $trail = TrailWeb::where('user_id', $user->id)->first();

        if (!$trail) {
            return redirect()->back()->with('error', 'You have not been assigned to manage a trail.');
        }

        $search = $request->input('search');
        $status = $request->input('status');

        $query = OrderWeb::where('id_jalur', $trail->id)
            ->with(['user', 'orderMembers', 'transaction'])
            ->whereHas('transaction', function ($q) {
                $q->where('status_pesanan', 'Complete');
            });

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15);

        // Hitung total overdue (tanpa filter halaman) untuk banner peringatan
        $overdueCount = OrderWeb::where('id_jalur', $trail->id)
            ->where('status', 'Sedang Mendaki')
            ->whereDate('tanggal_turun', '<', Carbon::today())
            ->count();

        return view('guards.history', compact('orders', 'trail', 'overdueCount'));
    }

    // Revenue report
    public function revenueReport(Request $request)
    {
        $user = Auth::user();
        $trail = TrailWeb::where('user_id', $user->id)->first();

        if (!$trail) {
            return redirect()->back()->with('error', 'You have not been assigned to manage a trail.');
        }

        $month = $request->input('bulan', now()->month);
        $year = $request->input('tahun', now()->year);

        // Successful transactions
        $transactions = TransactionWeb::whereHas('order', function ($query) use ($trail) {
            $query->where('id_jalur', $trail->id);
        })
            ->with(['order.user', 'order.orderMembers'])
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->whereIn('status_pesanan', $this->paidTransactionStatuses())
            ->orderBy('created_at', 'desc')
            ->get();

        $totalRevenue = $transactions->sum('total_bayar');
        $totalPaidVisitors = $transactions->sum(function ($transaction) {
            if (!$transaction->order) {
                return 0;
            }

            $memberCount = (int) $transaction->order->orderMembers->count();
            return $memberCount + 1;
        });

        // Daily revenue chart
        $dailyRevenue = TransactionWeb::whereHas('order', function ($query) use ($trail) {
            $query->where('id_jalur', $trail->id);
        })
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->whereIn('status_pesanan', $this->paidTransactionStatuses())
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_bayar) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('guards.revenue', compact('transactions', 'totalRevenue', 'totalPaidVisitors', 'dailyRevenue', 'trail', 'month', 'year'));
    }

    // Check in visitor
    public function checkIn($orderId)
    {
        $user = Auth::user();
        $trail = TrailWeb::where('user_id', $user->id)->first();

        $order = OrderWeb::where('id', $orderId)
            ->where('id_jalur', $trail->id)
            ->first();

        if (!$order) {
            return redirect()->back()->with('error', 'Order not found.');
        }

        $order->update([
            'check_in' => now(),
            'status' => 'Sedang Mendaki'
        ]);

        return redirect()->back()->with('success', 'Check-in successful!');
    }

    // Check out visitor
    public function checkOut($orderId)
    {
        $user = Auth::user();
        $trail = TrailWeb::where('user_id', $user->id)->first();

        $order = OrderWeb::where('id', $orderId)
            ->where('id_jalur', $trail->id)
            ->first();

        if (!$order) {
            return redirect()->back()->with('error', 'Order not found.');
        }

        $order->update([
            'check_out' => now(),
            'status' => 'Selesai'
        ]);

        return redirect()->back()->with('success', 'Check-out successful!');
    }

    // QR Code scanner page
    public function scanner()
    {
        $user = Auth::user();
        $trail = TrailWeb::where('user_id', $user->id)->first();

        if (!$trail) {
            return redirect()->back()->with('error', 'You have not been assigned to manage a trail.');
        }

        return view('guards.scanner', compact('trail'));
    }

    // Order detail from scanner
    public function orderDetail($orderId)
    {
        $user = Auth::user();
        $trail = TrailWeb::where('user_id', $user->id)->first();

        if (!$trail) {
            return redirect()->back()->with('error', 'You have not been assigned to manage a trail.');
        }

        $order = OrderWeb::with(['trail.mountain', 'user', 'orderMembers', 'transaction'])
            ->where('id', $orderId)
            ->where('id_jalur', $trail->id)
            ->first();

        if (!$order) {
            return redirect()->route('guards.scanner')->with('error', 'Order not found or not for your trail.');
        }

        // CRUCIAL: Validasi pembayaran - order hanya bisa diakses jika pembayaran sudah COMPLETE
        if (!$order->transaction || $order->transaction->status_pesanan !== 'Complete') {
            return redirect()->route('guards.scanner')
                ->with('error', 'Pesanan ini belum dibayar. Pembayaran harus diselesaikan terlebih dahulu.');
        }

        // CRUCIAL: Validasi waktu pembayaran - cek apakah order sudah expired
        if ($this->isPaymentExpired($order->transaction)) {
            return redirect()->route('guards.scanner')
                ->with('error', 'ID pesanan ini sudah expired. Waktu pembayaran telah habis.');
        }

        // Validasi order status
        if ($order->status === 'Expired' || $order->status === 'Cancelled') {
            return redirect()->route('guards.scanner')
                ->with('error', 'Pesanan ini tidak valid (status: ' . $order->status . ').');
        }

        return view('guards.order-detail', compact('order', 'trail'));
    }

    // Manual search order
    public function manualSearch(Request $request)
    {
        $request->validate([
            'pesanan_id' => 'required|integer'
        ]);

        $user = Auth::user();
        $trail = TrailWeb::where('user_id', $user->id)->first();

        if (!$trail) {
            return redirect()->route('guards.scanner')
                ->with('manual_search_status', 'no_trail')
                ->with('manual_search_message', 'Anda belum ditugaskan ke jalur manapun.');
        }

        $orderId = (int) $request->pesanan_id;
        $order = OrderWeb::with('transaction')
            ->where('id', $orderId)
            ->where('id_jalur', $trail->id)
            ->first();

        if (!$order) {
            return redirect()->route('guards.scanner')
                ->with('manual_search_status', 'not_found')
                ->with('manual_search_id', $orderId)
                ->with('manual_search_message', 'ID pesanan tidak ditemukan atau bukan untuk jalur Anda.');
        }

        // CRUCIAL: Validasi pembayaran - order hanya bisa di-scan jika pembayaran sudah COMPLETE
        if (!$order->transaction || $order->transaction->status_pesanan !== 'Complete') {
            return redirect()->route('guards.scanner')
                ->with('manual_search_status', 'payment_incomplete')
                ->with('manual_search_id', $orderId)
                ->with('manual_search_message', 'Pembayaran pesanan ini belum selesai. Pelanggan harus menyelesaikan pembayaran terlebih dahulu.');
        }

        // CRUCIAL: Validasi waktu pembayaran - cek apakah order sudah expired
        if ($this->isPaymentExpired($order->transaction)) {
            return redirect()->route('guards.scanner')
                ->with('manual_search_status', 'payment_expired')
                ->with('manual_search_id', $orderId)
                ->with('manual_search_message', 'ID pesanan ini sudah expired. Waktu pembayaran telah habis.');
        }

        // Validasi order status
        if ($order->status === 'Expired' || $order->status === 'Cancelled') {
            return redirect()->route('guards.scanner')
                ->with('manual_search_status', 'order_invalid')
                ->with('manual_search_id', $orderId)
                ->with('manual_search_message', 'Pesanan ini tidak valid (status: ' . $order->status . ').');
        }

        return redirect()->route('guards.order.detail', $orderId);
    }

    // Update order status
    public function updateStatus(Request $request, $orderId)
    {
        $user = Auth::user();
        $trail = TrailWeb::where('user_id', $user->id)->first();

        if (!$trail) {
            return redirect()->back()->with('error', 'You do not have access.');
        }

        $order = OrderWeb::with('transaction')
            ->where('id', $orderId)
            ->where('id_jalur', $trail->id)
            ->first();

        if (!$order) {
            return redirect()->back()->with('error', 'Order not found.');
        }

        // CRUCIAL: Validasi pembayaran sebelum update status
        if (!$order->transaction || $order->transaction->status_pesanan !== 'Complete') {
            return redirect()->back()->with('error', 'Pesanan ini belum dibayar. Pembayaran harus diselesaikan terlebih dahulu sebelum check-in.');
        }

        // CRUCIAL: Validasi waktu pembayaran
        if ($this->isPaymentExpired($order->transaction)) {
            return redirect()->back()->with('error', 'ID pesanan ini sudah expired. Waktu pembayaran telah habis. Check-in tidak dapat dilakukan.');
        }

        $request->validate([
            'status' => 'required|in:Booking,Cancel Requested,Cancelled,Sedang Mendaki,Selesai,Expired'
        ]);

        $newStatus = $request->status;

        // Validate status transition
        if ($order->status == 'Booking' && $newStatus == 'Sedang Mendaki') {
            $order->update([
                'status' => 'Sedang Mendaki',
                'check_in' => now()
            ]);
            $message = 'Check-in successful! Hiker has started climbing.';
        } elseif ($order->status == 'Sedang Mendaki' && $newStatus == 'Selesai') {
            $order->update([
                'status' => 'Selesai',
                'check_out' => now()
            ]);
            $message = 'Check-out successful! Hiking completed.';
        } else {
            return redirect()->back()->with('error', 'Invalid status transition.');
        }

        return redirect()->back()->with('success', $message);
    }

    // Auto scan - immediately update status based on current status
    public function autoScan($orderId)
    {
        $user = Auth::user();
        $trail = TrailWeb::where('user_id', $user->id)->first();

        if (!$trail) {
            return response()->json([
                'success' => false,
                'message' => 'You have not been assigned to manage a trail.'
            ], 403);
        }

        $order = OrderWeb::with('transaction')
            ->where('id', $orderId)
            ->where('id_jalur', $trail->id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found or not for your trail.'
            ], 404);
        }

        // CRUCIAL: Validasi pembayaran - order hanya bisa di-scan jika pembayaran sudah COMPLETE
        if (!$order->transaction || $order->transaction->status_pesanan !== 'Complete') {
            return response()->json([
                'success' => false,
                'message' => 'Pembayaran pesanan ini belum selesai. Pelanggan harus menyelesaikan pembayaran terlebih dahulu.'
            ], 400);
        }

        // CRUCIAL: Validasi waktu pembayaran - cek apakah order sudah expired
        if ($this->isPaymentExpired($order->transaction)) {
            return response()->json([
                'success' => false,
                'message' => 'ID pesanan ini sudah expired. Waktu pembayaran telah habis.'
            ], 400);
        }

        // Validasi order status sebelum check-in
        if ($order->status === 'Expired' || $order->status === 'Cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan ini tidak valid (status: ' . $order->status . ').'
            ], 400);
        }

        // Auto update status based on current status
        if ($order->status == 'Booking' || $order->status == 'Dikonfirmasi') {
            $order->update([
                'status' => 'Sedang Mendaki',
                'check_in' => now()
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Check-in successful! Hiker has started climbing.',
                'new_status' => 'Sedang Mendaki'
            ]);
        } elseif ($order->status == 'Sedang Mendaki') {
            $order->update([
                'status' => 'Selesai',
                'check_out' => now()
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Check-out successful! Hiking completed.',
                'new_status' => 'Selesai'
            ]);
        } elseif ($order->status == 'Selesai') {
            return response()->json([
                'success' => false,
                'message' => 'This order has already been completed.'
            ], 400);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update status from current state.'
            ], 400);
        }
    }

    // Guard Profile
    public function profile()
    {
        return view('guards.profile');
    }

    // Update Guard Profile
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . Auth::user()->id,
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:6|max:12|required_with:current_password',
            'password_confirmation' => 'nullable|max:12|required_with:new_password|same:new_password'
        ]);

        /** @var User|null $user */
        $user = Auth::user();
        if (!$user) {
            return redirect()->back()->with('error', 'User tidak ditemukan.');
        }
        $user->name = $request->input('name');
        $user->email = $request->input('email');

        if (!is_null($request->input('current_password'))) {
            if (\Illuminate\Support\Facades\Hash::check($request->input('current_password'), $user->password)) {
                $user->password = bcrypt($request->input('new_password'));
            } else {
                return redirect()->back()->with('error', 'Password saat ini tidak sesuai.');
            }
        }

        $user->save();

        return redirect()->route('guards.profile')->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * @return array<int, array{lat: float, lng: float}>
     */
    private function parseRoutePointsJson(?string $payload): array
    {
        if ($payload === null || trim($payload) === '') {
            return [];
        }

        $decoded = json_decode($payload, true);
        if (!is_array($decoded)) {
            throw ValidationException::withMessages([
                'route_points_json' => 'Format titik jalur tidak valid.',
            ]);
        }

        $normalized = [];
        foreach ($decoded as $index => $point) {
            if (!is_array($point)) {
                continue;
            }

            $lat = $point['lat'] ?? $point['latitude'] ?? null;
            $lng = $point['lng'] ?? $point['lon'] ?? $point['longitude'] ?? null;

            if (!is_numeric($lat) || !is_numeric($lng)) {
                throw ValidationException::withMessages([
                    'route_points_json' => 'Titik jalur ke-' . ($index + 1) . ' tidak valid.',
                ]);
            }

            $latValue = (float) $lat;
            $lngValue = (float) $lng;
            if ($latValue < -90 || $latValue > 90 || $lngValue < -180 || $lngValue > 180) {
                throw ValidationException::withMessages([
                    'route_points_json' => 'Koordinat titik jalur di luar batas yang diizinkan.',
                ]);
            }

            $normalized[] = [
                'lat' => $latValue,
                'lng' => $lngValue,
            ];
        }

        if (!empty($normalized) && count($normalized) < 2) {
            throw ValidationException::withMessages([
                'route_points_json' => 'Minimal 2 titik dibutuhkan untuk membentuk jalur.',
            ]);
        }

        return $normalized;
    }

    private function syncTrailPosts(TrailWeb $trail, ?string $payload): void
    {
        if ($payload === null || trim($payload) === '') {
            return;
        }

        $decoded = json_decode($payload, true);
        if (!is_array($decoded)) {
            throw ValidationException::withMessages([
                'trail_posts_json' => 'Format data pos tidak valid.',
            ]);
        }

        $rows = [];
        foreach ($decoded as $index => $item) {
            if (!is_array($item)) {
                continue;
            }

            $lat = $item['lat'] ?? $item['latitude'] ?? null;
            $lng = $item['lng'] ?? $item['lon'] ?? $item['longitude'] ?? null;
            $name = trim((string) ($item['name'] ?? ''));

            if (!is_numeric($lat) || !is_numeric($lng)) {
                throw ValidationException::withMessages([
                    'trail_posts_json' => 'Koordinat pos ke-' . ($index + 1) . ' tidak valid.',
                ]);
            }

            $latValue = (float) $lat;
            $lngValue = (float) $lng;

            if ($latValue < -90 || $latValue > 90 || $lngValue < -180 || $lngValue > 180) {
                throw ValidationException::withMessages([
                    'trail_posts_json' => 'Koordinat pos di luar batas yang diizinkan.',
                ]);
            }

            $rows[] = [
                'name' => $name !== '' ? $name : 'Pos ' . ($index + 1),
                'sequence' => $index + 1,
                'latitude' => $latValue,
                'longitude' => $lngValue,
                'elevation' => isset($item['elevation']) && is_numeric($item['elevation'])
                    ? (float) $item['elevation']
                    : null,
                'description' => isset($item['description']) ? trim((string) $item['description']) : null,
                'icon_type' => isset($item['icon_type']) && trim((string) $item['icon_type']) !== ''
                    ? trim((string) $item['icon_type'])
                    : 'signpost',
            ];
        }

        $trail->posts()->delete();
        if (!empty($rows)) {
            $trail->posts()->createMany($rows);
        }
    }

    /**
     * @return array<int, string>
     */
    private function paidTransactionStatuses(): array
    {
        return ['Verified', 'Complete'];
    }

    /**
     * CRUCIAL: Check if payment for a transaction has expired
     * Payment is considered expired if it's pending and has passed the payment window
     * 
     * @param TransactionWeb $transaction
     * @return bool True if payment has expired, false otherwise
     */
    private function isPaymentExpired($transaction): bool
    {
        // Jika sudah Complete, tidak expired
        if ($transaction->status_pesanan === 'Complete') {
            return false;
        }

        // Base time untuk pengecekan
        $baseTime = $transaction->transaction_time ?? $transaction->created_at;
        if (!$baseTime) {
            return true; // Jika tidak ada waktu transaksi, dianggap expired
        }

        // Ambil konfigurasi durasi pembayaran dari config Midtrans
        $duration = config('midtrans.payment_expiry_duration', 15);
        $unit = config('midtrans.payment_expiry_unit', 'minute');

        // Hitung waktu ekspirasi berdasarkan durasi dan unit
        $baseTimeCarbon = Carbon::parse($baseTime);

        switch ($unit) {
            case 'second':
                $expiryTime = $baseTimeCarbon->addSeconds($duration);
                break;
            case 'minute':
                $expiryTime = $baseTimeCarbon->addMinutes($duration);
                break;
            case 'hour':
                $expiryTime = $baseTimeCarbon->addHours($duration);
                break;
            case 'day':
                $expiryTime = $baseTimeCarbon->addDays($duration);
                break;
            default:
                $expiryTime = $baseTimeCarbon->addMinutes(15); // Default 15 menit
        }

        // Cek apakah waktu sekarang sudah melewati waktu ekspirasi
        return now()->isAfter($expiryTime);
    }
}
