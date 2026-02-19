<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Product') }}: {{ $product->title }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md">
                    {{ session('success') }}
                </div>
            @endif

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

            @php
                $productStatus = is_object($product->status) ? $product->status->value : $product->status;
            @endphp

            <form method="POST" action="{{ route('vendor.products.update', $product) }}" enctype="multipart/form-data" x-data="{ isDigital: {{ old('is_digital', $product->is_digital) ? 'true' : 'false' }}, trackStock: {{ old('track_stock', $product->track_stock) ? 'true' : 'false' }} }">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    {{-- Left Column (2/3) --}}
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h3 class="text-lg font-semibold mb-4 text-brand-primary">Product Details</h3>

                            <div class="space-y-5">
                                {{-- Title --}}
                                <div>
                                    <label for="title" class="block text-sm font-medium text-gray-700">Title *</label>
                                    <input type="text" name="title" id="title" value="{{ old('title', $product->title) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                                    @error('title')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Description --}}
                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                    <textarea name="description" id="description" rows="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">{{ old('description', $product->description) }}</textarea>
                                    @error('description')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
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
                                            <input type="number" name="price" id="price" value="{{ old('price', $product->price ? number_format($product->price / 100, 2, '.', '') : '') }}" step="0.01" min="0" required class="pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
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
                                            <input type="number" name="member_price" id="member_price" value="{{ old('member_price', $product->member_price ? number_format($product->member_price / 100, 2, '.', '') : '') }}" step="0.01" min="0" class="pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
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
                                        <input type="number" name="shipping_fee" id="shipping_fee" value="{{ old('shipping_fee', $product->shipping_fee ? number_format($product->shipping_fee / 100, 2, '.', '') : '') }}" step="0.01" min="0" class="pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
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
                                {{-- Existing Images --}}
                                @if ($product->images && count($product->images) > 0)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Current Images</label>
                                        <div class="flex flex-wrap gap-3">
                                            @foreach ($product->images as $image)
                                                <div class="relative group">
                                                    <img src="{{ $image->url }}" alt="{{ $product->title }}" class="h-24 w-24 rounded-lg object-cover border border-gray-200">
                                                    <label class="absolute inset-0 flex items-center justify-center bg-black/50 opacity-0 group-hover:opacity-100 transition rounded-lg cursor-pointer">
                                                        <input type="checkbox" name="remove_images[]" value="{{ $image->id }}" class="sr-only">
                                                        <span class="text-xs text-white font-medium" onclick="this.previousElementSibling.checked = !this.previousElementSibling.checked; this.textContent = this.previousElementSibling.checked ? 'Removing' : 'Remove';">Remove</span>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                        <p class="mt-1 text-xs text-gray-400">Hover over an image and click to mark it for removal.</p>
                                    </div>
                                @endif

                                {{-- Upload New Images --}}
                                <div>
                                    <label for="images" class="block text-sm font-medium text-gray-700">Add More Images</label>
                                    <input type="file" name="images[]" id="images" accept="image/*" multiple class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-brand-primary/10 file:text-brand-primary hover:file:bg-brand-primary/20">
                                    @error('images')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    @error('images.*')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Digital File Upload --}}
                                <div x-show="isDigital" x-cloak>
                                    <label for="digital_file" class="block text-sm font-medium text-gray-700">Digital File</label>
                                    @if ($product->digital_file_path)
                                        <p class="mt-1 text-xs text-green-600 flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            A digital file is already attached. Upload a new one to replace it.
                                        </p>
                                    @endif
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
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-brand-primary">Status</h3>
                                @php
                                    $editStatusColors = [
                                        'draft' => 'bg-gray-100 text-gray-800',
                                        'active' => 'bg-green-100 text-green-800',
                                        'inactive' => 'bg-red-100 text-red-800',
                                    ];
                                    $editStatusColor = $editStatusColors[$productStatus] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $editStatusColor }}">
                                    {{ ucfirst($productStatus) }}
                                </span>
                            </div>

                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Visibility *</label>
                                <select name="status" id="status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                                    <option value="draft" {{ old('status', $productStatus) === 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="active" {{ old('status', $productStatus) === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $productStatus) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
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
                                    <input type="text" name="sku" id="sku" value="{{ old('sku', $product->sku) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                                    @error('sku')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Category --}}
                                <div>
                                    <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                                    <select name="category_id" id="category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                                        <option value="">-- Select Category --</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
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
                                        <input type="checkbox" name="is_digital" value="1" class="rounded border-gray-300 shadow-sm text-brand-primary" x-model="isDigital" {{ old('is_digital', $product->is_digital) ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm font-medium text-gray-700">Digital product</span>
                                    </label>
                                    <p class="mt-1 ml-6 text-xs text-gray-500">Delivered electronically, no shipping required.</p>
                                </div>

                                {{-- Stock --}}
                                <div x-show="!isDigital" class="border border-gray-200 rounded-lg p-4 space-y-4">
                                    <label class="flex items-center cursor-pointer">
                                        <input type="checkbox" name="track_stock" value="1" class="rounded border-gray-300 shadow-sm text-brand-primary" x-model="trackStock" {{ old('track_stock', $product->track_stock) ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm font-medium text-gray-700">Track stock</span>
                                    </label>

                                    <div x-show="trackStock" x-cloak>
                                        <label for="stock_quantity" class="block text-sm font-medium text-gray-700">Quantity *</label>
                                        <input type="number" name="stock_quantity" id="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                                        @error('stock_quantity')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Delete Product --}}
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h3 class="text-sm font-semibold mb-3 text-red-600">Danger Zone</h3>
                            <form method="POST" action="{{ route('vendor.products.destroy', $product) }}" onsubmit="return confirm('Are you sure you want to delete this product? This action cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-red-300 text-sm font-medium rounded-md text-red-700 hover:bg-red-50">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Delete Product
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="mt-6 flex items-center justify-between">
                    <a href="{{ route('vendor.products.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
                    <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-md text-white bg-brand-primary">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
