<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class HandleImpersonation
{
    public function handle(Request $request, Closure $next): Response
    {
        // Session-timeout recovery: not authenticated but cookie exists → restore admin
        if (! Auth::check() && $request->cookie('impersonator_id')) {
            $admin = User::find($request->cookie('impersonator_id'));

            if ($admin && $admin->hasRole('admin')) {
                Auth::login($admin);
                $request->session()->forget(['impersonator_id', 'impersonator_name']);
                Cookie::queue(Cookie::forget('impersonator_id'));

                Log::info('Impersonation auto-restored after session timeout', [
                    'admin_id' => $admin->id,
                ]);

                return redirect('/admin/users');
            }

            // Invalid cookie — clean up
            Cookie::queue(Cookie::forget('impersonator_id'));
        }

        // Share impersonation state with all Blade views
        $isImpersonating = $request->session()->has('impersonator_id');
        View::share('isImpersonating', $isImpersonating);

        return $next($request);
    }
}
