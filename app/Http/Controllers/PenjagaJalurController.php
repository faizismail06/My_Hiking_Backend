<?php

namespace App\Http\Controllers;

use App\Models\JalurWeb;
use App\Models\PesananWeb;
use App\Models\TransaksiWeb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PenjagaJalurController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Dashboard utama penjaga jalur
    public function dashboard()
    {
        $user = Auth::user();

        // Ambil jalur yang dikelola penjaga ini
        $jalur = JalurWeb::where('user_id', $user->id)->first();

        if (!$jalur) {
            return redirect()->back()->with('error', 'Anda belum ditugaskan untuk mengelola jalur.');
        }

        // Statistik hari ini
        $today = Carbon::today();
        $pengunjungHariIni = PesananWeb::where('id_jalur', $jalur->id)
            ->whereDate('created_at', $today)
            ->count();

        // Total pengunjung bulan ini
        $pengunjungBulanIni = PesananWeb::where('id_jalur', $jalur->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Pendapatan bulan ini
        $pendapatanBulanIni = TransaksiWeb::whereHas('pesanan', function ($query) use ($jalur) {
            $query->where('id_jalur', $jalur->id);
        })
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status_pesanan', 'Verified')
            ->sum('total_bayar');

        // Pesanan terbaru
        $pesananTerbaru = PesananWeb::where('id_jalur', $jalur->id)
            ->with(['user', 'anggota'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('penjaga.dashboard', compact(
            'jalur',
            'pengunjungHariIni',
            'pengunjungBulanIni',
            'pendapatanBulanIni',
            'pesananTerbaru'
        ));
    }

    // Manajemen jalur
    public function manajemenJalur()
    {
        $user = Auth::user();
        $jalur = JalurWeb::with(['gunung', 'province', 'regency', 'district', 'village'])
            ->where('user_id', $user->id)
            ->first();

        if (!$jalur) {
            return redirect()->back()->with('error', 'Anda belum ditugaskan untuk mengelola jalur.');
        }

        return view('penjaga.jalur', compact('jalur'));
    }

    // Update info jalur
    public function updateJalur(Request $request)
    {
        $user = Auth::user();
        $jalur = JalurWeb::where('user_id', $user->id)->first();

        if (!$jalur) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk update jalur ini.');
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
            // Hapus gambar lama jika ada
            if ($jalur->gambar_jalur) {
                Storage::disk('public')->delete('images/' . $jalur->gambar_jalur);
            }

            $file = $request->file('gambar_jalur');
            $gambarName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('images', $gambarName, 'public');
            $data['gambar_jalur'] = $gambarName;
        }

        $jalur->update($data);

        return redirect()->route('penjaga.jalur')->with('success', 'Informasi jalur berhasil diperbarui!');
    }

    // Riwayat pengunjung
    public function riwayatPengunjung(Request $request)
    {
        $user = Auth::user();
        $jalur = JalurWeb::where('user_id', $user->id)->first();

        if (!$jalur) {
            return redirect()->back()->with('error', 'Anda belum ditugaskan untuk mengelola jalur.');
        }

        $search = $request->input('search');
        $status = $request->input('status');

        $query = PesananWeb::where('id_jalur', $jalur->id)
            ->with(['user', 'anggota']);

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $pesanan = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('penjaga.riwayat', compact('pesanan', 'jalur'));
    }

    // Laporan pendapatan
    public function laporanPendapatan(Request $request)
    {
        $user = Auth::user();
        $jalur = JalurWeb::where('user_id', $user->id)->first();

        if (!$jalur) {
            return redirect()->back()->with('error', 'Anda belum ditugaskan untuk mengelola jalur.');
        }

        $bulan = $request->input('bulan', now()->month);
        $tahun = $request->input('tahun', now()->year);

        // Transaksi berhasil
        $transaksi = TransaksiWeb::whereHas('pesanan', function ($query) use ($jalur) {
            $query->where('id_jalur', $jalur->id);
        })
            ->with(['pesanan.user', 'payment'])
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->where('status_pesanan', 'Verified')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalPendapatan = $transaksi->sum('total_bayar');

        // Grafik pendapatan per hari
        $pendapatanPerHari = TransaksiWeb::whereHas('pesanan', function ($query) use ($jalur) {
            $query->where('id_jalur', $jalur->id);
        })
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->where('status_pesanan', 'Verified')
            ->select(DB::raw('DATE(created_at) as tanggal'), DB::raw('SUM(total_bayar) as total'))
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        return view('penjaga.pendapatan', compact('transaksi', 'totalPendapatan', 'pendapatanPerHari', 'jalur', 'bulan', 'tahun'));
    }

    // Check in pengunjung
    public function checkIn($pesananId)
    {
        $user = Auth::user();
        $jalur = JalurWeb::where('user_id', $user->id)->first();

        $pesanan = PesananWeb::where('id', $pesananId)
            ->where('id_jalur', $jalur->id)
            ->first();

        if (!$pesanan) {
            return redirect()->back()->with('error', 'Pesanan tidak ditemukan.');
        }

        $pesanan->update([
            'check_in' => now(),
            'status' => 'Sedang Mendaki'
        ]);

        return redirect()->back()->with('success', 'Check-in berhasil!');
    }

    // Check out pengunjung
    public function checkOut($pesananId)
    {
        $user = Auth::user();
        $jalur = JalurWeb::where('user_id', $user->id)->first();

        $pesanan = PesananWeb::where('id', $pesananId)
            ->where('id_jalur', $jalur->id)
            ->first();

        if (!$pesanan) {
            return redirect()->back()->with('error', 'Pesanan tidak ditemukan.');
        }

        $pesanan->update([
            'check_out' => now(),
            'status' => 'Selesai'
        ]);

        return redirect()->back()->with('success', 'Check-out berhasil!');
    }

    // Halaman scanner QR Code
    public function scanner()
    {
        $user = Auth::user();
        $jalur = JalurWeb::where('user_id', $user->id)->first();

        if (!$jalur) {
            return redirect()->back()->with('error', 'Anda belum ditugaskan untuk mengelola jalur.');
        }

        return view('penjaga.scanner', compact('jalur'));
    }

    // Detail pesanan dari scanner
    public function detailPesanan($pesananId)
    {
        $user = Auth::user();
        $jalur = JalurWeb::where('user_id', $user->id)->first();

        if (!$jalur) {
            return redirect()->back()->with('error', 'Anda belum ditugaskan untuk mengelola jalur.');
        }

        $pesanan = PesananWeb::with(['jalur.gunung', 'user', 'anggotaPesanan', 'transaksi.payment'])
            ->where('id', $pesananId)
            ->where('id_jalur', $jalur->id)
            ->first();

        if (!$pesanan) {
            return redirect()->route('penjaga.scanner')->with('error', 'Pesanan tidak ditemukan atau bukan pesanan untuk jalur Anda.');
        }

        return view('penjaga.detail-pesanan', compact('pesanan', 'jalur'));
    }

    // Manual search pesanan
    public function manualSearch(Request $request)
    {
        $request->validate([
            'pesanan_id' => 'required|integer'
        ]);

        return redirect()->route('penjaga.scanner.detail', $request->pesanan_id);
    }

    // Update status pesanan
    public function updateStatus(Request $request, $pesananId)
    {
        $user = Auth::user();
        $jalur = JalurWeb::where('user_id', $user->id)->first();

        if (!$jalur) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses.');
        }

        $pesanan = PesananWeb::where('id', $pesananId)
            ->where('id_jalur', $jalur->id)
            ->first();

        if (!$pesanan) {
            return redirect()->back()->with('error', 'Pesanan tidak ditemukan.');
        }

        $request->validate([
            'status' => 'required|in:Booking,Sedang Mendaki,Selesai'
        ]);

        $newStatus = $request->status;

        // Validasi transisi status
        if ($pesanan->status == 'Booking' && $newStatus == 'Sedang Mendaki') {
            $pesanan->update([
                'status' => 'Sedang Mendaki',
                'check_in' => now()
            ]);
            $message = 'Check-in berhasil! Pendaki sudah mulai mendaki.';
        } elseif ($pesanan->status == 'Sedang Mendaki' && $newStatus == 'Selesai') {
            $pesanan->update([
                'status' => 'Selesai',
                'check_out' => now()
            ]);
            $message = 'Check-out berhasil! Pendakian selesai.';
        } else {
            return redirect()->back()->with('error', 'Transisi status tidak valid.');
        }

        return redirect()->back()->with('success', $message);
    }
}
