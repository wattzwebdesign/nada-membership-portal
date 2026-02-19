<x-public-layout>
    <x-slot name="title">Shop - NADA</x-slot>

    {{-- Hero Section --}}
    <div class="py-10 text-center text-white bg-brand-primary">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold">NADA Shop</h1>
            <p class="mt-2 text-gray-300 text-lg">Browse products from NADA vendors and community members</p>
        </div>
    </div>

    {{-- Search & Filters --}}
    <div class="sticky top-0 z-20 bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <form method="GET" action="{{ route('public.shop.index') }}" class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search products..."
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary sm:text-sm">
                </div>
                @if ($currentCategory)
                    <input type="hidden" name="category" value="{{ $currentCategory->slug }}">
                @endif
                <div class="flex gap-2">
                    <select name="sort" class="rounded-md border-gray-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary sm:text-sm">
                        <option value="newest" {{ ($sort ?? 'newest') === 'newest' ? 'selected' : '' }}>Newest</option>
                        <option value="price_low" {{ ($sort ?? '') === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_high" {{ ($sort ?? '') === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                    </select>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-brand-primary hover:bg-brand-primary-hover transition-colors">
                        Search
                    </button>
                    @if ($search || $sort || $currentCategory)
                        <a href="{{ route('public.shop.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

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

        {{-- Category Filter Links --}}
        @if ($categories->isNotEmpty())
            <div class="flex flex-wrap gap-2 mb-8">
                <a href="{{ route('public.shop.index', array_filter(['search' => $search, 'sort' => $sort])) }}"
                   class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium transition-colors {{ !$currentCategory ? 'bg-brand-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    All Products
                </a>
                @foreach ($categories as $category)
                    <a href="{{ route('public.shop.index', array_filter(['category' => $category->slug, 'search' => $search, 'sort' => $sort])) }}"
                       class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium transition-colors {{ $currentCategory && $currentCategory->id === $category->id ? 'bg-brand-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>
        @endif

        {{-- Results Info --}}
        <p class="text-sm text-gray-500 mb-4">
            {{ $products->total() }} {{ Str::plural('product', $products->total()) }} found
            @if ($currentCategory)
                in <span class="font-medium text-gray-700">{{ $currentCategory->name }}</span>
            @endif
            @if ($search)
                for "<span class="font-medium text-gray-700">{{ $search }}</span>"
            @endif
        </p>

        {{-- Product Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse ($products as $product)
                <div class="group bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-md hover:border-gray-300 transition-all duration-150 flex flex-col">
                    {{-- Product Image --}}
                    <a href="{{ route('public.shop.show', $product) }}" class="block">
                        <div class="aspect-square bg-gray-100 overflow-hidden">
                            @if ($product->featured_image_url)
                                <img src="{{ $product->featured_image_url }}" alt="{{ $product->title }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-300">
                                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            @endif
                        </div>
                    </a>

                    {{-- Product Info --}}
                    <div class="p-4 flex flex-col flex-1">
                        <a href="{{ route('public.shop.show', $product) }}" class="block">
                            <h3 class="text-sm font-semibold text-gray-900 line-clamp-2 group-hover:text-brand-primary transition-colors">{{ $product->title }}</h3>
                        </a>

                        @if ($product->vendorProfile)
                            <p class="text-xs text-gray-500 mt-1">{{ $product->vendorProfile->business_name }}</p>
                        @endif

                        <div class="mt-2 flex items-baseline gap-2">
                            <span class="text-lg font-bold text-brand-primary">{{ $product->price_formatted }}</span>
                            @if ($product->member_price_cents && $product->member_price_cents < $product->price_cents)
                                <span class="text-xs font-medium text-brand-secondary">Member: {{ $product->member_price_formatted }}</span>
                            @endif
                        </div>

                        {{-- Stock Status --}}
                        @if ($product->track_stock && $product->stock_quantity <= 0)
                            <span class="inline-flex items-center mt-2 px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Out of Stock</span>
                        @elseif ($product->track_stock && $product->stock_quantity <= 5)
                            <span class="inline-flex items-center mt-2 px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Only {{ $product->stock_quantity }} left</span>
                        @endif

                        {{-- Add to Cart --}}
                        <div class="mt-auto pt-3">
                            @if (!$product->track_stock || $product->stock_quantity > 0)
                                <form action="{{ route('shop.cart.add') }}" method="POST" class="js-add-to-cart">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="w-full inline-flex justify-center items-center px-3 py-2 text-sm font-medium text-white bg-brand-secondary hover:bg-brand-secondary/90 rounded-md transition">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                                        Add to Cart
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-16">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <h3 class="text-lg font-medium text-gray-900">No products found</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        @if ($search)
                            Try adjusting your search terms or clearing filters.
                        @else
                            Check back soon for new products.
                        @endif
                    </p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if ($products->hasPages())
            <div class="mt-8">
                {{ $products->links() }}
            </div>
        @endif
    </div>
    @push('scripts')
    <script>
        document.querySelectorAll('.js-add-to-cart').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var btn = form.querySelector('button');
                var originalHtml = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>';

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        product_id: form.querySelector('[name="product_id"]').value,
                        quantity: form.querySelector('[name="quantity"]').value,
                    }),
                })
                .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
                .then(function (result) {
                    if (result.ok) {
                        btn.innerHTML = '<svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Added!';
                        btn.classList.remove('bg-brand-secondary');
                        btn.classList.add('bg-green-600');
                        // Update cart icon count if Livewire cart-icon exists
                        if (window.Livewire) {
                            window.Livewire.dispatch('cart-updated');
                        }
                        setTimeout(function () {
                            btn.innerHTML = originalHtml;
                            btn.classList.remove('bg-green-600');
                            btn.classList.add('bg-brand-secondary');
                            btn.disabled = false;
                        }, 2000);
                    } else {
                        btn.innerHTML = result.data.error || 'Error';
                        btn.classList.remove('bg-brand-secondary');
                        btn.classList.add('bg-red-600');
                        setTimeout(function () {
                            btn.innerHTML = originalHtml;
                            btn.classList.remove('bg-red-600');
                            btn.classList.add('bg-brand-secondary');
                            btn.disabled = false;
                        }, 2000);
                    }
                })
                .catch(function () {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                });
            });
        });
    </script>
    @endpush
</x-public-layout>
