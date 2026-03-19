<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PanicRequest;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PanicController extends Controller
{
    /**
     * Create a new panic request (for mobile app users)
     */
    public function store(Request $request)
    {
        $authUser = $request->user();

        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|exists:users,id',
            'order_id' => 'required|exists:orders,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'emergency_type' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($authUser && $request->filled('user_id') && (string) $request->user_id !== (string) $authUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak dapat mengakses data user lain.'
            ], 403);
        }

        $requestUserId = $authUser?->id ?? $request->user_id;

        // Check if order belongs to this user and is active (status = "Sedang Mendaki")
        $order = Order::where('id', $request->order_id)
            ->where('id_user', $requestUserId)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak ditemukan atau bukan milik Anda'
            ], 404);
        }

        if ($order->status !== 'Sedang Mendaki') {
            return response()->json([
                'success' => false,
                'message' => 'Panic hanya dapat diaktifkan saat status Sedang Mendaki'
            ], 400);
        }

        // Check if there's already an active panic request for this order
        $existingPanic = PanicRequest::where('order_id', $request->order_id)
            ->whereIn('status', ['pending', 'responding'])
            ->first();

        if ($existingPanic) {
            return response()->json([
                'success' => false,
                'message' => 'Sudah ada permintaan darurat aktif untuk tiket ini'
            ], 400);
        }

        $panicRequest = PanicRequest::create([
            'user_id' => $requestUserId,
            'order_id' => $request->order_id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'emergency_type' => $request->emergency_type,
            'description' => $request->description,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permintaan darurat berhasil dikirim! Tim SAR akan segera merespons.',
            'data' => $panicRequest
        ], 201);
    }

    /**
     * Get panic request status for a specific order
     */
    public function getByOrder($orderId)
    {
        $panicRequest = PanicRequest::where('order_id', $orderId)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$panicRequest) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Tidak ada permintaan darurat'
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $panicRequest
        ]);
    }

    /**
     * Cancel a panic request (user can cancel if still pending)
     */
    public function cancel($id, Request $request)
    {
        $panicRequest = PanicRequest::findOrFail($id);

        // Verify that the user owns this panic request
        if ($panicRequest->user_id != $request->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke permintaan ini'
            ], 403);
        }

        if ($panicRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya permintaan dengan status pending yang dapat dibatalkan'
            ], 400);
        }

        $panicRequest->update(['status' => 'resolved', 'resolved_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Permintaan darurat berhasil dibatalkan'
        ]);
    }
}
