<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use Illuminate\View\View;

class TermsController extends Controller
{
    public function show(): View
    {
        $agreement = Agreement::getActiveTerms();

        return view('terms.show', compact('agreement'));
    }
}
