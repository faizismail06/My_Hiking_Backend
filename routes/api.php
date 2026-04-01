<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MountainTrailDetailController;
use App\Http\Controllers\Api\MountainController;
use App\Http\Controllers\Api\TrailController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderMemberController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\RuleController;
use App\Http\Controllers\Api\FriendController;
use App\Http\Controllers\Api\ExperienceOnboardingController;
use App\Http\Controllers\Api\WeatherController;

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
Route::post('auth/google', [AuthController::class, 'loginWithGoogle']);

// Mountain routes
Route::get('mountains', [MountainController::class, 'index']);
Route::get('mountains/home-feed', [MountainController::class, 'homeFeed']);
Route::get('/mountains/{id_gunung}', [TrailController::class, 'index']);
Route::get('/mountains/{id_gunung}/trails/{id_jalur}', [MountainTrailDetailController::class, 'index']);
Route::get('/mountains/{id_gunung}/trails/{id_jalur}/preview', [MountainTrailDetailController::class, 'preview']);
Route::get('/mountains/{id_gunung}/trails/{id_jalur}/booking', [MountainTrailDetailController::class, 'trailBooking']);
Route::get('/weather/current', [WeatherController::class, 'current']);
Route::get('/weather/forecast', [WeatherController::class, 'forecast']);

// Order routes
Route::get('/orders', [OrderController::class, 'index']);

Route::prefix('orders')->group(function () {
    Route::post('/', [OrderController::class, 'createOrder'])->middleware(['auth:sanctum', 'hiker.ready']);
    Route::post('{orderId}/add-members', [OrderController::class, 'addMembers']);
    Route::get('{orderId}', [OrderController::class, 'viewOrder']);
    Route::delete('{id}', [OrderController::class, 'destroy']);
});

Route::get('/orders/{orderId}/detail', [OrderController::class, 'getOrderDetail']);

Route::prefix('order-members')->group(function () {
    Route::post('{orderId}/add', [OrderMemberController::class, 'addMember']);
    Route::delete('{orderId}/remove/{userId}', [OrderMemberController::class, 'removeMember']);
    Route::get('{orderId}', [OrderMemberController::class, 'listMembers']);
});

// Transaction routes
Route::get('transactions', [TransactionController::class, 'index']);
Route::post('/transactions/store', [TransactionController::class, 'store']);
Route::post('/transactions/update-payment/{id}', [TransactionController::class, 'updatePayment']);
Route::get('/transactions/{id}/detail', [TransactionController::class, 'getTransactionDetail']);
Route::get('/payment-methods', [TransactionController::class, 'getPaymentMethods']);

// Rule routes
Route::prefix('rules')->group(function () {
    Route::get('/', [RuleController::class, 'index']);
    Route::post('/', [RuleController::class, 'store']);
    Route::get('/{id}', [RuleController::class, 'show']);
    Route::put('/{id}', [RuleController::class, 'update']);
    Route::delete('/{id}', [RuleController::class, 'destroy']);
    Route::get('/trail/{trailId}', [RuleController::class, 'getByTrail']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/update-password/{id}', [AuthController::class, 'updatePassword']);
    Route::get('/onboarding/experience/status', [ExperienceOnboardingController::class, 'status']);
    Route::post('/onboarding/experience', [ExperienceOnboardingController::class, 'store']);
});
Route::get('/user-data/{id?}', [AuthController::class, 'getUserData']);
Route::post('users/{id}', [AuthController::class, 'update']);

// Friend routes
Route::prefix('friends')->group(function () {
    Route::get('/', [FriendController::class, 'index']); // Get all friends
    Route::get('/pending', [FriendController::class, 'pendingRequests']); // Get pending requests
    Route::get('/search', [FriendController::class, 'searchUsers']); // Search users
    Route::post('/add', [FriendController::class, 'addFriend']); // Send friend request
    Route::put('/{friendshipId}/accept', [FriendController::class, 'acceptFriend']); // Accept request
    Route::put('/{friendshipId}/reject', [FriendController::class, 'rejectFriend']); // Reject request
    Route::delete('/{friendshipId}', [FriendController::class, 'removeFriend']); // Remove friend
});

// Midtrans Payment Gateway routes
Route::prefix('midtrans')->group(function () {
    Route::post('/create-payment', [\App\Http\Controllers\Api\MidtransController::class, 'createPayment']);
    Route::post('/notification', [\App\Http\Controllers\Api\MidtransController::class, 'handleNotification']);
    Route::get('/status/{orderId}', [\App\Http\Controllers\Api\MidtransController::class, 'checkStatus']);
    Route::get('/config', [\App\Http\Controllers\Api\MidtransController::class, 'getConfig']);
    Route::get('/finish', [\App\Http\Controllers\Api\MidtransController::class, 'finish']);
});

// Panic/Emergency routes
Route::post('/panic', [\App\Http\Controllers\Api\PanicController::class, 'store'])->middleware(['auth:sanctum', 'hiker.ready']);
Route::get('/panic/order/{orderId}', [\App\Http\Controllers\Api\PanicController::class, 'getByOrder']);
Route::post('/panic/{id}/cancel', [\App\Http\Controllers\Api\PanicController::class, 'cancel']);



// Image proxy route for CORS support (Flutter Web)
Route::get('/images/{path}', function ($path) {
    $storagePath = storage_path('app/public/images/' . $path);
    
    if (!file_exists($storagePath)) {
        return response()->json(['error' => 'Image not found'], 404);
    }
    
    $mimeType = mime_content_type($storagePath);
    return response()->file($storagePath, [
        'Content-Type' => $mimeType,
        'Access-Control-Allow-Origin' => '*',
    ]);
})->where('path', '.*');