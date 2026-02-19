<x-public-layout>
    <x-slot name="title">Sell on NADA - NADA</x-slot>

    {{-- Hero Section --}}
    <div class="py-12 bg-brand-primary">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl sm:text-4xl font-bold text-white">Sell on the NADA Marketplace</h1>
            <p class="mt-4 text-lg text-gray-300 max-w-2xl mx-auto">Join our community of vendors and reach NADA members with your products.</p>
        </div>
    </div>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="mb-6 rounded-md bg-green-50 border border-green-200 p-4">
                <div class="flex">
                    <svg class="h-5 w-5 text-green-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <p class="ml-3 text-sm text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 rounded-md bg-red-50 border border-red-200 p-4">
                <div class="flex">
                    <svg class="h-5 w-5 text-red-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    <p class="ml-3 text-sm text-red-700">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        {{-- Already a vendor --}}
        @if (isset($user) && $user->hasActiveVendorProfile())
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
                <svg class="w-16 h-16 mx-auto text-green-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <h2 class="text-xl font-bold text-gray-900">You're Already a Vendor</h2>
                <p class="mt-2 text-gray-600">Your vendor account is active. Head to your vendor portal to manage products and orders.</p>
                <a href="{{ route('vendor.dashboard') }}" class="mt-6 inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-brand-primary hover:bg-brand-primary-hover transition-colors">
                    Go to Vendor Portal
                </a>
            </div>

        {{-- Pending application --}}
        @elseif (isset($user) && $user->vendorApplications()->pending()->exists())
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
                <svg class="w-16 h-16 mx-auto text-brand-secondary mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <h2 class="text-xl font-bold text-gray-900">Application Under Review</h2>
                <p class="mt-2 text-gray-600">Your vendor application has been submitted and is currently being reviewed by our team. We'll notify you by email once a decision has been made.</p>
                <div class="mt-6 inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Pending Review
                </div>
            </div>

        {{-- Application form --}}
        @else
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sm:p-8">
                <h2 class="text-xl font-bold text-gray-900 mb-2">Vendor Application</h2>
                <p class="text-sm text-gray-600 mb-6">Fill out the form below to apply to sell on the NADA Marketplace. We review all applications and will get back to you within a few business days.</p>

                <form method="POST" action="{{ route('vendor-application.store') }}">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- First Name --}}
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700">First Name <span class="text-red-500">*</span></label>
                            <input type="text" name="first_name" id="first_name"
                                   value="{{ old('first_name', $user->first_name ?? '') }}" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary text-sm">
                            @error('first_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Last Name --}}
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name <span class="text-red-500">*</span></label>
                            <input type="text" name="last_name" id="last_name"
                                   value="{{ old('last_name', $user->last_name ?? '') }}" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary text-sm">
                            @error('last_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Email --}}
                    <div class="mt-4">
                        <label for="email" class="block text-sm font-medium text-gray-700">Email Address <span class="text-red-500">*</span></label>
                        <input type="email" name="email" id="email"
                               value="{{ old('email', $user->email ?? '') }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary text-sm">
                        @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Business Name --}}
                    <div class="mt-4">
                        <label for="business_name" class="block text-sm font-medium text-gray-700">Business Name <span class="text-red-500">*</span></label>
                        <input type="text" name="business_name" id="business_name"
                               value="{{ old('business_name') }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary text-sm">
                        @error('business_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Website --}}
                    <div class="mt-4">
                        <label for="website" class="block text-sm font-medium text-gray-700">Website</label>
                        <input type="url" name="website" id="website"
                               value="{{ old('website') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary text-sm"
                               placeholder="https://www.example.com">
                        @error('website') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- What They Sell --}}
                    <div class="mt-4">
                        <label for="what_they_sell" class="block text-sm font-medium text-gray-700">What do you plan to sell? <span class="text-red-500">*</span></label>
                        <textarea name="what_they_sell" id="what_they_sell" rows="4" required
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary text-sm"
                                  placeholder="Describe the products you'd like to sell on the NADA Marketplace...">{{ old('what_they_sell') }}</textarea>
                        @error('what_they_sell') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Submit --}}
                    <div class="mt-6">
                        <button type="submit" class="w-full inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-brand-primary hover:bg-brand-primary-hover transition-colors">
                            Submit Application
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </div>
</x-public-layout>
