<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Membership') }}
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

            {{-- Current Plan Details --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-6 text-brand-primary">Current Plan</h3>

                    @if ($subscription)
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div data-guide="membership-plan-name">
                                <p class="text-sm text-gray-500">Plan Name</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $subscription->plan->name ?? 'N/A' }}</p>
                            </div>

                            <div data-guide="membership-status">
                                <p class="text-sm text-gray-500">Status</p>
                                @php
                                    $statusColors = [
                                        'active' => 'bg-green-100 text-green-800',
                                        'past_due' => 'bg-yellow-100 text-yellow-800',
                                        'canceled' => 'bg-red-100 text-red-800',
                                        'trialing' => 'bg-blue-100 text-blue-800',
                                        'incomplete' => 'bg-gray-100 text-gray-800',
                                        'unpaid' => 'bg-red-100 text-red-800',
                                        'paused' => 'bg-gray-100 text-gray-800',
                                    ];
                                    $statusColor = $statusColors[$subscription->status->value ?? $subscription->status] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="mt-1 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusColor }}">
                                    {{ ucfirst(str_replace('_', ' ', $subscription->status->value ?? $subscription->status)) }}
                                </span>
                            </div>

                            <div data-guide="membership-renewal-date">
                                <p class="text-sm text-gray-500">Renewal Date</p>
                                <p class="text-lg font-semibold text-gray-900">
                                    {{ $subscription->current_period_end ? $subscription->current_period_end->format('F j, Y') : 'N/A' }}
                                </p>
                            </div>
                        </div>

                        @if ($subscription->cancel_at_period_end)
                            <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-md p-4">
                                <div class="flex">
                                    <svg class="h-5 w-5 text-yellow-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                                    <div>
                                        <p class="text-sm text-yellow-700 font-medium">Cancellation Scheduled</p>
                                        <p class="text-sm text-yellow-600 mt-1">Your membership will end on {{ $subscription->current_period_end->format('F j, Y') }}. You can reactivate before then to keep your membership.</p>
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('membership.reactivate') }}" class="mt-3">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md text-white bg-brand-primary">
                                        Reactivate Membership
                                    </button>
                                </form>
                            </div>
                        @endif

                        {{-- Plan Details --}}
                        <div class="mt-6 border-t border-gray-200 pt-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-500">Billing Interval</p>
                                    <p class="text-base text-gray-900">
                                        Every {{ $subscription->plan->billing_interval_count ?? 1 }} {{ ucfirst($subscription->plan->billing_interval ?? 'year') }}{{ ($subscription->plan->billing_interval_count ?? 1) > 1 ? 's' : '' }}
                                    </p>
                                </div>
                                <div data-guide="membership-price">
                                    <p class="text-sm text-gray-500">Price</p>
                                    <p class="text-base text-gray-900">${{ number_format(($subscription->plan->price_cents ?? 0) / 100, 2) }} / {{ $subscription->plan->billing_interval ?? 'year' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Current Period Start</p>
                                    <p class="text-base text-gray-900">
                                        {{ $subscription->current_period_start ? $subscription->current_period_start->format('F j, Y') : 'N/A' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Stripe Subscription ID</p>
                                    <p class="text-base text-gray-500 font-mono text-sm">{{ $subscription->stripe_subscription_id }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="mt-6 border-t border-gray-200 pt-6 flex flex-wrap gap-3">
                            <a href="{{ route('billing.index') }}" data-guide="membership-manage-billing" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-brand-primary">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                Manage Billing
                            </a>

                            <a href="{{ route('membership.plans') }}" data-guide="membership-change-plan" class="inline-flex items-center px-4 py-2 border text-sm font-medium rounded-md border-brand-primary text-brand-primary">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                Change Plan
                            </a>

                            <a href="{{ route('invoices.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-50">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                                Invoice History
                            </a>

                            @if (!$subscription->cancel_at_period_end && ($subscription->status->value ?? $subscription->status) === 'active')
                                <form method="POST" action="{{ route('membership.cancel') }}" onsubmit="return confirm('Are you sure you want to cancel your membership? Your access will continue until the end of the current billing period.');">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-red-300 text-sm font-medium rounded-md text-red-700 hover:bg-red-50">
                                        Cancel Membership
                                    </button>
                                </form>
                            @endif
                        </div>

                        {{-- Digital Membership Card --}}
                        @if (($subscription->status->value ?? $subscription->status) === 'active')
                            <div class="mt-6 border-t border-gray-200 pt-6">
                                <h4 class="text-sm font-semibold text-gray-900 mb-3">Digital Membership Card</h4>
                                <p class="text-sm text-gray-500 mb-4">Add your membership card to your phone's wallet. Your card updates automatically when your membership renews.</p>
                                <div class="flex flex-wrap items-center gap-3">
                                    <a href="{{ route('membership.wallet.apple') }}">
                                        <img src="{{ asset('images/add-to-apple-wallet.svg') }}" alt="Add to Apple Wallet" class="h-11">
                                    </a>
                                    <a href="{{ route('membership.wallet.google') }}">
                                        <img src="{{ asset('images/add-to-google-wallet.svg') }}" alt="Add to Google Wallet" class="h-11">
                                    </a>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z"/></svg>
                            <h3 class="mt-3 text-sm font-medium text-gray-900">No Active Membership</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by choosing a membership plan.</p>
                            <div class="mt-6">
                                <a href="{{ route('membership.plans') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-brand-primary">
                                    View Plans
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
    if (typeof umami !== 'undefined') {
        const params = new URLSearchParams(window.location.search);
        if (params.get('checkout') === 'success') {
            umami.track('Membership Purchase');
        }
    }
    </script>
    @endpush
</x-app-layout>
