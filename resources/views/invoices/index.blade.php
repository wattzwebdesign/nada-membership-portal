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
                    <h3 class="text-lg font-semibold mb-6" style="color: #374269;">Your Invoices</h3>

                    @if ($invoices->isEmpty())
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                            <h3 class="mt-3 text-sm font-medium text-gray-900">No invoices yet</h3>
                            <p class="mt-1 text-sm text-gray-500">Invoices will appear here once you have an active subscription.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($invoices as $invoice)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-500">
                                                {{ $invoice->stripe_invoice_id ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $invoice->paid_at ? $invoice->paid_at->format('M d, Y') : ($invoice->created_at ? $invoice->created_at->format('M d, Y') : 'N/A') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                ${{ number_format(($invoice->amount_cents ?? 0) / 100, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $statusColors = [
                                                        'paid' => 'bg-green-100 text-green-800',
                                                        'open' => 'bg-blue-100 text-blue-800',
                                                        'void' => 'bg-gray-100 text-gray-800',
                                                        'uncollectible' => 'bg-red-100 text-red-800',
                                                        'draft' => 'bg-gray-100 text-gray-800',
                                                    ];
                                                    $color = $statusColors[$invoice->status ?? ''] ?? 'bg-gray-100 text-gray-800';
                                                @endphp
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $color }}">
                                                    {{ ucfirst($invoice->status ?? 'Unknown') }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                @if ($invoice->hosted_invoice_url)
                                                    <a href="{{ $invoice->hosted_invoice_url }}" target="_blank" rel="noopener" class="font-medium hover:underline" style="color: #374269;">
                                                        View Invoice
                                                    </a>
                                                @elseif ($invoice->invoice_pdf_url)
                                                    <a href="{{ $invoice->invoice_pdf_url }}" target="_blank" rel="noopener" class="font-medium hover:underline" style="color: #374269;">
                                                        Download PDF
                                                    </a>
                                                @elseif ($invoice->stripe_invoice_id)
                                                    <a href="{{ route('invoices.download', $invoice) }}" target="_blank" rel="noopener" class="font-medium hover:underline" style="color: #374269;">
                                                        Download
                                                    </a>
                                                @else
                                                    <span class="text-gray-400">N/A</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6">
                            {{ $invoices->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
