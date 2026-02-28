<?php

namespace App\Http\Controllers;


use App\Models\OrderWeb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HistoryController extends Controller
{
    // Display list of order history
    public function index(Request $request)
    {
        $query = OrderWeb::with(['user:id,name', 'orderMembers.user'])
            ->select('id', 'tanggal_naik', 'tanggal_turun', 'status', 'id_user');

        // Filter by order ID
        if ($request->filled('search')) {
            $query->where('id', $request->input('search'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $orders = $query->get();

        return view('history.index', ['orders' => $orders]);
    }


    // Display order details and members
    public function show($id)
    {
        $order = OrderWeb::with(['mountain:id,nama', 'trail:id,nama', 'orderMembers.user', 'user:id,name'])
            ->findOrFail($id);

        return view('history.show', compact('order'));
    }


    // Update order status
    public function updateStatus(Request $request, $id)
    {
        $order = OrderWeb::findOrFail($id);
        $order->status = $request->input('status');
        $order->save();

        return redirect()->route('history.show', $id)->with('success', 'Order status updated successfully!');
    }

    // Scanner for update order status
    public function scan(Request $request)
    {
        Log::info('Scan request received', $request->all());

        $id = $request->input('id');
        $order = OrderWeb::find($id);

        if (!$order) {
            Log::error("Order with ID {$id} not found");
            return response()->json(['success' => false, 'message' => 'Order not found']);
        }

        Log::info("Order found with status {$order->status}");

        switch ($order->status) {
            case 'Booking':
                $order->status = 'Sedang Mendaki';
                break;
            case 'Sedang Mendaki':
                $order->status = 'Selesai';
                break;
            default:
                Log::warning("Status {$order->status} not recognized");
                return response()->json(['success' => false, 'message' => 'Status cannot be updated further']);
        }

        $order->save();
        Log::info("Order status successfully updated to {$order->status}");

        return response()->json(['success' => true]);
    }
}
