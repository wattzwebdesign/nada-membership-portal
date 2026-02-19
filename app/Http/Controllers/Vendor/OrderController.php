<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\VendorOrderSplit;
use App\Notifications\Concerns\SafelyNotifies;
use App\Notifications\StoreOrderShippedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class OrderController extends Controller
{
    use SafelyNotifies;

    public function index(Request $request): View
    {
        $vendorProfile = $request->user()->vendorProfile;

        if (! $vendorProfile) {
            return view('store-vendor.orders.index', ['orders' => collect()]);
        }

        $orders = $vendorProfile->vendorOrderSplits()
            ->with(['order', 'order.items' => fn ($q) => $q->where('vendor_profile_id', $vendorProfile->id)])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('store-vendor.orders.index', compact('orders'));
    }

    public function show(Request $request, Order $order): View
    {
        $vendorProfile = $request->user()->vendorProfile;

        $split = VendorOrderSplit::where('order_id', $order->id)
            ->where('vendor_profile_id', $vendorProfile->id)
            ->firstOrFail();

        $items = $order->items()->where('vendor_profile_id', $vendorProfile->id)->get();

        return view('store-vendor.orders.show', compact('order', 'split', 'items'));
    }

    public function markShipped(Request $request, Order $order): RedirectResponse
    {
        $vendorProfile = $request->user()->vendorProfile;

        $split = VendorOrderSplit::where('order_id', $order->id)
            ->where('vendor_profile_id', $vendorProfile->id)
            ->firstOrFail();

        $validated = $request->validate([
            'tracking_number' => ['nullable', 'string', 'max:255'],
        ]);

        $split->update([
            'shipped_at' => now(),
            'tracking_number' => $validated['tracking_number'] ?? null,
        ]);

        // Update order status if all splits are shipped
        $allShipped = $order->vendorOrderSplits()->whereNull('shipped_at')->whereNull('canceled_at')->count() === 0;
        if ($allShipped) {
            $order->update(['status' => OrderStatus::Shipped]);
        } else {
            $order->update(['status' => OrderStatus::Processing]);
        }

        // Notify customer
        if ($order->customer_email) {
            Notification::route('mail', $order->customer_email)
                ->notify(new StoreOrderShippedNotification($order, $split));
        }

        return redirect()->route('vendor.orders.show', $order)
            ->with('success', 'Order marked as shipped.');
    }

    public function markDelivered(Request $request, Order $order): RedirectResponse
    {
        $vendorProfile = $request->user()->vendorProfile;

        $split = VendorOrderSplit::where('order_id', $order->id)
            ->where('vendor_profile_id', $vendorProfile->id)
            ->firstOrFail();

        $split->update(['delivered_at' => now()]);

        // Update order status if all splits are delivered
        $allDelivered = $order->vendorOrderSplits()->whereNull('delivered_at')->whereNull('canceled_at')->count() === 0;
        if ($allDelivered) {
            $order->update(['status' => OrderStatus::Delivered]);
        }

        return redirect()->route('vendor.orders.show', $order)
            ->with('success', 'Order marked as delivered.');
    }
}
