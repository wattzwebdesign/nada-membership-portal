<?php

namespace App\Http\Middleware;

use App\Models\Agreement;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNdaSigned
{
    public function handle(Request $request, Closure $next): Response
    {
        // Skip NDA check when admin is impersonating a user
        if (session()->has('impersonator_id')) {
            return $next($request);
        }

        if ($request->user() && $request->user()->isCustomerOnly()) {
            return $next($request);
        }

        if ($request->user() && !$request->user()->hasSignedNda()) {
            // Only enforce if there's actually an active NDA published
            if (Agreement::getActiveNda()) {
                if (!$request->routeIs('nda.*') && !$request->routeIs('logout')) {
                    return redirect()->route('nda.show');
                }
            }
        }

        return $next($request);
    }
}
