<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Product') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
                    <p class="text-sm font-medium">Please fix the following errors:</p>
                    <ul class="mt-1 text-sm list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('vendor.products.store') }}" enctype="multipart/form-data" x-data="{ isDigital: {{ old('is_digital') ? 'true' : 'false' }}, trackStock: {{ old('track_stock', true) ? 'true' : 'false' }} }">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    {{-- Left Column (2/3) --}}
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h3 class="text-lg font-semibold mb-4 text-brand-primary">Product Details</h3>

                            <div class="space-y-5">
                                {{-- Title --}}
                                <div>
                                    <label for="title" class="block text-sm font-medium text-gray-700">Title *</label>
                                    <input type="text" name="title" id="title" value="{{ old('title') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" placeholder="Product name">
                                    @error('title')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Description --}}
                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                    <textarea name="description" id="description" rows="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" placeholder="Describe your product, its features, and benefits...">{{ old('description') }}</textarea>
                                    @error('description')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Product Type --}}
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h3 class="text-lg font-semibold mb-4 text-brand-primary">Product Type</h3>

                            <div class="space-y-5">
                                {{-- Digital Product Toggle --}}
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <label class="flex items-center cursor-pointer">
                                        <input type="checkbox" name="is_digital" value="1" class="rounded border-gray-300 shadow-sm text-brand-primary" x-model="isDigital" {{ old('is_digital') ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm font-medium text-gray-700">Digital product</span>
                                    </label>
                                    <p class="mt-1 ml-6 text-xs text-gray-500">Delivered electronically, no shipping required.</p>
                                </div>

                                {{-- Stock --}}
                                <div x-show="!isDigital" class="border border-gray-200 rounded-lg p-4 space-y-4">
                                    <label class="flex items-center cursor-pointer">
                                        <input type="checkbox" name="track_stock" value="1" class="rounded border-gray-300 shadow-sm text-brand-primary" x-model="trackStock" {{ old('track_stock', true) ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm font-medium text-gray-700">Track stock</span>
                                    </label>

                                    <div x-show="trackStock" x-cloak>
                                        <label for="stock_quantity" class="block text-sm font-medium text-gray-700">Quantity *</label>
                                        <input type="number" name="stock_quantity" id="stock_quantity" value="{{ old('stock_quantity', 0) }}" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                                        @error('stock_quantity')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Pricing --}}
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h3 class="text-lg font-semibold mb-4 text-brand-primary">Pricing</h3>

                            <div class="space-y-5">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label for="price" class="block text-sm font-medium text-gray-700">Price ($) *</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">$</span>
                                            </div>
                                            <input type="number" name="price" id="price" value="{{ old('price') }}" step="0.01" min="0" required class="pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" placeholder="0.00">
                                        </div>
                                        @error('price')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="member_price" class="block text-sm font-medium text-gray-700">Member Price ($)</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">$</span>
                                            </div>
                                            <input type="number" name="member_price" id="member_price" value="{{ old('member_price') }}" step="0.01" min="0" class="pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" placeholder="0.00">
                                        </div>
                                        <p class="mt-1 text-xs text-gray-400">Optional. Discounted price for NADA members.</p>
                                        @error('member_price')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Shipping Fee --}}
                                <div x-show="!isDigital">
                                    <label for="shipping_fee" class="block text-sm font-medium text-gray-700">Shipping Fee ($)</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">$</span>
                                        </div>
                                        <input type="number" name="shipping_fee" id="shipping_fee" value="{{ old('shipping_fee') }}" step="0.01" min="0" class="pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" placeholder="0.00">
                                    </div>
                                    <p class="mt-1 text-xs text-gray-400">Leave blank to use your default shipping fee.</p>
                                    @error('shipping_fee')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Images --}}
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h3 class="text-lg font-semibold mb-4 text-brand-primary">Images</h3>

                            <div class="space-y-5">
                                <div x-data="imageCompressor">
                                    <label for="images" class="block text-sm font-medium text-gray-700">Product Images</label>
                                    <input type="file" name="images[]" id="images" accept="image/*" multiple x-on:change="compressFiles($event)" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-brand-primary/10 file:text-brand-primary hover:file:bg-brand-primary/20">
                                    <span x-show="compressing" x-cloak class="mt-1 text-sm text-gray-500">Optimizing images...</span>
                                    <p class="mt-1 text-xs text-gray-400">Upload up to 10 product images. First image will be the primary image.</p>
                                    @error('images')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    @error('images.*')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Digital File Upload --}}
                                <div x-show="isDigital" x-cloak>
                                    <label for="digital_file" class="block text-sm font-medium text-gray-700">Digital File *</label>
                                    <input type="file" name="digital_file" id="digital_file" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-brand-primary/10 file:text-brand-primary hover:file:bg-brand-primary/20">
                                    <p class="mt-1 text-xs text-gray-400">The file customers will receive after purchase. Max 100MB.</p>
                                    @error('digital_file')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column (1/3) --}}
                    <div class="lg:col-span-1 space-y-6">
                        {{-- Status --}}
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h3 class="text-lg font-semibold mb-4 text-brand-primary">Status</h3>

                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Visibility *</label>
                                <select name="status" id="status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                                    <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                                </select>
                                <p class="mt-1 text-xs text-gray-400">Draft products are not visible to customers.</p>
                                @error('status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Organization --}}
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h3 class="text-lg font-semibold mb-4 text-brand-primary">Organization</h3>

                            <div class="space-y-5">
                                {{-- SKU --}}
                                <div>
                                    <label for="sku" class="block text-sm font-medium text-gray-700">SKU</label>
                                    <input type="text" name="sku" id="sku" value="{{ old('sku') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" placeholder="e.g., NADA-NEEDLE-001">
                                    <p class="mt-1 text-xs text-gray-400">Optional. A unique identifier for inventory tracking.</p>
                                    @error('sku')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Category --}}
                                <div x-data="{ showNew: false }">
                                    <label for="product_category_id" class="block text-sm font-medium text-gray-700">Category</label>
                                    <div x-show="!showNew">
                                        <select name="product_category_id" id="product_category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                                            <option value="">-- Select Category --</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}" {{ old('product_category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" @click="showNew = true" class="mt-1.5 text-xs text-brand-primary hover:underline">+ Add new category</button>
                                    </div>
                                    <div x-show="showNew" x-cloak>
                                        <input type="text" name="new_category" id="new_category" value="{{ old('new_category') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" placeholder="New category name">
                                        <button type="button" @click="showNew = false; document.getElementById('new_category').value = ''" class="mt-1.5 text-xs text-gray-500 hover:underline">Cancel, select existing</button>
                                    </div>
                                    @error('product_category_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    @error('new_category')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Submit --}}
                <div class="mt-6 flex items-center justify-between">
                    <a href="{{ route('vendor.products.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
                    <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-md text-white bg-brand-primary">
                        Create Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
