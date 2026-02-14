<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsTrainer
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->isTrainer()) {
            abort(403, 'You must be a registered trainer to access this area.');
        }

        return $next($request);
    }
}
