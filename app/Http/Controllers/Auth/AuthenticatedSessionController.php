<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        session()->flash('umami_event', 'User Login');

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // If impersonating, restore admin instead of logging out
        $impersonatorId = $request->session()->get('impersonator_id');
        if ($impersonatorId) {
            $admin = User::find($impersonatorId);

            if ($admin && $admin->hasRole('admin')) {
                Auth::login($admin);

                $request->session()->forget(['impersonator_id', 'impersonator_name']);
                Cookie::queue(Cookie::forget('impersonator_id'));

                Log::info('Impersonation stopped via logout', [
                    'admin_id' => $admin->id,
                ]);

                return redirect('/admin/users');
            }
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
