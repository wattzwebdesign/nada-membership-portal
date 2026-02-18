<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Billing & Payment Method') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Current Payment Method --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 text-brand-primary">Current Payment Method</h3>

                    @if (isset($paymentMethod) && $paymentMethod)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    @php
                                        $brandIcons = [
                                            'visa' => 'V',
                                            'mastercard' => 'MC',
                                            'amex' => 'AX',
                                            'discover' => 'D',
                                        ];
                                        $brand = $paymentMethod->card->brand ?? 'card';
                                    @endphp
                                    <div class="w-12 h-8 rounded flex items-center justify-center text-white text-xs font-bold bg-brand-primary">
                                        {{ $brandIcons[$brand] ?? strtoupper(substr($brand, 0, 2)) }}
                                    </div>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ ucfirst($paymentMethod->card->brand ?? 'Card') }} ending in {{ $paymentMethod->card->last4 ?? '****' }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        Expires {{ $paymentMethod->card->exp_month ?? '--' }}/{{ $paymentMethod->card->exp_year ?? '----' }}
                                    </p>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('billing.remove-payment-method') }}" onsubmit="return confirm('Are you sure you want to remove your card? Auto-payments will be stopped and you will need to add a card before your next renewal.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-red-300 text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Remove Card
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div class="flex">
                                <svg class="w-5 h-5 text-yellow-600 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                                <div>
                                    <p class="text-sm font-medium text-yellow-800">No payment method on file</p>
                                    <p class="text-sm text-yellow-700 mt-1">Auto-payments are stopped. Add a card below before your next renewal to keep your membership active.</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Update Payment Method --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 text-brand-primary">Update Payment Method</h3>
                    <p class="text-sm text-gray-500 mb-6">Enter your new card details below. Your current payment method will be replaced.</p>

                    <form method="POST" action="{{ route('billing.update-payment-method') }}" id="payment-form">
                        @csrf

                        {{-- Stripe Elements will mount here --}}
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Card Details</label>
                            <div id="card-element" class="p-3 border border-gray-300 rounded-md bg-white">
                                {{-- Stripe Card Element will be injected here via JavaScript --}}
                                <p class="text-sm text-gray-400">Loading payment form...</p>
                            </div>
                            <div id="card-errors" class="mt-2 text-sm text-red-600" role="alert"></div>
                        </div>

                        <input type="hidden" name="payment_method_id" id="payment-method-input">

                        <div class="flex items-center justify-between">
                            <button type="submit" id="submit-button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-brand-primary">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                Update Payment Method
                            </button>
                            <a href="{{ route('membership.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
                                Back to Membership
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Quick Links --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 text-brand-primary">Billing Links</h3>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('invoices.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-50">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                            View Invoices
                        </a>
                        <a href="{{ route('membership.plans') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-50">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                            Change Plan
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const stripe = Stripe('{{ config("services.stripe.key") }}');
            const elements = stripe.elements();
            const cardElement = elements.create('card', {
                style: {
                    base: {
                        fontSize: '16px',
                        color: '#1C3519',
                        '::placeholder': { color: '#9ca3af' },
                    },
                },
            });

            const cardContainer = document.getElementById('card-element');
            cardContainer.innerHTML = '';
            cardElement.mount('#card-element');

            cardElement.on('change', function(event) {
                const displayError = document.getElementById('card-errors');
                displayError.textContent = event.error ? event.error.message : '';
            });

            const form = document.getElementById('payment-form');
            form.addEventListener('submit', async function(event) {
                event.preventDefault();
                const submitButton = document.getElementById('submit-button');
                submitButton.disabled = true;
                submitButton.textContent = 'Processing...';

                const { paymentMethod, error } = await stripe.createPaymentMethod({
                    type: 'card',
                    card: cardElement,
                });

                if (error) {
                    document.getElementById('card-errors').textContent = error.message;
                    submitButton.disabled = false;
                    submitButton.textContent = 'Update Payment Method';
                } else {
                    document.getElementById('payment-method-input').value = paymentMethod.id;
                    form.submit();
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
