<?php

namespace App\Http\Controllers;

use App\Models\GlossaryCategory;

class PublicGlossaryController extends Controller
{
    public function index()
    {
        $categories = GlossaryCategory::with(['publishedTerms' => function ($query) {
            $query->orderBy('term');
        }])
            ->withCount('publishedTerms')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $totalTerms = $categories->sum('published_terms_count');

        return view('public.glossary.index', compact('categories', 'totalTerms'));
    }
}
