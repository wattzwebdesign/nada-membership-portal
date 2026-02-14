<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Invoice {{ $invoice->number }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('warning'))
                <div class="mb-4 bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-md">
                    {{ session('warning') }}
                </div>
            @endif

            @if (session('info'))
                <div class="mb-4 bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-md">
                    {{ session('info') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 sm:p-8">
                    {{-- Header --}}
                    <div class="flex items-start justify-between mb-8">
                        <div>
                            <h3 class="text-2xl font-bold" style="color: #374269;">Invoice</h3>
                            <p class="text-sm text-gray-500 mt-1">{{ $invoice->number }}</p>
                        </div>
                        <div class="text-right">
                            @php
                                $statusColors = [
                                    'paid' => 'bg-green-100 text-green-800',
                                    'open' => 'bg-blue-100 text-blue-800',
                                    'draft' => 'bg-gray-100 text-gray-800',
                                    'uncollectible' => 'bg-red-100 text-red-800',
                                    'void' => 'bg-gray-100 text-gray-500',
                                ];
                                $statusColor = $statusColors[$invoice->status] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusColor }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                            <p class="text-sm text-gray-500 mt-2">{{ $invoice->created_at->format('F j, Y') }}</p>
                        </div>
                    </div>

                    {{-- Bill To --}}
                    <div class="mb-8">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Bill To</p>
                        <p class="text-sm font-medium text-gray-900">{{ $invoice->user->full_name }}</p>
                        <p class="text-sm text-gray-500">{{ $invoice->user->email }}</p>
                    </div>

                    {{-- Line Items --}}
                    <div class="border border-gray-200 rounded-lg overflow-hidden mb-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($invoice->items as $item)
                                    <tr>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            {{ $item->description }}
                                            @if ($item->plan)
                                                <span class="text-xs text-gray-400 ml-1">({{ $item->plan->name }})</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 text-center">{{ $item->quantity }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900 text-right">${{ number_format($item->unit_price, 2) }}</td>
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900 text-right">${{ number_format($item->total, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-sm text-gray-500 text-center">No line items</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Totals --}}
                    <div class="flex justify-end mb-8">
                        <div class="w-64">
                            <div class="flex justify-between py-2 border-b border-gray-200">
                                <span class="text-sm text-gray-500">Subtotal</span>
                                <span class="text-sm font-medium text-gray-900">${{ number_format($invoice->amount_due, 2) }}</span>
                            </div>
                            @if ($invoice->amount_paid > 0)
                                <div class="flex justify-between py-2 border-b border-gray-200">
                                    <span class="text-sm text-gray-500">Paid</span>
                                    <span class="text-sm font-medium text-green-600">-${{ number_format($invoice->amount_paid, 2) }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between py-3">
                                <span class="text-base font-semibold" style="color: #374269;">Amount Due</span>
                                <span class="text-base font-bold" style="color: #374269;">
                                    ${{ number_format(max(0, $invoice->amount_due - $invoice->amount_paid), 2) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                        <a href="{{ route('invoices.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
                            &larr; Back to Invoices
                        </a>

                        <div class="flex items-center space-x-3">
                            @if ($invoice->invoice_pdf_url)
                                <a href="{{ $invoice->invoice_pdf_url }}" target="_blank" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Download PDF
                                </a>
                            @endif

                            @if (in_array($invoice->status, ['open', 'draft']))
                                <form action="{{ route('invoices.pay', $invoice) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md text-white" style="background-color: #d39c27;">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                        Pay Now — ${{ number_format(max(0, $invoice->amount_due - $invoice->amount_paid), 2) }}
                                    </button>
                                </form>
                            @endif

                            @if ($invoice->status === 'paid')
                                <span class="inline-flex items-center text-sm font-medium text-green-600">
                                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Paid {{ $invoice->paid_at?->format('M j, Y') }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
