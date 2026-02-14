<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicCertificateController extends Controller
{
    /**
     * Public certificate verification page.
     * Allows anyone to look up a certificate by its code and verify its validity.
     */
    public function verify(Request $request): View
    {
        $certificate = null;
        $searched = false;

        if ($request->filled('code')) {
            $searched = true;
            $code = trim($request->input('code'));

            $certificate = Certificate::with('user:id,first_name,last_name')
                ->where('certificate_code', $code)
                ->first();
        }

        return view('public.certificate-verify', compact('certificate', 'searched'));
    }
}
