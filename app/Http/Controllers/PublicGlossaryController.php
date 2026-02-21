<?php

namespace App\Http\Controllers;

use App\Models\GlossaryCategory;
use App\Models\GlossaryTerm;

class PublicGlossaryController extends Controller
{
    public function index()
    {
        $categories = GlossaryCategory::orderBy('sort_order')->get();

        $terms = GlossaryTerm::published()
            ->with('category')
            ->orderBy('term')
            ->get();

        return view('public.glossary.index', [
            'categories' => $categories,
            'terms' => $terms,
            'totalTerms' => $terms->count(),
        ]);
    }
}
