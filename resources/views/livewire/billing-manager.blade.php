<div>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="rounded-md bg-green-50 p-4">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-md bg-red-50 p-4">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            @endif

            {{-- Current Plan --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-xl font-bold mb-4 text-brand-primary">Current Plan</h2>

                    @if ($subscription)
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ $subscription->plan->name ?? 'Membership' }}
                                </h3>
                                <p class="text-sm text-gray-600 mt-1">
                                    {{ $subscription->plan->price_formatted ?? '' }} {{ $subscription->plan->billing_label ?? '' }}
                                </p>
                                <div class="mt-3 space-y-1 text-sm text-gray-500">
                                    <p>Status:
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                            {{ $subscription->isActive() ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $subscription->status->label() }}
                                        </span>
                                    </p>
                                    @if ($subscription->current_period_end)
                                        <p>
                                            @if ($subscription->cancel_at_period_end)
                                                Access until: {{ $subscription->current_period_end->format('M d, Y') }}
                                            @else
                                                Next billing date: {{ $subscription->current_period_end->format('M d, Y') }}
                                            @endif
                                        </p>
                                    @endif
                                </div>

                                @if ($subscription->cancel_at_period_end)
                                    <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                                        <p class="text-sm text-yellow-800">
                                            Your subscription is set to cancel at the end of your current billing period.
                                        </p>
                                    </div>
                                @endif
                            </div>

                            <div>
                                @if ($subscription->cancel_at_period_end)
                                    <button wire:click="reactivateSubscription"
                                            wire:confirm="Reactivate your subscription? You will continue to be billed."
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center px-4 py-2 rounded-md text-white text-sm font-medium shadow-sm hover:opacity-90 transition-opacity bg-brand-primary">
                                        <span wire:loading.remove wire:target="reactivateSubscription">Reactivate</span>
                                        <span wire:loading wire:target="reactivateSubscription">Processing...</span>
                                    </button>
                                @elseif ($subscription->isActive())
                                    <button wire:click="cancelSubscription"
                                            wire:confirm="Cancel your subscription? You will retain access until the end of the current billing period."
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium border border-red-300 text-red-700 bg-white hover:bg-red-50 transition-colors">
                                        <span wire:loading.remove wire:target="cancelSubscription">Cancel Subscription</span>
                                        <span wire:loading wire:target="cancelSubscription">Processing...</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="text-center py-6">
                            <p class="text-gray-500 mb-4">You don't have an active subscription.</p>
                            <a href="{{ route('membership.index') }}"
                               class="inline-flex items-center px-4 py-2 rounded-md text-white text-sm font-medium shadow-sm hover:opacity-90 transition-opacity bg-brand-secondary">
                                View Plans
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Payment Method --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-xl font-bold mb-4 text-brand-primary" data-guide="billing-update-payment">Payment Method</h2>

                    @if ($cardLast4)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 w-12 h-8 bg-gray-100 rounded flex items-center justify-center">
                                    <svg class="w-8 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-900">{{ $cardBrand }} ending in {{ $cardLast4 }}</p>
                                    <p class="text-xs text-gray-500">Expires {{ $cardExpiry }}</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-gray-500">No payment method on file.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
