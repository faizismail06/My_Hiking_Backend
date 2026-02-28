<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;

class OrderController extends Controller
{
    /**
     * Display a listing of orders.
     */
    public function index()
    {
        try {
            $orders = Order::with("mountain", "trail", "booker")->get()->map(function ($item) {
                return [
                    "id" => (string) $item->id,
                    "id_gunung" => $item->id_gunung,
                    "id_jalur" => $item->id_jalur,
                    "id_user" => $item->id_user,
                    "tanggal_naik" => $item->tanggal_naik,
                    "tanggal_turun" => $item->tanggal_turun,
                    "total_harga_tiket" => $item->total_harga_tiket,
                    "status" => $item->status,
                    "gunung" => $item->mountain->nama,
                    "jalur" => $item->trail->nama,
                    "user" => $item->booker->name,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Successfully get data on orders',
                'data' => $orders,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get data on orders',
                'data' => $e->getMessage(),
            ], 500);
        }
    }


    // Create new order and add members
    public function createOrder(Request $request)
    {
        Log::info('Request received:', $request->all());
        $request->validate([
            'id_gunung' => 'required|exists:mountains,id',
            'id_jalur' => 'required|exists:routes,id',
            'id_user' => 'required|exists:users,id',
            'tanggal_naik' => 'required|date',
            'tanggal_turun' => 'required|date',
            'total_harga_tiket' => 'required|numeric',
            'anggota_ids' => 'array',
            'anggota_ids.*' => 'exists:users,id',
        ]);

        try {
            // Create order
            $order = Order::create([
                'id_gunung' => $request->id_gunung,
                'id_jalur' => $request->id_jalur,
                'id_user' => $request->id_user,
                'tanggal_naik' => $request->tanggal_naik,
                'tanggal_turun' => $request->tanggal_turun,
                'total_harga_tiket' => $request->total_harga_tiket,
            ]);

            // Add members if any
            if (!empty($request->anggota_ids)) {
                $order->members()->attach($request->anggota_ids);
            }

            return response()->json([
                'message' => 'Order created successfully!',
                'order' => $order->load('members'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the order.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Add members to existing order
    public function addMembers(Request $request, $orderId)
    {
        $request->validate([
            'anggota_ids' => 'required|array',
            'anggota_ids.*' => 'exists:users,id',
        ]);

        try {
            $order = Order::findOrFail($orderId);

            // Add members to order
            $order->members()->attach($request->anggota_ids);

            return response()->json([
                'message' => 'Members added to order successfully.',
                'order' => $order,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while adding members.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // View order details
    public function viewOrder($orderId)
    {
        try {
            $order = Order::with('mountain:id,nama', 'trail:id,nama', 'booker:id,name', 'members')->findOrFail($orderId);

            return response()->json([
                'order' => $order,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Order not found.',
                'error' => $e->getMessage(),
            ], 404);
        }
    }
    
    public function getOrderDetail($orderId)
    {
        try {
            $order = Order::with(['booker:id,name', 'members', 'transaction'])
                ->findOrFail($orderId);

            if (!$order->transaction) {
                throw new \Exception('Transaction not found for this order.');
            }

            $orderDetail = [
                'id_pesanan' => $order->id,
                'tanggal_pesanan' => $order->tanggal_naik,
                'nama_pemesan' => $order->booker->name,
                'total_anggota' => $order->members->count() + 1, // +1 for the main booker
                'total_harga' => $order->transaction->total_bayar,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Successfully retrieved order detail',
                'data' => $orderDetail,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve order detail',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            // Find order by ID
            $order = Order::findOrFail($id);

            // Find related transaction based on id_pesanan
            $transaction = Transaction::where('id_pesanan', $order->id)->first();

            // Delete transaction if exists
            if ($transaction) {
                $transaction->delete();
            }

            // Delete order
            $order->delete();

            return response()->json([
                'message' => 'Order and related transaction deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while deleting the order.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
