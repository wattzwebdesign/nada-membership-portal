<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use App\Models\ResourceCategory;
use Illuminate\Http\Request;

class PublicResourceController extends Controller
{
    public function index()
    {
        $categories = ResourceCategory::withCount('publishedResources')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('public.resources.index', compact('categories'));
    }

    public function category(ResourceCategory $resourceCategory)
    {
        $resources = $resourceCategory->publishedResources()
            ->with('categories')
            ->orderByDesc('published_at')
            ->paginate(20);

        return view('public.resources.category', [
            'category' => $resourceCategory,
            'resources' => $resources,
        ]);
    }

    public function show(ResourceCategory $resourceCategory, Resource $resource)
    {
        // Verify resource belongs to this category
        if (! $resource->categories()->where('resource_category_id', $resourceCategory->id)->exists()) {
            abort(404);
        }

        $canViewFull = $resource->canViewFullContent();

        return view('public.resources.show', [
            'category' => $resourceCategory,
            'resource' => $resource,
            'canViewFull' => $canViewFull,
        ]);
    }
}
