<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DetailJalurGunungController;
use App\Http\Controllers\Api\GunungController;
use App\Http\Controllers\Api\JalurController;
use App\Http\Controllers\Api\PesananController;
use App\Http\Controllers\Api\AnggotaPesananController;
use App\Http\Controllers\Api\TransaksiController;
use App\Http\Controllers\Api\TataTertibController;
use App\Http\Controllers\Api\PaymentController;
use App\Models\Pesanan;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/', function () {
    return response()->json([
        'status' => false,
        'message' => 'Unauthorized access'
    ], 401);
})->name('login');

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::get('gunung', [GunungController::class, 'index']);
Route::get('/gunung/{id_gunung}', [JalurController::class, 'index']);
Route::get('/gunung/{id_gunung}/jalur/{id_jalur}', [DetailJalurGunungController::class, 'index']);
Route::get('/gunung/{id_gunung}/jalur/{id_jalur}/jalurbooking', [DetailJalurGunungController::class, 'JalurBooking']);
Route::get('/pesanan', [PesananController::class, 'index']);

Route::prefix('pesanan')->group(function () {
    Route::post('/', [PesananController::class, 'buatPesanan']);
    Route::post('{pesananId}/tambah-anggota', [PesananController::class, 'tambahAnggota']); // Menambahkan anggota ke pesanan
    Route::get('{pesananId}', [PesananController::class, 'lihatPesanan']); // Melihat detail pesanan
    Route::delete('{id}', [PesananController::class, 'destroy']);

});

Route::get('/pesanan/{pesananId}/detailPesanan', [PesananController::class, 'getDetailPesanan']);

Route::prefix('anggota-pesanan')->group(function () {
    Route::post('{pesananId}/tambah', [AnggotaPesananController::class, 'tambahAnggota']); // Menambahkan anggota
    Route::delete('{pesananId}/hapus/{userId}', [AnggotaPesananController::class, 'hapusAnggota']); // Menghapus anggota
    Route::get('{pesananId}', [AnggotaPesananController::class, 'daftarAnggota']); // Melihat daftar anggota
});

Route::get('transaksi', [TransaksiController::class, 'index']);
// Route::post('transaksi', [TransaksiController::class, 'create']);
Route::post('/transaksi/store', [TransaksiController::class, 'store']);
Route::post('/transaksi/update-pembayaran/{id}', [TransaksiController::class, 'updatePembayaran']);
Route::get('/transaksi/{id}/with-payment', [TransaksiController::class, 'getTransactionWithPayment']);


Route::get('/payments', [PaymentController::class, 'index']);
Route::post('/payments', [PaymentController::class, 'store']);

Route::prefix('tata-tertib')->group(function () {
    Route::get('/', [TataTertibController::class, 'index']); // Get all
    Route::post('/', [TataTertibController::class, 'store']); // Create
    Route::get('/{id}', [TataTertibController::class, 'show']); // Get by ID
    Route::put('/{id}', [TataTertibController::class, 'update']); // Update
    Route::delete('/{id}', [TataTertibController::class, 'destroy']); // Delete
    Route::get('/jalur/{jalurId}', [TataTertibController::class, 'getByJalur']); // Get by Jalur ID
});
Route::middleware('auth:sanctum')->group(function () {
    // ... route lainnya
    Route::post('/update-password/{id}', [AuthController::class, 'updatePassword']);
});
Route::get('/user-data/{id?}', [AuthController::class, 'getUserData']);
Route::post('users/{id}', [AuthController::class, 'update']);