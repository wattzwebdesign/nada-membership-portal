<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\VendorProfile;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicShopController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::active()->inStock()
            ->with(['vendorProfile', 'media'])
            ->whereHas('vendorProfile', fn ($q) => $q->active());

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $currentCategory = null;
        if ($categorySlug = $request->input('category')) {
            $currentCategory = ProductCategory::where('slug', $categorySlug)->first();
            if ($currentCategory) {
                $query->where('product_category_id', $currentCategory->id);
            }
        }

        $sort = $request->input('sort', 'newest');
        $query = match ($sort) {
            'price_low' => $query->orderBy('price_cents', 'asc'),
            'price_high' => $query->orderBy('price_cents', 'desc'),
            default => $query->orderByDesc('created_at'),
        };

        $products = $query->paginate(24)->withQueryString();
        $categories = ProductCategory::active()->orderBy('sort_order')->orderBy('name')->get();

        return view('public.shop.index', [
            'products' => $products,
            'categories' => $categories,
            'search' => $search,
            'sort' => $sort,
            'currentCategory' => $currentCategory,
        ]);
    }

    public function category(ProductCategory $category): View
    {
        $products = Product::active()->inStock()
            ->where('product_category_id', $category->id)
            ->with(['vendorProfile', 'media'])
            ->whereHas('vendorProfile', fn ($q) => $q->active())
            ->orderByDesc('created_at')
            ->paginate(24);

        $categories = ProductCategory::active()->orderBy('sort_order')->orderBy('name')->get();

        return view('public.shop.index', [
            'products' => $products,
            'categories' => $categories,
            'search' => null,
            'sort' => 'newest',
            'currentCategory' => $category,
        ]);
    }

    public function vendor(VendorProfile $vendorProfile): View
    {
        $products = $vendorProfile->products()
            ->active()->inStock()
            ->with('media')
            ->orderByDesc('created_at')
            ->paginate(24);

        return view('public.shop.vendor', compact('vendorProfile', 'products'));
    }

    public function show(Product $product): View
    {
        $product->load(['vendorProfile', 'category', 'media']);

        if ($product->status->value !== 'active') {
            abort(404);
        }

        return view('public.shop.show', compact('product'));
    }
}
