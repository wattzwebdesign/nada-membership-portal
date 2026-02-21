<div>
    @if (session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('events.register', $event->slug) }}" method="POST" class="space-y-8">
        @csrf

        {{-- Registrant Information --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Your Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700">First Name *</label>
                    <input type="text" name="first_name" id="first_name" wire:model="first_name" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary sm:text-sm">
                    @error('first_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name *</label>
                    <input type="text" name="last_name" id="last_name" wire:model="last_name" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary sm:text-sm">
                    @error('last_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                    <div class="flex gap-2">
                        <input type="email" name="email" id="email" wire:model="email" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary sm:text-sm">
                        @if (!$isMemberVerified)
                            <button type="button" wire:click="verifyMember"
                                class="mt-1 inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 whitespace-nowrap">
                                Check Member
                            </button>
                        @endif
                    </div>
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    @if ($memberVerificationMessage)
                        <p class="mt-1 text-sm {{ $isMemberVerified ? 'text-green-600' : 'text-gray-500' }}">
                            {{ $memberVerificationMessage }}
                        </p>
                    @endif
                </div>
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                    <input type="tel" name="phone" id="phone" wire:model="phone"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary sm:text-sm">
                </div>
            </div>
        </div>

        {{-- Pricing Categories & Packages --}}
        @if ($event->pricingCategories->count())
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Registration Options</h3>

                @foreach ($event->pricingCategories as $category)
                    @if ($category->is_active && $category->packages->where('is_active', true)->count())
                        <div class="mb-6 last:mb-0">
                            <h4 class="font-medium text-gray-800 mb-1">
                                {{ $category->name }}
                                @if ($category->is_required)
                                    <span class="text-red-500">*</span>
                                @endif
                            </h4>
                            @if ($category->description)
                                <p class="text-sm text-gray-500 mb-3">{{ $category->description }}</p>
                            @endif

                            <div class="space-y-2">
                                @foreach ($category->packages->where('is_active', true) as $package)
                                    @php
                                        $currentPrice = $package->getCurrentPrice($isMemberVerified);
                                        $regularPrice = $package->price_cents;
                                        $hasDiscount = $currentPrice < $regularPrice;
                                    @endphp
                                    <label class="flex items-start p-3 border rounded-lg cursor-pointer hover:bg-gray-50 {{ isset($selectedPackages[$category->id]) && $selectedPackages[$category->id] == $package->id ? 'border-brand-primary bg-green-50' : 'border-gray-200' }}">
                                        <input type="radio"
                                            name="selected_packages[{{ $category->id }}]"
                                            value="{{ $package->id }}"
                                            wire:model.live="selectedPackages.{{ $category->id }}"
                                            class="mt-1 text-brand-primary focus:ring-brand-primary"
                                            {{ !$package->isAvailable() ? 'disabled' : '' }}>
                                        <div class="ml-3 flex-1">
                                            <div class="flex justify-between">
                                                <span class="font-medium text-gray-900">{{ $package->name }}</span>
                                                <span class="font-semibold">
                                                    @if ($currentPrice === 0)
                                                        Free
                                                    @else
                                                        ${{ number_format($currentPrice / 100, 2) }}
                                                        @if ($hasDiscount)
                                                            <span class="text-sm text-gray-400 line-through ml-1">${{ number_format($regularPrice / 100, 2) }}</span>
                                                        @endif
                                                    @endif
                                                </span>
                                            </div>
                                            @if ($package->description)
                                                <p class="text-sm text-gray-500">{{ $package->description }}</p>
                                            @endif
                                            @if ($package->isEarlyBird())
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 mt-1">
                                                    Early Bird - ends {{ $package->early_bird_deadline->format('M j') }}
                                                </span>
                                            @endif
                                            @if (!$package->isAvailable())
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 mt-1">Sold Out</span>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach

                @error('packages') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        @endif

        {{-- Custom Form Fields --}}
        @if ($event->formFields->where('is_active', true)->count())
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Additional Information</h3>

                <div class="space-y-4">
                    @foreach ($event->formFields->where('is_active', true) as $field)
                        @if ($field->isDisplayOnly())
                            @if ($field->type->value === 'heading')
                                <h4 class="text-md font-semibold text-gray-800 pt-2">{{ $field->label }}</h4>
                            @elseif ($field->type->value === 'paragraph')
                                <p class="text-sm text-gray-600">{{ $field->label }}</p>
                            @endif
                        @else
                            <div>
                                <label for="form_{{ $field->name }}" class="block text-sm font-medium text-gray-700">
                                    {{ $field->label }}
                                    @if ($field->is_required) <span class="text-red-500">*</span> @endif
                                </label>

                                @switch($field->type->value)
                                    @case('text')
                                    @case('email')
                                    @case('phone')
                                    @case('number')
                                        <input type="{{ $field->type->value === 'phone' ? 'tel' : $field->type->value }}"
                                            name="form_responses[{{ $field->name }}]"
                                            id="form_{{ $field->name }}"
                                            wire:model="formResponses.{{ $field->name }}"
                                            placeholder="{{ $field->placeholder }}"
                                            {{ $field->is_required ? 'required' : '' }}
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary sm:text-sm">
                                        @break

                                    @case('textarea')
                                        <textarea name="form_responses[{{ $field->name }}]"
                                            id="form_{{ $field->name }}"
                                            wire:model="formResponses.{{ $field->name }}"
                                            placeholder="{{ $field->placeholder }}"
                                            rows="3"
                                            {{ $field->is_required ? 'required' : '' }}
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary sm:text-sm"></textarea>
                                        @break

                                    @case('select')
                                        <select name="form_responses[{{ $field->name }}]"
                                            id="form_{{ $field->name }}"
                                            wire:model="formResponses.{{ $field->name }}"
                                            {{ $field->is_required ? 'required' : '' }}
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary sm:text-sm">
                                            <option value="">Select...</option>
                                            @foreach ($field->options as $option)
                                                <option value="{{ $option->value }}">{{ $option->label }}</option>
                                            @endforeach
                                        </select>
                                        @break

                                    @case('radio')
                                        <div class="mt-2 space-y-2">
                                            @foreach ($field->options as $option)
                                                <label class="flex items-center">
                                                    <input type="radio" name="form_responses[{{ $field->name }}]"
                                                        value="{{ $option->value }}"
                                                        wire:model="formResponses.{{ $field->name }}"
                                                        class="text-brand-primary focus:ring-brand-primary">
                                                    <span class="ml-2 text-sm text-gray-700">{{ $option->label }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                        @break

                                    @case('checkbox')
                                        <div class="mt-2 space-y-2">
                                            @foreach ($field->options as $option)
                                                <label class="flex items-center">
                                                    <input type="checkbox" name="form_responses[{{ $field->name }}][]"
                                                        value="{{ $option->value }}"
                                                        wire:model="formResponses.{{ $field->name }}"
                                                        class="rounded text-brand-primary focus:ring-brand-primary">
                                                    <span class="ml-2 text-sm text-gray-700">{{ $option->label }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                        @break

                                    @case('date')
                                        <input type="date"
                                            name="form_responses[{{ $field->name }}]"
                                            id="form_{{ $field->name }}"
                                            wire:model="formResponses.{{ $field->name }}"
                                            {{ $field->is_required ? 'required' : '' }}
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary sm:text-sm">
                                        @break

                                    @default
                                        <input type="text"
                                            name="form_responses[{{ $field->name }}]"
                                            id="form_{{ $field->name }}"
                                            wire:model="formResponses.{{ $field->name }}"
                                            placeholder="{{ $field->placeholder }}"
                                            {{ $field->is_required ? 'required' : '' }}
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary sm:text-sm">
                                @endswitch

                                @if ($field->help_text)
                                    <p class="mt-1 text-xs text-gray-500">{{ $field->help_text }}</p>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Order Summary --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h3>

            @if (count($lineItems))
                <div class="space-y-2 mb-4">
                    @foreach ($lineItems as $item)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">
                                {{ $item['category_name'] }}: {{ $item['package_name'] }}
                                @if ($item['is_early_bird'])
                                    <span class="text-yellow-600">(Early Bird)</span>
                                @endif
                                @if ($item['is_member_pricing'])
                                    <span class="text-green-600">(Member)</span>
                                @endif
                            </span>
                            <span class="font-medium">${{ number_format($item['price_cents'] / 100, 2) }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="border-t pt-3 flex justify-between">
                    <span class="text-lg font-semibold">Total</span>
                    <span class="text-lg font-bold text-brand-primary">${{ number_format($totalCents / 100, 2) }}</span>
                </div>
            @else
                <p class="text-gray-500 text-sm">
                    @if ($event->pricingCategories->count())
                        Select your registration options above.
                    @else
                        This is a free event.
                    @endif
                </p>
            @endif
        </div>

        {{-- Submit --}}
        <div class="flex justify-end">
            <button type="submit"
                class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-brand-primary hover:bg-brand-accent focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-primary">
                @if ($totalCents > 0)
                    Proceed to Payment
                @else
                    Register Now
                @endif
            </button>
        </div>
    </form>
</div>
