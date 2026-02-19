<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderHistoryController extends Controller
{
    public function index(Request $request)
    {
        $orders = $request->user()
            ->shopOrders()
            ->where('status', '!=', 'pending')
            ->with('items')
            ->latest()
            ->paginate(15);

        return view('orders.index', compact('orders'));
    }

    public function show(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $order->load(['items.product', 'items.vendorProfile', 'vendorOrderSplits.vendorProfile']);

        return view('orders.show', compact('order'));
    }
}
