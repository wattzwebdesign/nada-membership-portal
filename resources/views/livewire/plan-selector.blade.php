<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <h2 class="text-3xl font-bold text-brand-primary">Choose Your Plan</h2>
                <p class="mt-2 text-gray-600">Select the membership plan that best fits your needs.</p>
            </div>

            @if ($plans->isEmpty())
                <div class="bg-white shadow-sm sm:rounded-lg p-12 text-center">
                    <p class="text-gray-500">No plans are currently available. Please check back later.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach ($plans as $plan)
                        @php
                            $isCurrent = $currentPlan && $currentPlan->id === $plan->id;
                        @endphp
                        <div class="bg-white rounded-lg shadow-sm overflow-hidden flex flex-col {{ $isCurrent ? 'ring-2 ring-brand-secondary border-brand-secondary' : 'border border-gray-200' }}">
                            {{-- Plan Header --}}
                            @if ($isCurrent)
                                <div class="px-6 py-2 text-center text-xs font-bold text-white uppercase tracking-wider bg-brand-secondary">
                                    Current Plan
                                </div>
                            @endif

                            <div class="p-6 flex-1">
                                <h3 class="text-xl font-bold text-gray-900">{{ $plan->name }}</h3>

                                <div class="mt-4 flex items-baseline">
                                    <span class="text-4xl font-extrabold text-brand-primary">{{ $plan->price_formatted }}</span>
                                    <span class="ml-1 text-sm text-gray-500">{{ $plan->billing_label }}</span>
                                </div>

                                @if ($plan->description)
                                    <p class="mt-4 text-sm text-gray-600">{{ $plan->description }}</p>
                                @endif

                                @if ($plan->plan_type)
                                    <div class="mt-4">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-700">
                                            {{ $plan->plan_type->label() }}
                                        </span>
                                    </div>
                                @endif
                            </div>

                            {{-- Action --}}
                            <div class="px-6 pb-6">
                                @if ($isCurrent)
                                    <button disabled
                                            class="w-full py-2.5 px-4 rounded-md text-sm font-medium bg-gray-100 text-gray-500 cursor-not-allowed">
                                        Current Plan
                                    </button>
                                @else
                                    <button wire:click="selectPlan({{ $plan->id }})"
                                            wire:loading.attr="disabled"
                                            class="w-full py-2.5 px-4 rounded-md text-white text-sm font-medium shadow-sm hover:opacity-90 transition-opacity bg-brand-primary">
                                        <span wire:loading.remove wire:target="selectPlan({{ $plan->id }})">
                                            {{ $currentPlan ? 'Switch to This Plan' : 'Select Plan' }}
                                        </span>
                                        <span wire:loading wire:target="selectPlan({{ $plan->id }})">
                                            Redirecting to checkout...
                                        </span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
