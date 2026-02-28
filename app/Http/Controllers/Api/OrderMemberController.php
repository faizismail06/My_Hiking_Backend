<?php

namespace App\Http\Controllers\Api;

use App\Models\OrderMember;
use App\Models\Order;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderMemberController extends Controller
{
    // Add member to order
    public function addMember(Request $request, $orderId)
    {
        $request->validate([
            'id_user' => 'required|exists:users,id',
        ]);

        try {
            $order = Order::findOrFail($orderId);

            // Add member
            $order->members()->attach($request->id_user);

            return response()->json([
                'message' => 'Member added successfully.',
                'members' => $order->members,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Remove member from order
    public function removeMember($orderId, $userId)
    {
        try {
            $order = Order::findOrFail($orderId);

            // Ensure order is not completed
            if ($order->status == 'Selesai') {
                return response()->json([
                    'message' => 'Member cannot be removed because the order is completed.',
                ], 400);
            }

            // Remove member from order
            $order->members()->detach($userId);

            return response()->json([
                'message' => 'Member removed successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while removing member.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    // View list of order members
    public function listMembers($orderId)
    {
        try {
            $order = Order::with('members')->findOrFail($orderId);

            return response()->json([
                'members' => $order->members,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Order not found.',
                'error' => $e->getMessage(),
            ], 404);
        }
    }
}
