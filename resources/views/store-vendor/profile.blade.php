<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Vendor Profile') }}
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

            @if (session('warning'))
                <div class="bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 rounded-md">
                    {{ session('warning') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
                    <p class="text-sm font-medium">Please fix the following errors:</p>
                    <ul class="mt-1 text-sm list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (!$vendorProfile)
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <svg class="h-5 w-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                        <div>
                            <h4 class="text-sm font-semibold text-amber-800">Create Your Vendor Profile</h4>
                            <p class="text-sm text-amber-700 mt-1">You need to set up your vendor profile before you can list products in the NADA store.</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Profile Form --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-2 text-brand-primary">{{ $vendorProfile ? 'Edit Vendor Profile' : 'Create Vendor Profile' }}</h3>
                    <p class="text-sm text-gray-500 mb-6">This information will be displayed to customers on your store page.</p>

                    <form data-guide="vendor-profile-form" method="POST" action="{{ route('vendor.profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="space-y-5">
                            {{-- Business Name --}}
                            <div>
                                <label for="business_name" class="block text-sm font-medium text-gray-700">Business Name *</label>
                                <input type="text" name="business_name" id="business_name" value="{{ old('business_name', $vendorProfile->business_name ?? '') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" placeholder="Your business or brand name">
                                @error('business_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Description --}}
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea name="description" id="description" rows="4" maxlength="2000" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" placeholder="Describe your business, products, and what customers can expect...">{{ old('description', $vendorProfile->description ?? '') }}</textarea>
                                <p class="mt-1 text-xs text-gray-400">Max 2,000 characters.</p>
                                @error('description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Contact Info --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700">Contact Email</label>
                                    <input type="email" name="email" id="email" value="{{ old('email', $vendorProfile->email ?? $user->email) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" placeholder="vendor@example.com">
                                    @error('email')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                                    <input type="tel" name="phone" id="phone" value="{{ old('phone', $vendorProfile->phone ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" placeholder="(555) 123-4567">
                                    @error('phone')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Website --}}
                            <div>
                                <label for="website" class="block text-sm font-medium text-gray-700">Website</label>
                                <input type="url" name="website" id="website" value="{{ old('website', $vendorProfile->website ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" placeholder="https://www.example.com">
                                @error('website')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Default Shipping Fee --}}
                            <div>
                                <label for="default_shipping_fee" class="block text-sm font-medium text-gray-700">Default Shipping Fee ($)</label>
                                <p class="text-xs text-gray-500 mb-1">This will be applied to all products unless overridden on individual products.</p>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">$</span>
                                    </div>
                                    <input type="number" name="default_shipping_fee" id="default_shipping_fee" value="{{ old('default_shipping_fee', $vendorProfile ? number_format($vendorProfile->default_shipping_fee / 100, 2) : '') }}" step="0.01" min="0" class="pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" placeholder="0.00">
                                </div>
                                @error('default_shipping_fee')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Logo Upload --}}
                            <div>
                                <label for="logo" class="block text-sm font-medium text-gray-700">Business Logo</label>
                                @if ($vendorProfile && $vendorProfile->logo_url)
                                    <div class="mt-2 mb-3 flex items-center gap-4">
                                        <img src="{{ $vendorProfile->logo_url }}" alt="Current logo" class="h-16 w-16 rounded-lg object-cover border border-gray-200">
                                        <span class="text-xs text-gray-500">Current logo</span>
                                    </div>
                                @endif
                                <input type="file" name="logo" id="logo" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-brand-primary/10 file:text-brand-primary hover:file:bg-brand-primary/20">
                                <p class="mt-1 text-xs text-gray-400">Recommended: 400x400px, JPG or PNG. Your logo will be cropped to a square.</p>
                                @error('logo')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Gallery Images --}}
                            <div>
                                <label for="gallery" class="block text-sm font-medium text-gray-700">Gallery Images</label>
                                @if ($vendorProfile && $vendorProfile->gallery_urls && count($vendorProfile->gallery_urls) > 0)
                                    <div class="mt-2 mb-3 flex flex-wrap gap-3">
                                        @foreach ($vendorProfile->gallery_urls as $url)
                                            <img src="{{ $url }}" alt="Gallery image" class="h-20 w-20 rounded-lg object-cover border border-gray-200">
                                        @endforeach
                                    </div>
                                @endif
                                <input type="file" name="gallery[]" id="gallery" accept="image/*" multiple class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-brand-primary/10 file:text-brand-primary hover:file:bg-brand-primary/20">
                                <p class="mt-1 text-xs text-gray-400">Upload multiple images to showcase your business. Max 5 images.</p>
                                @error('gallery')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                @error('gallery.*')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-6 flex items-center gap-3">
                            <button data-guide="vendor-profile-save" type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-brand-primary">
                                {{ $vendorProfile ? 'Save Profile' : 'Create Profile' }}
                            </button>
                            <a href="{{ route('vendor.dashboard') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
