<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\StripeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(
        protected StripeService $stripeService,
    ) {}

    /**
     * List all invoices for the authenticated user.
     */
    public function index(Request $request): View
    {
        $invoices = $request->user()
            ->invoices()
            ->with('items')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('invoices.index', compact('invoices'));
    }

    /**
     * Show a single invoice with line items.
     */
    public function show(Request $request, Invoice $invoice): View
    {
        if ($invoice->user_id !== $request->user()->id) {
            abort(403);
        }

        $invoice->load('items.plan');

        return view('invoices.show', compact('invoice'));
    }

    /**
     * Pay an open invoice — redirect to Stripe hosted invoice page for subscription
     * invoices (paying reactivates the subscription), or fall back to Checkout for
     * manually-created invoices.
     */
    public function pay(Request $request, Invoice $invoice): RedirectResponse
    {
        if ($invoice->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($invoice->status === 'paid') {
            return back()->with('info', 'This invoice has already been paid.');
        }

        if (!in_array($invoice->status, ['open', 'draft'])) {
            return back()->with('error', 'This invoice cannot be paid.');
        }

        // For Stripe subscription invoices, redirect to the hosted invoice page.
        // When the member pays via Stripe's hosted page, Stripe reactivates the subscription automatically.
        if ($invoice->hosted_invoice_url) {
            return redirect($invoice->hosted_invoice_url);
        }

        // Try to fetch the hosted URL from Stripe if we have a Stripe invoice ID
        if ($invoice->stripe_invoice_id) {
            try {
                $stripeInvoice = \Stripe\Invoice::retrieve($invoice->stripe_invoice_id);
                if ($stripeInvoice->hosted_invoice_url) {
                    $invoice->update(['hosted_invoice_url' => $stripeInvoice->hosted_invoice_url]);
                    return redirect($stripeInvoice->hosted_invoice_url);
                }
            } catch (\Stripe\Exception\ApiErrorException $e) {
                // Fall through to Checkout flow
            }
        }

        // Fallback: create a Checkout Session for manually-created invoices
        $invoice->load('items');
        $user = $request->user();
        $customer = $this->stripeService->getOrCreateCustomer($user);

        $lineItems = $invoice->items->map(fn ($item) => [
            'price_data' => [
                'currency' => 'usd',
                'unit_amount' => (int) round($item->unit_price * 100),
                'product_data' => [
                    'name' => $item->description,
                ],
            ],
            'quantity' => $item->quantity,
        ])->toArray();

        $session = \Stripe\Checkout\Session::create([
            'customer' => $customer->id,
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => route('invoices.pay.success', $invoice) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('invoices.show', $invoice),
            'metadata' => [
                'invoice_id' => $invoice->id,
                'user_id' => $user->id,
            ],
        ]);

        return redirect($session->url);
    }

    /**
     * Handle successful payment callback from Stripe Checkout.
     */
    public function paySuccess(Request $request, Invoice $invoice): RedirectResponse
    {
        if ($invoice->user_id !== $request->user()->id) {
            abort(403);
        }

        $sessionId = $request->query('session_id');

        if ($sessionId) {
            try {
                $session = \Stripe\Checkout\Session::retrieve($sessionId);

                if ($session->payment_status === 'paid') {
                    $invoice->update([
                        'status' => 'paid',
                        'amount_paid' => $invoice->amount_due,
                        'paid_at' => now(),
                        'stripe_invoice_id' => $session->payment_intent,
                    ]);

                    return redirect()
                        ->route('invoices.show', $invoice)
                        ->with('success', 'Payment successful! Thank you.');
                }
            } catch (\Exception $e) {
                // Log but don't expose error to user
                \Illuminate\Support\Facades\Log::error('Invoice payment verification failed', [
                    'invoice_id' => $invoice->id,
                    'session_id' => $sessionId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('warning', 'Payment is being processed. Your invoice will be updated shortly.');
    }

    /**
     * Redirect the user to the Stripe-hosted invoice PDF or show a download link.
     */
    public function download(Request $request, Invoice $invoice): RedirectResponse
    {
        if ($invoice->user_id !== $request->user()->id) {
            abort(403, 'You are not authorized to download this invoice.');
        }

        if ($invoice->invoice_pdf_url) {
            return redirect($invoice->invoice_pdf_url);
        }

        if ($invoice->hosted_invoice_url) {
            return redirect($invoice->hosted_invoice_url);
        }

        if ($invoice->stripe_invoice_id) {
            try {
                $stripeInvoice = \Stripe\Invoice::retrieve($invoice->stripe_invoice_id);

                if ($stripeInvoice->invoice_pdf) {
                    $invoice->update(['invoice_pdf_url' => $stripeInvoice->invoice_pdf]);
                    return redirect($stripeInvoice->invoice_pdf);
                }

                if ($stripeInvoice->hosted_invoice_url) {
                    $invoice->update(['hosted_invoice_url' => $stripeInvoice->hosted_invoice_url]);
                    return redirect($stripeInvoice->hosted_invoice_url);
                }
            } catch (\Stripe\Exception\ApiErrorException $e) {
                // Stripe lookup failed
            }
        }

        return back()->with('error', 'No downloadable invoice is available for this record.');
    }
}
