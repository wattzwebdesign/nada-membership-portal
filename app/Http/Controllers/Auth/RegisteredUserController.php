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
use Illuminate\Support\Facades\Cache;
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
            'organization' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'size:2'],
            'zip' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:2'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'plan_id' => ['nullable', 'integer', 'exists:plans,id'],
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'organization' => $request->organization,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'address_line_1' => $request->address_line_1,
            'address_line_2' => $request->address_line_2,
            'city' => $request->city,
            'state' => $request->state,
            'zip' => $request->zip,
            'country' => $request->country,
        ]);

        $user->assignRole('member');

        event(new Registered($user));

        $this->safeNotify($user, new WelcomeNotification($user));

        Auth::login($user);

        if ($request->filled('plan_id')) {
            Cache::put("pending_plan:{$user->id}", (int) $request->input('plan_id'), now()->addHours(24));
        }

        session()->flash('umami_event', 'User Registration');

        return redirect(route('dashboard', absolute: false));
    }
}
