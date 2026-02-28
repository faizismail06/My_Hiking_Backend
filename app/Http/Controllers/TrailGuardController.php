<?php

namespace App\Http\Controllers;

use App\Models\TrailWeb;
use App\Models\OrderWeb;
use App\Models\TransactionWeb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

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

        // Today's statistics
        $today = Carbon::today();
        $visitorsToday = OrderWeb::where('id_jalur', $trail->id)
            ->whereDate('created_at', $today)
            ->count();

        // Total visitors this month
        $visitorsThisMonth = OrderWeb::where('id_jalur', $trail->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Revenue this month
        $revenueThisMonth = TransactionWeb::whereHas('order', function ($query) use ($trail) {
            $query->where('id_jalur', $trail->id);
        })
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status_pesanan', 'Verified')
            ->sum('total_bayar');

        // Recent orders
        $recentOrders = OrderWeb::where('id_jalur', $trail->id)
            ->with(['user', 'orderMembers'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('guards.dashboard', compact(
            'trail',
            'visitorsToday',
            'visitorsThisMonth',
            'revenueThisMonth',
            'recentOrders'
        ));
    }

    // Trail management
    public function trailManagement()
    {
        $user = Auth::user();
        $trail = TrailWeb::with(['mountain', 'province', 'regency', 'district', 'village'])
            ->where('user_id', $user->id)
            ->first();

        if (!$trail) {
            return redirect()->back()->with('error', 'You have not been assigned to manage a trail.');
        }

        return view('guards.trail', compact('trail'));
    }

    // Update trail info
    public function updateTrail(Request $request)
    {
        $user = Auth::user();
        $trail = TrailWeb::where('user_id', $user->id)->first();

        if (!$trail) {
            return redirect()->back()->with('error', 'You do not have access to update this trail.');
        }

        $request->validate([
            'deskripsi' => 'nullable|string|max:1000',
            'map_basecamp' => 'nullable|string|max:255',
            'gambar_jalur' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'deskripsi' => $request->deskripsi,
            'map_basecamp' => $request->map_basecamp,
        ];

        if ($request->hasFile('gambar_jalur')) {
            // Delete old image if exists
            if ($trail->gambar_jalur) {
                Storage::disk('public')->delete('images/' . $trail->gambar_jalur);
            }

            $file = $request->file('gambar_jalur');
            $imageName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('images', $imageName, 'public');
            $data['gambar_jalur'] = $imageName;
        }

        $trail->update($data);

        return redirect()->route('guards.trail')->with('success', 'Trail information updated successfully!');
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
            ->with(['user', 'orderMembers']);

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

        return view('guards.history', compact('orders', 'trail'));
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
            ->with(['order.user', 'payment'])
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->where('status_pesanan', 'Verified')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalRevenue = $transactions->sum('total_bayar');

        // Daily revenue chart
        $dailyRevenue = TransactionWeb::whereHas('order', function ($query) use ($trail) {
            $query->where('id_jalur', $trail->id);
        })
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->where('status_pesanan', 'Verified')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_bayar) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('guards.revenue', compact('transactions', 'totalRevenue', 'dailyRevenue', 'trail', 'month', 'year'));
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

        $order = OrderWeb::with(['trail.mountain', 'user', 'orderMembers', 'transaction.payment'])
            ->where('id', $orderId)
            ->where('id_jalur', $trail->id)
            ->first();

        if (!$order) {
            return redirect()->route('guards.scanner')->with('error', 'Order not found or not for your trail.');
        }

        return view('guards.order-detail', compact('order', 'trail'));
    }

    // Manual search order
    public function manualSearch(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer'
        ]);

        return redirect()->route('guards.scanner.detail', $request->order_id);
    }

    // Update order status
    public function updateStatus(Request $request, $orderId)
    {
        $user = Auth::user();
        $trail = TrailWeb::where('user_id', $user->id)->first();

        if (!$trail) {
            return redirect()->back()->with('error', 'You do not have access.');
        }

        $order = OrderWeb::where('id', $orderId)
            ->where('id_jalur', $trail->id)
            ->first();

        if (!$order) {
            return redirect()->back()->with('error', 'Order not found.');
        }

        $request->validate([
            'status' => 'required|in:Booking,Sedang Mendaki,Selesai'
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

        $user = Auth::user();
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
}
