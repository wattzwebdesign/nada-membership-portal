<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $vendorProfile = $request->user()->vendorProfile;

        if (! $vendorProfile) {
            return view('store-vendor.products.index', [
                'products' => collect(),
                'needsProfile' => true,
            ]);
        }

        $products = $vendorProfile->products()
            ->orderByDesc('updated_at')
            ->paginate(20);

        return view('store-vendor.products.index', [
            'products' => $products,
            'needsProfile' => false,
        ]);
    }

    public function create(Request $request): View
    {
        $categories = ProductCategory::active()->orderBy('name')->get();

        return view('store-vendor.products.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $vendorProfile = $request->user()->vendorProfile;

        if (! $vendorProfile) {
            return redirect()->route('vendor.profile.edit')
                ->with('error', 'Please set up your vendor profile first.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'sku' => ['nullable', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0.01', 'max:99999.99'],
            'member_price' => ['nullable', 'numeric', 'min:0.01', 'max:99999.99'],
            'shipping_fee' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'product_category_id' => ['nullable', 'exists:product_categories,id'],
            'new_category' => ['nullable', 'string', 'max:255'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'track_stock' => ['boolean'],
            'is_digital' => ['boolean'],
            'status' => ['required', 'in:draft,active'],
            'images.*' => ['nullable', 'image', 'max:5120'],
            'digital_file' => ['nullable', 'file', 'max:51200'],
        ]);

        $categoryId = $this->resolveCategory($validated);

        $product = Product::create([
            'vendor_profile_id' => $vendorProfile->id,
            'product_category_id' => $categoryId,
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']) . '-' . Str::random(6),
            'description' => $validated['description'] ?? null,
            'sku' => $validated['sku'] ?? null,
            'price_cents' => (int) round($validated['price'] * 100),
            'member_price_cents' => isset($validated['member_price']) ? (int) round($validated['member_price'] * 100) : null,
            'shipping_fee_cents' => isset($validated['shipping_fee']) ? (int) round($validated['shipping_fee'] * 100) : null,
            'stock_quantity' => $validated['stock_quantity'],
            'track_stock' => $validated['track_stock'] ?? true,
            'is_digital' => $validated['is_digital'] ?? false,
            'status' => $validated['status'],
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $product->addMedia($image)->toMediaCollection('images');
            }
        }

        if ($request->hasFile('digital_file')) {
            $product->addMediaFromRequest('digital_file')->toMediaCollection('digital_file');
        }

        return redirect()->route('vendor.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function show(Request $request, Product $product): View
    {
        $this->authorizeVendorProduct($request, $product);

        return view('store-vendor.products.show', compact('product'));
    }

    public function edit(Request $request, Product $product): View
    {
        $this->authorizeVendorProduct($request, $product);

        $categories = ProductCategory::active()->orderBy('name')->get();

        return view('store-vendor.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeVendorProduct($request, $product);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'sku' => ['nullable', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0.01', 'max:99999.99'],
            'member_price' => ['nullable', 'numeric', 'min:0.01', 'max:99999.99'],
            'shipping_fee' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'product_category_id' => ['nullable', 'exists:product_categories,id'],
            'new_category' => ['nullable', 'string', 'max:255'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'track_stock' => ['boolean'],
            'is_digital' => ['boolean'],
            'status' => ['required', 'in:draft,active,archived'],
            'images.*' => ['nullable', 'image', 'max:5120'],
            'digital_file' => ['nullable', 'file', 'max:51200'],
        ]);

        $categoryId = $this->resolveCategory($validated);

        $product->update([
            'product_category_id' => $categoryId,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'sku' => $validated['sku'] ?? null,
            'price_cents' => (int) round($validated['price'] * 100),
            'member_price_cents' => isset($validated['member_price']) ? (int) round($validated['member_price'] * 100) : null,
            'shipping_fee_cents' => isset($validated['shipping_fee']) ? (int) round($validated['shipping_fee'] * 100) : null,
            'stock_quantity' => $validated['stock_quantity'],
            'track_stock' => $validated['track_stock'] ?? true,
            'is_digital' => $validated['is_digital'] ?? false,
            'status' => $validated['status'],
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $product->addMedia($image)->toMediaCollection('images');
            }
        }

        if ($request->hasFile('digital_file')) {
            $product->clearMediaCollection('digital_file');
            $product->addMediaFromRequest('digital_file')->toMediaCollection('digital_file');
        }

        return redirect()->route('vendor.products.edit', $product)
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeVendorProduct($request, $product);

        $product->delete();

        return redirect()->route('vendor.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    protected function resolveCategory(array $validated): ?int
    {
        if (! empty($validated['new_category'])) {
            $category = ProductCategory::firstOrCreate(
                ['slug' => Str::slug($validated['new_category'])],
                ['name' => $validated['new_category'], 'is_active' => true]
            );

            return $category->id;
        }

        return $validated['product_category_id'] ?? null;
    }

    protected function authorizeVendorProduct(Request $request, Product $product): void
    {
        $vendorProfile = $request->user()->vendorProfile;

        if (! $vendorProfile || $product->vendor_profile_id !== $vendorProfile->id) {
            abort(403, 'You do not own this product.');
        }
    }
}
