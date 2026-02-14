<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicCertificateController extends Controller
{
    public function verify(Request $request, ?string $certificate_code = null): View
    {
        $certificate = null;
        $searched = false;

        $code = $certificate_code ?? $request->input('code');

        if ($code) {
            $searched = true;
            $code = trim($code);

            $certificate = Certificate::with('user:id,first_name,last_name')
                ->where('certificate_code', $code)
                ->first();
        }

        return view('public.certificate-verify', compact('certificate', 'searched', 'code'));
    }
}
