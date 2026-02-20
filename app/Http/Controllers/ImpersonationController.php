<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;

class ImpersonationController extends Controller
{
    public function stop(Request $request)
    {
        $impersonatorId = $request->session()->get('impersonator_id')
            ?? $request->cookie('impersonator_id');

        if (! $impersonatorId) {
            return redirect('/admin/users');
        }

        $admin = User::find($impersonatorId);

        if (! $admin || ! $admin->hasRole('admin')) {
            $request->session()->forget(['impersonator_id', 'impersonator_name']);
            Cookie::queue(Cookie::forget('impersonator_id'));

            return redirect('/admin/users');
        }

        $impersonatedName = Auth::user()?->first_name . ' ' . Auth::user()?->last_name;

        Auth::login($admin);

        $request->session()->forget(['impersonator_id', 'impersonator_name']);
        Cookie::queue(Cookie::forget('impersonator_id'));

        Log::info('Impersonation stopped', [
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
            'was_impersonating' => $impersonatedName,
        ]);

        return redirect('/admin/users');
    }
}
