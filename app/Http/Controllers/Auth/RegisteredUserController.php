<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\User;
use App\Notifications\Concerns\SafelyNotifies;
use App\Notifications\WelcomeNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    use SafelyNotifies;

    public function create(Request $request): View
    {
        $plan = null;
        if ($request->has('plan')) {
            $plan = Plan::visible()->find($request->input('plan'));
        }

        return view('auth.register', compact('plan'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'plan_id' => ['nullable', 'integer', 'exists:plans,id'],
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole('member');

        event(new Registered($user));

        $this->safeNotify($user, new WelcomeNotification($user));

        Auth::login($user);

        if ($request->filled('plan_id')) {
            $request->session()->put('pending_plan_id', (int) $request->input('plan_id'));
        }

        return redirect(route('dashboard', absolute: false));
    }
}
