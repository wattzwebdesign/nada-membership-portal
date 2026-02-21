<?php

namespace App\Http\Controllers;

use App\Models\CheckoutFieldConfig;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Notifications\Concerns\SafelyNotifies;
use App\Notifications\NewStoreOrderNotification;
use App\Notifications\StoreOrderConfirmationNotification;
use App\Services\CartService;
use App\Services\StoreCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Stripe;

class ShopCheckoutController extends Controller
{
    use SafelyNotifies;

    public function __construct(
        protected CartService $cartService,
        protected StoreCheckoutService $checkoutService,
    ) {}

    public function index(Request $request): View
    {
        $items = $this->cartService->getItems();

        if (empty($items)) {
            return redirect()->route('shop.cart.index')
                ->with('error', 'Your cart is empty.');
        }

        $user = $request->user();
        $subtotal = $this->cartService->getSubtotalCentsForUser($user);
        $shipping = $this->cartService->getShippingCents();
        $total = $subtotal + $shipping;

        $checkoutFields = CheckoutFieldConfig::getVisibleFields()
            ->groupBy('section');

        $allDigital = $this->cartService->hasOnlyDigitalItems();

        // Map checkout field names to user model attributes for autofill
        $autofill = [];
        if ($user) {
            $autofill = [
                'customer_first_name' => $user->first_name,
                'customer_last_name' => $user->last_name,
                'customer_email' => $user->email,
                'customer_phone' => $user->phone,
                'billing_address_line_1' => $user->address_line_1,
                'billing_address_line_2' => $user->address_line_2,
                'billing_city' => $user->city,
                'billing_state' => $user->state,
                'billing_zip' => $user->zip,
                'billing_country' => $user->country,
                'shipping_address_line_1' => $user->address_line_1,
                'shipping_address_line_2' => $user->address_line_2,
                'shipping_city' => $user->city,
                'shipping_state' => $user->state,
                'shipping_zip' => $user->zip,
                'shipping_country' => $user->country,
            ];
        }

        return view('public.shop.checkout', [
            'cart' => $items,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $total,
            'checkoutFields' => $checkoutFields,
            'allDigital' => $allDigital,
            'user' => $user,
            'autofill' => $autofill,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $items = $this->cartService->getItems();

        if (empty($items)) {
            return redirect()->route('shop.cart.index')
                ->with('error', 'Your cart is empty.');
        }

        // Validate stock
        $stockErrors = $this->cartService->validateStock();
        if (! empty($stockErrors)) {
            return redirect()->route('shop.cart.index')
                ->with('error', 'Some items in your cart are no longer available. Please review your cart.');
        }

        // Get validation rules from checkout field configs
        $rules = CheckoutFieldConfig::getValidationRules();
        $validated = $request->validate($rules);

        $user = $request->user();
        $subtotal = $this->cartService->getSubtotalCentsForUser($user);
        $shipping = $this->cartService->getShippingCents();
        $total = $subtotal + $shipping;

        try {
            // Create order
            $order = Order::create(array_merge($validated, [
                'user_id' => $user?->id,
                'subtotal_cents' => $subtotal,
                'shipping_cents' => $shipping,
                'tax_cents' => 0,
                'total_cents' => $total,
                'currency' => 'usd',
                'status' => 'pending',
                'download_token' => bin2hex(random_bytes(32)),
            ]));

            // Create order items
            foreach ($items as $item) {
                $product = Product::find($item['product_id']);
                $effectivePrice = $product ? $product->getEffectivePrice($user) : $item['price_cents'];
                $wasMemberPrice = $user && $item['member_price_cents'] && $user->hasFullMembership() && $effectivePrice === $item['member_price_cents'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'vendor_profile_id' => $item['vendor_profile_id'],
                    'product_title' => $item['title'],
                    'product_sku' => $product?->sku,
                    'unit_price_cents' => $effectivePrice,
                    'quantity' => $item['quantity'],
                    'total_cents' => $effectivePrice * $item['quantity'],
                    'shipping_fee_cents' => $item['is_digital'] ? 0 : $item['shipping_fee_cents'] * $item['quantity'],
                    'was_member_price' => $wasMemberPrice,
                    'is_digital' => $item['is_digital'],
                ]);
            }

            // Reload items relationship so Stripe session and splits use fresh data
            $order->load('items.vendorProfile.user');

            // Calculate vendor splits
            $this->checkoutService->calculateVendorSplits($order);

            // Create Stripe Checkout session
            $session = $this->checkoutService->createCheckoutSession($order);
            $order->update(['stripe_checkout_session_id' => $session->id]);

            return redirect($session->url);
        } catch (\Exception $e) {
            Log::error('Store checkout failed', [
                'order_id' => $order->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if (isset($order)) {
                $order->delete();
            }

            return redirect()->route('shop.checkout.index')
                ->with('error', 'Unable to process payment. Please try again or contact support.');
        }
    }

    public function success(Request $request): View|RedirectResponse
    {
        $sessionId = $request->query('session_id');

        if (! $sessionId) {
            return redirect()->route('public.shop.index');
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $session = CheckoutSession::retrieve($sessionId);

            if ($session->payment_status !== 'paid') {
                return redirect()->route('shop.cart.index')
                    ->with('error', 'Payment was not completed.');
            }

            $order = Order::where('stripe_checkout_session_id', $sessionId)->first();

            if (! $order) {
                return redirect()->route('public.shop.index')
                    ->with('error', 'Order not found.');
            }

            // Process if not already processed
            if ($order->status->value === 'pending') {
                $this->checkoutService->processPayment($order, $session->payment_intent);
            }

            // Clear cart
            $this->cartService->clear();

            return view('public.shop.confirmation', compact('order'));
        } catch (\Exception $e) {
            Log::error('Store checkout success verification failed', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('public.shop.index')
                ->with('error', 'Unable to verify payment. Please contact support.');
        }
    }

    public function cancel(): View
    {
        return view('public.shop.cancel');
    }

    public function download(Request $request, Order $order, OrderItem $orderItem): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = $request->user();

        if ($order->user_id) {
            // Authenticated order: require the owning user
            if (! $user || $user->id !== $order->user_id) {
                abort(403);
            }
        } else {
            // Guest order: require a valid download token
            if (! $order->download_token || $request->query('token') !== $order->download_token) {
                abort(403);
            }
        }

        if ($orderItem->order_id !== $order->id) {
            abort(404);
        }

        if (! $orderItem->is_digital || $order->status->value === 'pending') {
            abort(404);
        }

        $product = $orderItem->product;
        if (! $product) {
            abort(404, 'Product no longer available for download.');
        }

        $media = $product->getFirstMedia('digital_file');
        if (! $media) {
            abort(404, 'Digital file not found.');
        }

        return response()->streamDownload(function () use ($media) {
            echo file_get_contents($media->getPath());
        }, $media->file_name, [
            'Content-Type' => $media->mime_type,
        ]);
    }
}
