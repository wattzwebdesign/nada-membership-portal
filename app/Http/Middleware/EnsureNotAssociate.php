<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotAssociate
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->hasAssociatePlan()) {
            return redirect()->route('membership.index')
                ->with('error', 'This feature is not available on the Associate plan.');
        }

        return $next($request);
    }
}
