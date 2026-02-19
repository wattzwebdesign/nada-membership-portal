<x-public-layout>
    <x-slot name="title">Membership Pricing - NADA</x-slot>

    {{-- Hero Section --}}
    <div class="py-16 bg-brand-primary">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl sm:text-4xl font-bold text-white">Membership Plans</h1>
            <p class="mt-4 text-lg text-gray-300 max-w-2xl mx-auto">Join the National Acupuncture Detoxification Association and become a certified Acupuncture Detox Specialist.</p>
        </div>
    </div>

    {{-- Plans Grid --}}
    <div class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if (session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse ($plans as $plan)
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100 hover:shadow-xl transition-shadow relative">
                        @if ($plan->plan_type === 'trainer')
                            <div class="absolute top-0 right-0 px-3 py-1 text-xs font-bold text-white rounded-bl-lg bg-brand-secondary">
                                Trainer
                            </div>
                        @endif

                        <div class="p-8">
                            <h3 class="text-xl font-bold text-gray-900">{{ $plan->name }}</h3>

                            @if ($plan->description)
                                <p class="mt-2 text-sm text-gray-500">{{ $plan->description }}</p>
                            @endif

                            <div class="mt-6">
                                <span class="text-5xl font-extrabold text-brand-primary">${{ number_format($plan->price_cents / 100, 0) }}</span>
                                @if (($plan->price_cents % 100) !== 0)
                                    <span class="text-2xl font-bold text-brand-primary">.{{ str_pad($plan->price_cents % 100, 2, '0', STR_PAD_LEFT) }}</span>
                                @endif
                                <span class="text-base text-gray-500 ml-1">
                                    / {{ $plan->billing_interval_count > 1 ? $plan->billing_interval_count . ' ' : '' }}{{ $plan->billing_interval }}{{ $plan->billing_interval_count > 1 ? 's' : '' }}
                                </span>
                            </div>

                            <ul class="mt-8 space-y-4">
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 mr-3 mt-0.5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span class="text-sm text-gray-600">Full NADA membership access</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 mr-3 mt-0.5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span class="text-sm text-gray-600">Digital certificate with unique NADA ID</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 mr-3 mt-0.5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span class="text-sm text-gray-600">Training registration access</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 mr-3 mt-0.5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span class="text-sm text-gray-600">Public certificate verification</span>
                                </li>
                                @if ($plan->plan_type === 'trainer')
                                    <li class="flex items-start">
                                        <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0 text-brand-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span class="text-sm font-medium text-brand-primary">Host your own trainings</span>
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0 text-brand-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span class="text-sm font-medium text-brand-primary">Stripe Connect payouts</span>
                                    </li>
                                @endif
                                @if ($plan->discount_required)
                                    <li class="flex items-start">
                                        <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0 text-brand-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                                        <span class="text-sm font-medium text-brand-secondary">{{ ucfirst($plan->discount_required) }} discount rate</span>
                                    </li>
                                @endif
                            </ul>

                            <div class="mt-8">
                                <a href="{{ auth()->check() ? route('membership.plans') : route('register', ['plan' => $plan->id]) }}" class="w-full inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white transition-colors {{ $plan->plan_type === 'trainer' ? 'bg-brand-secondary hover:bg-brand-secondary-hover' : 'bg-brand-primary hover:bg-brand-primary-hover' }}">
                                    {{ auth()->check() ? 'View Plans' : 'Get Started' }}
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-12">
                        <p class="text-gray-500 text-lg">Plans are currently being updated. Please check back soon.</p>
                    </div>
                @endforelse
            </div>

            {{-- Discount Info --}}
            <div class="mt-16 bg-gray-50 rounded-xl p-8">
                <div class="text-center max-w-2xl mx-auto">
                    <h3 class="text-xl font-bold text-brand-primary">Student & Senior Discounts Available</h3>
                    <p class="mt-3 text-gray-600">NADA offers discounted membership rates for currently enrolled students and seniors. After creating your account, you can request a discount from your account settings.</p>
                    @auth
                        <a href="{{ route('discount.request.create') }}" class="mt-4 inline-flex items-center px-4 py-2 border text-sm font-medium rounded-md border-brand-primary text-brand-primary">
                            Request a Discount
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="mt-4 inline-flex items-center px-4 py-2 border text-sm font-medium rounded-md border-brand-primary text-brand-primary">
                            Create Account to Request Discount
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</x-public-layout>
