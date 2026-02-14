<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Invoice History') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold" style="color: #374269;">Invoices</h3>
                        <a href="{{ route('membership.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Back to Membership</a>
                    </div>

                    @if ($invoices->count() > 0)
                        {{-- Desktop Table --}}
                        <div class="hidden md:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice Number</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($invoices as $invoice)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $invoice->created_at->format('M j, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">
                                                {{ $invoice->number ?? $invoice->stripe_invoice_id }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                ${{ number_format($invoice->amount_due_cents / 100, 2) }} {{ strtoupper($invoice->currency) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $invoiceStatusColors = [
                                                        'paid' => 'bg-green-100 text-green-800',
                                                        'open' => 'bg-blue-100 text-blue-800',
                                                        'draft' => 'bg-gray-100 text-gray-800',
                                                        'uncollectible' => 'bg-red-100 text-red-800',
                                                        'void' => 'bg-gray-100 text-gray-500',
                                                    ];
                                                    $invoiceColor = $invoiceStatusColors[$invoice->status] ?? 'bg-gray-100 text-gray-800';
                                                @endphp
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $invoiceColor }}">
                                                    {{ ucfirst($invoice->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                                <div class="flex justify-end space-x-2">
                                                    @if ($invoice->invoice_pdf_url)
                                                        <a href="{{ $invoice->invoice_pdf_url }}" target="_blank" class="inline-flex items-center text-sm font-medium hover:underline" style="color: #374269;">
                                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                            Download
                                                        </a>
                                                    @endif
                                                    @if ($invoice->hosted_invoice_url)
                                                        <a href="{{ $invoice->hosted_invoice_url }}" target="_blank" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
                                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                            View
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Mobile Cards --}}
                        <div class="md:hidden space-y-3">
                            @foreach ($invoices as $invoice)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-900">${{ number_format($invoice->amount_due_cents / 100, 2) }}</span>
                                        @php
                                            $invoiceColor = $invoiceStatusColors[$invoice->status] ?? 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $invoiceColor }}">
                                            {{ ucfirst($invoice->status) }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500">{{ $invoice->number ?? $invoice->stripe_invoice_id }}</p>
                                    <p class="text-xs text-gray-400">{{ $invoice->created_at->format('M j, Y') }}</p>
                                    @if ($invoice->invoice_pdf_url)
                                        <a href="{{ $invoice->invoice_pdf_url }}" target="_blank" class="mt-2 inline-flex items-center text-xs font-medium" style="color: #374269;">
                                            Download PDF
                                        </a>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        {{-- Pagination --}}
                        @if ($invoices->hasPages())
                            <div class="mt-6">
                                {{ $invoices->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                            <h3 class="mt-3 text-sm font-medium text-gray-900">No Invoices</h3>
                            <p class="mt-1 text-sm text-gray-500">Your invoice history will appear here once you have an active subscription.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
