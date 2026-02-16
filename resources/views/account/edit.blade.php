<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Account Settings') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

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

            {{-- Profile Information --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-2" style="color: #374269;">Profile Information</h3>
                    <p class="text-sm text-gray-500 mb-6">Update your personal information and contact details.</p>

                    <form method="POST" action="{{ route('account.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="space-y-4">
                            {{-- Name --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                                    <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $user->first_name) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                                    @error('first_name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                                    <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $user->last_name) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                                    @error('last_name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Email (read-only) --}}
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                                <input type="email" id="email" value="{{ $user->email }}" disabled class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 text-gray-500 shadow-sm sm:text-sm cursor-not-allowed">
                                <p class="mt-1 text-xs text-gray-400">Contact support to change your email address.</p>
                            </div>

                            {{-- Phone --}}
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                                <input type="tel" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" placeholder="(555) 123-4567">
                                @error('phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Address --}}
                            <div>
                                <label for="address_line_1" class="block text-sm font-medium text-gray-700">Address Line 1</label>
                                <input type="text" name="address_line_1" id="address_line_1" value="{{ old('address_line_1', $user->address_line_1) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" placeholder="123 Main Street">
                                @error('address_line_1')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="address_line_2" class="block text-sm font-medium text-gray-700">Address Line 2</label>
                                <input type="text" name="address_line_2" id="address_line_2" value="{{ old('address_line_2', $user->address_line_2) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" placeholder="Suite 100">
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                <div class="col-span-2 sm:col-span-1">
                                    <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                                    <input type="text" name="city" id="city" value="{{ old('city', $user->city) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                                </div>
                                <div>
                                    <label for="state" class="block text-sm font-medium text-gray-700">State</label>
                                    <input type="text" name="state" id="state" value="{{ old('state', $user->state) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                                </div>
                                <div>
                                    <label for="zip" class="block text-sm font-medium text-gray-700">ZIP</label>
                                    <input type="text" name="zip" id="zip" value="{{ old('zip', $user->zip) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                                </div>
                            </div>

                            <div>
                                <label for="country" class="block text-sm font-medium text-gray-700">Country</label>
                                <select name="country" id="country" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                                    <option value="US" {{ old('country', $user->country) === 'US' ? 'selected' : '' }}>United States</option>
                                    <option value="CA" {{ old('country', $user->country) === 'CA' ? 'selected' : '' }}>Canada</option>
                                    <option value="GB" {{ old('country', $user->country) === 'GB' ? 'selected' : '' }}>United Kingdom</option>
                                    <option value="AU" {{ old('country', $user->country) === 'AU' ? 'selected' : '' }}>Australia</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-6">
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white" style="background-color: #374269;">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4" style="color: #374269;">Account Actions</h3>

                    <div class="space-y-4">
                        {{-- Request Discount --}}
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Student / Senior Discount</p>
                                <p class="text-xs text-gray-500">Request a discounted membership rate.</p>
                            </div>
                            @if ($user->discount_approved)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ $user->discount_type->label() }} Approved
                                </span>
                            @else
                                <a href="{{ route('discount.request.create') }}" class="inline-flex items-center px-3 py-1.5 border text-xs font-medium rounded-md" style="border-color: #374269; color: #374269;">
                                    Request Discount
                                </a>
                            @endif
                        </div>

                        {{-- Upgrade to Trainer --}}
                        @if (!$user->isTrainer())
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Upgrade to Registered Trainer</p>
                                    <p class="text-xs text-gray-500">Apply to become a NADA Registered Trainer.</p>
                                </div>
                                @if ($user->trainer_application_status === 'pending')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Application Pending
                                    </span>
                                @elseif ($user->trainer_application_status === 'denied')
                                    <a href="{{ route('trainer-application.create') }}" class="inline-flex items-center px-3 py-1.5 border text-xs font-medium rounded-md" style="border-color: #374269; color: #374269;">
                                        Reapply
                                    </a>
                                @else
                                    <a href="{{ route('trainer-application.create') }}" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white" style="background-color: #d39c27;">
                                        Apply Now
                                    </a>
                                @endif
                            </div>
                        @else
                            <div class="flex items-center justify-between p-4 border border-green-200 bg-green-50 rounded-lg">
                                <div>
                                    <p class="text-sm font-medium text-green-800">Registered Trainer</p>
                                    <p class="text-xs text-green-600">You are an approved NADA Registered Trainer.</p>
                                </div>
                                <a href="{{ route('trainer.dashboard') }}" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white" style="background-color: #374269;">
                                    Trainer Portal
                                </a>
                            </div>
                        @endif

                        {{-- Profile & Password --}}
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Password & Security</p>
                                <p class="text-xs text-gray-500">Update your password and security settings.</p>
                            </div>
                            <a href="{{ route('profile.edit') }}" class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 hover:bg-gray-50">
                                Manage
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
