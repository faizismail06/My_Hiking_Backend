<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GunungWeb;
use App\Models\JalurWeb;
use App\Models\TransaksiWeb;
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
        $totalTransaksi = TransaksiWeb::count(); // Hitung semua transaksi
        $totalGunung = GunungWeb::count(); // Hitung jumlah gunung
        $totalJalur = JalurWeb::count(); // Hitung jumlah jalur
        $totalUser = UserWeb::count(); // Hitung jumlah user
        $totalPendapatan = TransaksiWeb::sum('total_bayar'); // Total pendapatan dari semua transaksi

        // Mengirim data ke view
        return view('home', compact('totalTransaksi', 'totalGunung', 'totalJalur', 'totalUser','totalPendapatan'));
    }
}