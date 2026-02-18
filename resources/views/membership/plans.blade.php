<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Membership Plans') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
                    {{ session('error') }}
                </div>
            @endif

            <div class="text-center mb-8">
                <h3 class="text-2xl font-bold text-brand-primary">Choose Your Plan</h3>
                <p class="mt-2 text-gray-600">Select the membership plan that best fits your needs.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse ($plans as $plan)
                    <div class="relative bg-white overflow-hidden shadow-sm sm:rounded-lg border-2 {{ $currentPlan && $currentPlan->id === $plan->id ? 'border-amber-400' : ($plan->discount_required ? 'border-brand-primary' : 'border-transparent hover:border-gray-200') }} transition-colors">
                        {{-- Discount ribbon --}}
                        @if ($plan->discount_required)
                            <div style="position:absolute;top:0;right:0;width:130px;height:130px;overflow:hidden;pointer-events:none;">
                                <div class="bg-brand-primary" style="position:absolute;top:20px;right:-36px;width:170px;text-align:center;transform:rotate(45deg);padding:6px 0;box-shadow:0 1px 3px rgba(0,0,0,.15);">
                                    <span style="color:#fff;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;">{{ ucfirst($plan->discount_required) }}</span>
                                </div>
                            </div>
                        @endif

                        <div class="p-6">
                            @if ($currentPlan && $currentPlan->id === $plan->id)
                                <div class="mb-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-brand-secondary text-white">
                                        Current Plan
                                    </span>
                                </div>
                            @endif

                            <h4 class="text-xl font-bold text-gray-900">{{ $plan->name }}</h4>

                            @if ($plan->description)
                                <p class="mt-2 text-sm text-gray-500">{{ $plan->description }}</p>
                            @endif

                            <div class="mt-4">
                                <span class="text-4xl font-extrabold text-brand-primary">${{ number_format($plan->price_cents / 100, 2) }}</span>
                                <span class="text-base text-gray-500">
                                    / {{ $plan->billing_interval_count > 1 ? $plan->billing_interval_count . ' ' : '' }}{{ $plan->billing_interval }}{{ $plan->billing_interval_count > 1 ? 's' : '' }}
                                </span>
                            </div>

                            <ul class="mt-6 space-y-3">
                                <li class="flex items-center text-sm text-gray-600">
                                    <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    NADA Membership Access
                                </li>
                                <li class="flex items-center text-sm text-gray-600">
                                    <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Digital Certificate
                                </li>
                                <li class="flex items-center text-sm text-gray-600">
                                    <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Training Registration
                                </li>
                                @if ($plan->plan_type === 'trainer')
                                    <li class="flex items-center text-sm text-gray-600">
                                        <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Host Trainings
                                    </li>
                                    <li class="flex items-center text-sm text-gray-600">
                                        <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Stripe Connect Payouts
                                    </li>
                                @endif
                                @if ($plan->discount_required)
                                    <li class="flex items-center text-sm font-medium text-brand-secondary">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                                        {{ ucfirst($plan->discount_required) }} Discount Applied
                                    </li>
                                @endif
                            </ul>

                            <div class="mt-6">
                                @if ($currentPlan && $currentPlan->id === $plan->id)
                                    <button disabled class="w-full inline-flex justify-center items-center px-4 py-3 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-gray-50 cursor-not-allowed">
                                        Current Plan
                                    </button>
                                @elseif ($currentPlan)
                                    <form method="POST" action="{{ route('membership.switch-plan') }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                        <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-3 border border-transparent text-sm font-medium rounded-md text-white transition-colors bg-brand-primary hover:bg-brand-primary-hover">
                                            Switch to This Plan
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('membership.subscribe') }}">
                                        @csrf
                                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                        <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-3 border border-transparent text-sm font-medium rounded-md text-white transition-colors bg-brand-secondary hover:opacity-90">
                                            Subscribe
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-12">
                        <p class="text-gray-500">No plans are currently available.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
