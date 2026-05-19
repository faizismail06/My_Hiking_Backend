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
use App\Http\Controllers\TrailGuardController;
use App\Http\Controllers\RefundWebController;
use App\Http\Controllers\AdminEarningsController;

use App\Http\Controllers\TrailGuardWithdrawalController;


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

    Route::prefix('admin/refunds')->name('admin.refunds.')->group(function () {
        Route::get('/', [RefundWebController::class, 'index'])->name('index');
        Route::get('/{id}', [RefundWebController::class, 'show'])->name('show');
        Route::post('/{id}/approve', [RefundWebController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [RefundWebController::class, 'reject'])->name('reject');
        Route::post('/{id}/refunded', [RefundWebController::class, 'markRefunded'])->name('refunded');
    });

    // Admin Earnings Routes
    Route::prefix('admin/earnings')->name('admin.earnings.')->group(function () {
        Route::get('/', [AdminEarningsController::class, 'index'])->name('index');
        Route::get('/withdrawal-requests', [AdminEarningsController::class, 'withdrawalRequests'])->name('withdrawal-requests');
        Route::get('/withdrawal/{id}', [AdminEarningsController::class, 'showWithdrawalRequest'])->name('withdrawal-request-detail');
        Route::post('/withdrawal/{id}/approve', [AdminEarningsController::class, 'approveWithdrawalRequest'])->name('withdrawal-request-approve');
        Route::post('/withdrawal/{id}/reject', [AdminEarningsController::class, 'rejectWithdrawalRequest'])->name('withdrawal-request-reject');
        Route::post('/withdrawal/{id}/complete', [AdminEarningsController::class, 'completeWithdrawalRequest'])->name('withdrawal-request-complete');
        Route::get('/admin-fee-settings', [AdminEarningsController::class, 'adminFeeSettings'])->name('admin-fee-settings');
        Route::put('/admin-fee-settings', [AdminEarningsController::class, 'updateAdminFeeSettings'])->name('admin-fee-settings-update');
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
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{id}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::post('/transactions/{id}/verify', [TransactionController::class, 'verify'])->name('transactions.verify');
    Route::post('/transactions/{id}/unverify', [TransactionController::class, 'unverify'])->name('transactions.unverify');

Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
Route::post('/users', [UserController::class, 'store'])->name('users.store');
Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

// History routes
    Route::get('/history', [HistoryController::class, 'index'])->name('history.index');
    Route::get('/history/{id}', [HistoryController::class, 'show'])->name('history.show');
    Route::post('/history/scan', [HistoryController::class, 'scan'])->name('history.scan');
    Route::put('/history/{id}/update-status', [HistoryController::class, 'updateStatus'])->name('history.updateStatus');

}); // <-- End of level3 middleware group

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
    Route::post('/scanner/auto-scan/{id}', [TrailGuardController::class, 'autoScan'])->name('scanner.autoscan');
    Route::get('/order/{id}', [TrailGuardController::class, 'orderDetail'])->name('order.detail');
    Route::post('/scanner/manual', [TrailGuardController::class, 'manualSearch'])->name('scanner.manual');
    Route::put('/orders/{id}/status', [TrailGuardController::class, 'updateStatus'])->name('updateStatus');

    // SAR Dashboard (Emergency Panic Requests)
    Route::get('/sar-dashboard', [\App\Http\Controllers\SarDashboardController::class, 'index'])->name('sar.index');
    Route::get('/sar-dashboard/check-new-panics', [\App\Http\Controllers\SarDashboardController::class, 'checkNewPanics'])->name('sar.check-new-panics');
    Route::get('/sar-dashboard/{id}', [\App\Http\Controllers\SarDashboardController::class, 'show'])->name('sar.show');
    Route::post('/sar-dashboard/{id}/respond', [\App\Http\Controllers\SarDashboardController::class, 'respond'])->name('sar.respond');
    Route::post('/sar-dashboard/{id}/resolve', [\App\Http\Controllers\SarDashboardController::class, 'resolve'])->name('sar.resolve');

    // Guard Profile
    Route::get('/profile', [TrailGuardController::class, 'profile'])->name('profile');
    Route::put('/profile', [TrailGuardController::class, 'updateProfile'])->name('profile.update');
});

// Trail Guard Withdrawal Routes
Route::middleware(['penjaga'])->prefix('trail-guard/withdrawal')->name('trail-guard.withdrawal.')->group(function () {
    Route::get('/', [TrailGuardWithdrawalController::class, 'index'])->name('index');
    Route::get('/create', [TrailGuardWithdrawalController::class, 'create'])->name('create');
    Route::post('/', [TrailGuardWithdrawalController::class, 'store'])->name('store');
    Route::get('/{id}', [TrailGuardWithdrawalController::class, 'show'])->name('show');
    Route::delete('/{id}/cancel', [TrailGuardWithdrawalController::class, 'cancel'])->name('cancel');
});