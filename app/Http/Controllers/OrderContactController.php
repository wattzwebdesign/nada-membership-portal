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

        $validated = $request->validate([
            'subject' => ['required', 'in:Shipping question,Item issue,Return / exchange,Other'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $order->load('vendorOrderSplits.vendorProfile.user');

        $vendors = $order->vendorOrderSplits
            ->pluck('vendorProfile.user')
            ->filter()
            ->unique('id');

        foreach ($vendors as $vendor) {
            $vendor->notify(new OrderContactNotification(
                $order,
                $request->user(),
                $validated['subject'],
                $validated['message'],
            ));
        }

        return redirect()->back()->with('success', 'Your message has been sent to the vendor(s).');
    }
}
