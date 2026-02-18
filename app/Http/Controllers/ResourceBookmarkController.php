<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use Illuminate\Http\Request;

class ResourceBookmarkController extends Controller
{
    public function index(Request $request)
    {
        $resources = $request->user()
            ->bookmarkedResources()
            ->with('categories')
            ->orderByPivot('created_at', 'desc')
            ->paginate(20);

        return view('bookmarks.index', compact('resources'));
    }

    public function toggle(Request $request, Resource $resource)
    {
        $user = $request->user();

        if ($user->bookmarkedResources()->where('resource_id', $resource->id)->exists()) {
            $user->bookmarkedResources()->detach($resource->id);
            $bookmarked = false;
        } else {
            $user->bookmarkedResources()->attach($resource->id);
            $bookmarked = true;
        }

        return response()->json(['bookmarked' => $bookmarked]);
    }
}
