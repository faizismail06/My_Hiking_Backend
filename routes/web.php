<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GunungController;
use App\Http\Controllers\JalurController;
use App\Http\Controllers\WilayahController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RiwayatController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TataTertibController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PenjagaJalurController;
// use App\Http\Controllers\CheckController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', 'HomeController@index');
Auth::routes();
// Route::get('/home', 'HomeController@index')->name('home');
// Route::get('/', [HomeController::class, 'index']);
// Auth::routes();
// Route::get('/home', [HomeController::class, 'index'])->name('home');
// Route::controller(HomeController::class)->group(function () {
//     Route::get('/', 'index');
//     Route::get('/home', 'index')->name('home');
// });
Route::middleware(['auth', 'level3'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
});

Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::get('/about', function () {
    return view('about');
})->name('about');

// Rute untuk daftar gunung
Route::get('/gunung', [GunungController::class, 'index'])->name('gunung');
// Rute untuk menampilkan form create gunung
Route::get('/gunung/create', [GunungController::class, 'create'])->name('gunung.create');
// Resource route tanpa index
Route::resource('gunung', GunungController::class)->except(['create', 'index']);
Route::resource('gunung', GunungController::class);
Route::resource('jalur', JalurController::class);
Route::resource('tata_tertib', TataTertibController::class);

Route::get('/get-regencies/{province_id}', [WilayahController::class, 'getRegencies']);
Route::get('/get-districts/{regency_id}', [WilayahController::class, 'getDistricts']);
Route::get('/get-villages/{district_id}', [WilayahController::class, 'getVillages']);

Route::get('/jalur/{id}/edit', [JalurController::class, 'edit'])->name('jalur.edit');
Route::put('/jalur/{id}', [JalurController::class, 'update'])->name('jalur.update');

Route::delete('/gunung/{id}', [GunungController::class, 'destroy'])->name('gunung.destroy');

Route::get('gunung/{id}/edit', [GunungController::class, 'edit'])->name('gunung.edit');
Route::post('gunung/{id}', [GunungController::class, 'update'])->name('gunung.update');

// Route untuk transaksi
Route::middleware(['auth'])->group(function () {
    Route::get('/transaksi', [TransaksiController::class, 'index'])->name('transaksi.index');
    Route::get('/transaksi/{id}', [TransaksiController::class, 'show'])->name('transaksi.show');
    Route::post('/transaksi/{id}/verify', [TransaksiController::class, 'verify'])->name('transaksi.verify');
    Route::post('/transaksi/{id}/unverify', [TransaksiController::class, 'unverify'])->name('transaksi.unverify');
});

Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
// Route::get('/riwayat', [RiwayatController::class, 'index'])->name('riwayat.index');


Route::middleware('auth')->group(function () {
    Route::get('/riwayat', [RiwayatController::class, 'index'])->name('riwayat.index');
    Route::get('/riwayat/{id}', [RiwayatController::class, 'show'])->name('riwayat.show');
    Route::put('/riwayat/{id}/update-status', [RiwayatController::class, 'updateStatus'])->name('riwayat.updateStatus');
    Route::patch('/transaksi/{id}/verify', [TransaksiController::class, 'verify'])->name('transaksi.verify');
    Route::post('/transaksi/{id}/unverify', [TransaksiController::class, 'unverify'])->name('transaksi.unverify');
});


Route::get('/riwayat', [RiwayatController::class, 'index'])->name('riwayat.index');
Route::get('/riwayat/{id}', [RiwayatController::class, 'show'])->name('riwayat.show');
Route::post('/riwayat/scan', [RiwayatController::class, 'scan'])->name('riwayat.scan');
Route::post('/riwayat/{id}/update-status', [RiwayatController::class, 'updateStatus'])->name('riwayat.updateStatus');

Route::middleware(['auth'])->group(function () {
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/{id}/edit', [PaymentController::class, 'edit'])->name('payments.edit');
    Route::put('/payments/{id}', [PaymentController::class, 'update'])->name('payments.update');
    Route::delete('/payments/{id}', [PaymentController::class, 'destroy'])->name('payments.destroy');
});

// Routes untuk Penjaga Jalur (Level 2)
Route::middleware(['penjaga'])->prefix('penjaga')->name('penjaga.')->group(function () {
    Route::get('/dashboard', [PenjagaJalurController::class, 'dashboard'])->name('dashboard');
    Route::get('/jalur', [PenjagaJalurController::class, 'manajemenJalur'])->name('jalur');
    Route::post('/jalur/update', [PenjagaJalurController::class, 'updateJalur'])->name('jalur.update');
    Route::get('/riwayat', [PenjagaJalurController::class, 'riwayatPengunjung'])->name('riwayat');
    Route::get('/pendapatan', [PenjagaJalurController::class, 'laporanPendapatan'])->name('pendapatan');
    Route::post('/check-in/{id}', [PenjagaJalurController::class, 'checkIn'])->name('checkin');
    Route::post('/check-out/{id}', [PenjagaJalurController::class, 'checkOut'])->name('checkout');

    // Scanner QR Code
    Route::get('/scanner', [PenjagaJalurController::class, 'scanner'])->name('scanner');
    Route::get('/scanner/detail/{id}', [PenjagaJalurController::class, 'detailPesanan'])->name('scanner.detail');
    Route::post('/scanner/manual', [PenjagaJalurController::class, 'manualSearch'])->name('scanner.manual');
    Route::put('/pesanan/{id}/status', [PenjagaJalurController::class, 'updateStatus'])->name('updateStatus');
});