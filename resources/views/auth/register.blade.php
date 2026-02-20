<x-guest-layout>
    @if ($plan ?? null)
        <div class="mb-6 rounded-lg border border-brand-primary/20 bg-brand-primary/5 p-4">
            <p class="text-sm font-medium text-brand-primary">Selected Plan</p>
            <p class="mt-1 text-lg font-semibold text-brand-primary">{{ $plan->name }}</p>
            <p class="text-sm text-gray-600">{{ $plan->price_formatted }} {{ $plan->billing_label }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        @if ($plan ?? null)
            <input type="hidden" name="plan_id" value="{{ $plan->id }}">
        @endif

        <!-- First Name -->
        <div>
            <x-input-label for="first_name" :value="__('First Name')" />
            <x-text-input id="first_name" class="block mt-1 w-full" type="text" name="first_name" :value="old('first_name')" required autofocus autocomplete="given-name" />
            <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
        </div>

        <!-- Last Name -->
        <div class="mt-4">
            <x-input-label for="last_name" :value="__('Last Name')" />
            <x-text-input id="last_name" class="block mt-1 w-full" type="text" name="last_name" :value="old('last_name')" required autocomplete="family-name" />
            <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
        </div>

        <!-- Organization (optional) -->
        <div class="mt-4">
            <x-input-label for="organization" :value="__('Company / Organization (optional)')" />
            <x-text-input id="organization" class="block mt-1 w-full" type="text" name="organization" :value="old('organization')" autocomplete="organization" />
            <x-input-error :messages="$errors->get('organization')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Address -->
        <div class="mt-4">
            <x-input-label for="address_line_1" :value="__('Address')" />
            <x-text-input id="address_line_1" class="block mt-1 w-full" type="text" name="address_line_1" :value="old('address_line_1')" required autocomplete="address-line1" placeholder="Street address" />
            <x-input-error :messages="$errors->get('address_line_1')" class="mt-2" />
        </div>

        <!-- Address Line 2 -->
        <div class="mt-4">
            <x-input-label for="address_line_2" :value="__('Address Line 2 (optional)')" />
            <x-text-input id="address_line_2" class="block mt-1 w-full" type="text" name="address_line_2" :value="old('address_line_2')" autocomplete="address-line2" placeholder="Apt, suite, unit, etc." />
            <x-input-error :messages="$errors->get('address_line_2')" class="mt-2" />
        </div>

        <!-- City, State, Zip -->
        <div class="mt-4 grid grid-cols-6 gap-4">
            <div class="col-span-2">
                <x-input-label for="city" :value="__('City')" />
                <x-text-input id="city" class="block mt-1 w-full" type="text" name="city" :value="old('city')" required autocomplete="address-level2" />
                <x-input-error :messages="$errors->get('city')" class="mt-2" />
            </div>
            <div class="col-span-2">
                <x-input-label for="state" :value="__('State')" />
                <select id="state" name="state" required autocomplete="address-level1"
                        class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="">Select...</option>
                    @foreach (['AL','AK','AZ','AR','CA','CO','CT','DE','FL','GA','HI','ID','IL','IN','IA','KS','KY','LA','ME','MD','MA','MI','MN','MS','MO','MT','NE','NV','NH','NJ','NM','NY','NC','ND','OH','OK','OR','PA','RI','SC','SD','TN','TX','UT','VT','VA','WA','WV','WI','WY','DC'] as $st)
                        <option value="{{ $st }}" {{ old('state') === $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('state')" class="mt-2" />
            </div>
            <div class="col-span-2">
                <x-input-label for="zip" :value="__('Zip')" />
                <x-text-input id="zip" class="block mt-1 w-full" type="text" name="zip" :value="old('zip')" required autocomplete="postal-code" />
                <x-input-error :messages="$errors->get('zip')" class="mt-2" />
            </div>
        </div>

        <input type="hidden" name="country" value="US">

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
