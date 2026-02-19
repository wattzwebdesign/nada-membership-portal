<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('My Products') }}
            </h2>
            @if (!$needsProfile)
                <a href="{{ route('vendor.products.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-brand-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Create Product
                </a>
            @endif
        </div>
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

            @if (session('warning'))
                <div class="mb-6 bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 rounded-md">
                    {{ session('warning') }}
                </div>
            @endif

            @if ($needsProfile)
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-5 mb-6">
                    <div class="flex items-start gap-3">
                        <svg class="h-6 w-6 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        <div>
                            <h3 class="text-sm font-semibold text-amber-800">Set Up Your Profile First</h3>
                            <p class="text-sm text-amber-700 mt-1">You need to create your vendor profile before you can add products.</p>
                            <a href="{{ route('vendor.profile.edit') }}" class="mt-3 inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md text-white bg-brand-primary">
                                Set Up Profile
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @if (isset($products) && $products->count() > 0)
                    {{-- Desktop Table --}}
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($products as $product)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                @if ($product->featured_image_url)
                                                    <img src="{{ $product->featured_image_url }}" alt="{{ $product->title }}" class="h-10 w-10 rounded-md object-cover flex-shrink-0">
                                                @else
                                                    <div class="h-10 w-10 rounded-md bg-gray-100 flex items-center justify-center flex-shrink-0">
                                                        <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                    </div>
                                                @endif
                                                <a href="{{ route('vendor.products.show', $product) }}" class="text-sm font-medium hover:underline text-brand-primary">{{ $product->title }}</a>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $productStatus = is_object($product->status) ? $product->status->value : $product->status;
                                                $productStatusColors = [
                                                    'draft' => 'bg-gray-100 text-gray-800',
                                                    'active' => 'bg-green-100 text-green-800',
                                                    'inactive' => 'bg-red-100 text-red-800',
                                                ];
                                                $productStatusColor = $productStatusColors[$productStatus] ?? 'bg-gray-100 text-gray-800';
                                            @endphp
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $productStatusColor }}">
                                                {{ ucfirst($productStatus) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            ${{ number_format($product->price_cents / 100, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if ($product->track_stock)
                                                {{ $product->stock_quantity }}
                                            @else
                                                <span class="text-gray-400">N/A</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($product->is_digital)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">Digital</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Physical</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $product->created_at->format('M j, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2">
                                            <a href="{{ route('public.shop.show', $product) }}" target="_blank" class="font-medium text-brand-secondary" title="Preview in shop">Preview</a>
                                            <a href="{{ route('vendor.products.show', $product) }}" class="font-medium text-brand-primary">View</a>
                                            <a href="{{ route('vendor.products.edit', $product) }}" class="font-medium text-gray-600 hover:text-gray-900">Edit</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile Cards --}}
                    <div class="md:hidden divide-y divide-gray-200">
                        @foreach ($products as $product)
                            @php
                                $productStatus = is_object($product->status) ? $product->status->value : $product->status;
                                $productStatusColors = [
                                    'draft' => 'bg-gray-100 text-gray-800',
                                    'active' => 'bg-green-100 text-green-800',
                                    'inactive' => 'bg-red-100 text-red-800',
                                ];
                                $productStatusColor = $productStatusColors[$productStatus] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <div class="p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-3 min-w-0">
                                        @if ($product->featured_image_url)
                                            <img src="{{ $product->featured_image_url }}" alt="{{ $product->title }}" class="h-10 w-10 rounded-md object-cover flex-shrink-0">
                                        @endif
                                        <a href="{{ route('vendor.products.show', $product) }}" class="text-sm font-medium hover:underline truncate text-brand-primary">{{ $product->title }}</a>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $productStatusColor }} flex-shrink-0 ml-2">
                                        {{ ucfirst($productStatus) }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500">${{ number_format($product->price_cents / 100, 2) }} | {{ $product->is_digital ? 'Digital' : 'Physical' }} | {{ $product->created_at->format('M j, Y') }}</p>
                                <div class="mt-2 flex space-x-3">
                                    <a href="{{ route('public.shop.show', $product) }}" target="_blank" class="text-xs font-medium text-brand-secondary">Preview</a>
                                    <a href="{{ route('vendor.products.show', $product) }}" class="text-xs font-medium text-brand-primary">View</a>
                                    <a href="{{ route('vendor.products.edit', $product) }}" class="text-xs font-medium text-gray-600">Edit</a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if ($products->hasPages())
                        <div class="px-6 py-4 border-t border-gray-200">
                            {{ $products->links() }}
                        </div>
                    @endif
                @else
                    <div class="p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <h3 class="mt-3 text-sm font-medium text-gray-900">No Products Yet</h3>
                        <p class="mt-1 text-sm text-gray-500">Create your first product to start selling in the NADA store.</p>
                        @if (!$needsProfile)
                            <div class="mt-6">
                                <a href="{{ route('vendor.products.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-brand-primary">
                                    Create Product
                                </a>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
