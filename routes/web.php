<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MountainController;
use App\Http\Controllers\TrailController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RuleController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TrailGuardController;


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

Route::get('/', function () {
    return redirect('/login');
});
Auth::routes();

Route::middleware(['auth', 'level3'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
});

Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::get('/about', function () {
    return view('about');
})->name('about');

// Mountain routes
Route::get('/mountains', [MountainController::class, 'index'])->name('mountains.index');
Route::get('/mountains/create', [MountainController::class, 'create'])->name('mountains.create');
Route::resource('mountains', MountainController::class)->except(['create', 'index']);
Route::resource('mountains', MountainController::class);

// Trail routes
Route::resource('trails', TrailController::class);

// Rule routes
Route::resource('rules', RuleController::class);

// Region routes (for AJAX dropdowns)
Route::get('/get-regencies/{province_id}', [RegionController::class, 'getRegencies']);
Route::get('/get-districts/{regency_id}', [RegionController::class, 'getDistricts']);
Route::get('/get-villages/{district_id}', [RegionController::class, 'getVillages']);

Route::get('/trails/{id}/edit', [TrailController::class, 'edit'])->name('trails.edit');
Route::put('/trails/{id}', [TrailController::class, 'update'])->name('trails.update');

Route::delete('/mountains/{id}', [MountainController::class, 'destroy'])->name('mountains.destroy');

Route::get('mountains/{id}/edit', [MountainController::class, 'edit'])->name('mountains.edit');
Route::post('mountains/{id}', [MountainController::class, 'update'])->name('mountains.update');

// Transaction routes
Route::middleware(['auth'])->group(function () {
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{id}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::post('/transactions/{id}/verify', [TransactionController::class, 'verify'])->name('transactions.verify');
    Route::post('/transactions/{id}/unverify', [TransactionController::class, 'unverify'])->name('transactions.unverify');
});

Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');

// History routes
Route::middleware('auth')->group(function () {
    Route::get('/history', [HistoryController::class, 'index'])->name('history.index');
    Route::get('/history/{id}', [HistoryController::class, 'show'])->name('history.show');
    Route::put('/history/{id}/update-status', [HistoryController::class, 'updateStatus'])->name('history.updateStatus');
    Route::patch('/transactions/{id}/verify', [TransactionController::class, 'verify'])->name('transactions.verify');
    Route::post('/transactions/{id}/unverify', [TransactionController::class, 'unverify'])->name('transactions.unverify');
});


Route::get('/history', [HistoryController::class, 'index'])->name('history.index');
Route::get('/history/{id}', [HistoryController::class, 'show'])->name('history.show');
Route::post('/history/scan', [HistoryController::class, 'scan'])->name('history.scan');
Route::post('/history/{id}/update-status', [HistoryController::class, 'updateStatus'])->name('history.updateStatus');

Route::middleware(['auth'])->group(function () {
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/{id}/edit', [PaymentController::class, 'edit'])->name('payments.edit');
    Route::put('/payments/{id}', [PaymentController::class, 'update'])->name('payments.update');
    Route::delete('/payments/{id}', [PaymentController::class, 'destroy'])->name('payments.destroy');
});

// Trail Guard Routes (Level 2)
Route::middleware(['penjaga'])->prefix('guards')->name('guards.')->group(function () {
    Route::get('/dashboard', [TrailGuardController::class, 'dashboard'])->name('dashboard');
    Route::get('/trail', [TrailGuardController::class, 'trailManagement'])->name('trail');
    Route::post('/trail/update', [TrailGuardController::class, 'updateTrail'])->name('trail.update');
    Route::get('/history', [TrailGuardController::class, 'visitorHistory'])->name('history');
    Route::get('/revenue', [TrailGuardController::class, 'revenueReport'])->name('revenue');
    Route::post('/check-in/{id}', [TrailGuardController::class, 'checkIn'])->name('checkin');
    Route::post('/check-out/{id}', [TrailGuardController::class, 'checkOut'])->name('checkout');

    // QR Code Scanner
    Route::get('/scanner', [TrailGuardController::class, 'scanner'])->name('scanner');
    Route::get('/scanner/detail/{id}', [TrailGuardController::class, 'orderDetail'])->name('scanner.detail');
    Route::get('/order/{id}', [TrailGuardController::class, 'orderDetail'])->name('order.detail');
    Route::post('/scanner/manual', [TrailGuardController::class, 'manualSearch'])->name('scanner.manual');
    Route::put('/orders/{id}/status', [TrailGuardController::class, 'updateStatus'])->name('updateStatus');

    // Guard Profile
    Route::get('/profile', [TrailGuardController::class, 'profile'])->name('profile');
    Route::put('/profile', [TrailGuardController::class, 'updateProfile'])->name('profile.update');
});