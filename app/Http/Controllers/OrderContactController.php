<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Notifications\OrderContactNotification;
use Illuminate\Http\Request;

class OrderContactController extends Controller
{
    public function store(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $order->load('vendorOrderSplits.vendorProfile.user');

        $vendorProfileIds = $order->vendorOrderSplits
            ->pluck('vendor_profile_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $rules = [
            'subject' => ['required', 'in:Shipping question,Item issue,Return / exchange,Other'],
            'message' => ['required', 'string', 'max:2000'],
        ];

        // Require vendor selection when order has multiple vendors
        if (count($vendorProfileIds) > 1) {
            $rules['vendor_profile_id'] = ['required', 'in:' . implode(',', $vendorProfileIds)];
        }

        $validated = $request->validate($rules);

        // Determine which vendor(s) to notify
        if (count($vendorProfileIds) === 1) {
            // Single vendor — send to them directly
            $splits = $order->vendorOrderSplits;
        } else {
            // Multi-vendor — send only to the selected vendor
            $splits = $order->vendorOrderSplits
                ->where('vendor_profile_id', (int) $validated['vendor_profile_id']);
        }

        $vendor = $splits->first()?->vendorProfile?->user;

        if ($vendor) {
            $vendor->notify(new OrderContactNotification(
                $order,
                $request->user(),
                $validated['subject'],
                $validated['message'],
            ));
        }

        return redirect()->back()->with('success', 'Your message has been sent to the vendor.');
    }
}
