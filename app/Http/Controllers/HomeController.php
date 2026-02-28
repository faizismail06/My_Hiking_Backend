<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MountainWeb;
use App\Models\TrailWeb;
use App\Models\TransactionWeb;
use App\Models\UserWeb;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Menghitung data yang diperlukan
        $totalTransaksi = TransactionWeb::count(); // Hitung semua transaksi
        $totalGunung = MountainWeb::count(); // Hitung jumlah gunung
        $totalJalur = TrailWeb::count(); // Hitung jumlah jalur
        $totalUser = UserWeb::count(); // Hitung jumlah user
        $totalPendapatan = TransactionWeb::sum('total_bayar'); // Total pendapatan dari semua transaksi

        // Mengirim data ke view
        return view('home', compact('totalTransaksi', 'totalGunung', 'totalJalur', 'totalUser','totalPendapatan'));
    }
}