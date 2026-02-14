<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\AgreementSignature;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NdaController extends Controller
{
    public function show(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        $agreement = Agreement::getActiveNda();

        // If no active NDA exists, just let them through
        if (!$agreement) {
            return redirect()->route('dashboard');
        }

        return view('auth.nda', [
            'agreement' => $agreement,
        ]);
    }

    public function accept(Request $request): \Illuminate\Http\RedirectResponse
    {
        $agreement = Agreement::getActiveNda();

        if (!$agreement) {
            return redirect()->route('dashboard');
        }

        AgreementSignature::create([
            'user_id' => $request->user()->id,
            'agreement_id' => $agreement->id,
            'signed_at' => now(),
            'ip_address' => $request->ip(),
        ]);

        $request->user()->update(['nda_accepted_at' => now()]);

        return redirect()->route('dashboard')->with('success', 'Thank you for accepting the agreement.');
    }
}
