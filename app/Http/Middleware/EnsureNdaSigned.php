<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNdaSigned
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && !$request->user()->hasSignedNda()) {
            if (!$request->routeIs('nda.*') && !$request->routeIs('logout')) {
                return redirect()->route('nda.show');
            }
        }

        return $next($request);
    }
}
