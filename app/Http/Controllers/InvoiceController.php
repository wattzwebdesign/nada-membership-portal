<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    /**
     * List all invoices for the authenticated user.
     */
    public function index(Request $request): View
    {
        $invoices = $request->user()
            ->invoices()
            ->orderByDesc('paid_at')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('invoices.index', compact('invoices'));
    }

    /**
     * Redirect the user to the Stripe-hosted invoice PDF or show a download link.
     */
    public function download(Request $request, Invoice $invoice): RedirectResponse
    {
        // Ensure the invoice belongs to the authenticated user
        if ($invoice->user_id !== $request->user()->id) {
            abort(403, 'You are not authorized to download this invoice.');
        }

        if ($invoice->invoice_pdf_url) {
            return redirect($invoice->invoice_pdf_url);
        }

        if ($invoice->hosted_invoice_url) {
            return redirect($invoice->hosted_invoice_url);
        }

        // Fallback: fetch URLs directly from Stripe if not stored locally
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
                // Stripe lookup failed; fall through to error
            }
        }

        return back()->with('error', 'No downloadable invoice is available for this record.');
    }
}
