<?php

namespace App\Http\Controllers;

use App\Services\GeocodingService;
use App\Services\StripeService;
use App\Services\WalletPassService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function __construct(
        protected StripeService $stripeService,
        protected GeocodingService $geocodingService,
    ) {}

    /**
     * Show the account/profile edit form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('account.edit', compact('user'));
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'zip' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'size:2'],
            'bio' => ['nullable', 'string', 'max:2000'],
        ]);

        $emailChanged = $user->email !== $validated['email'];

        $user->update($validated);

        // Geocode if address fields changed
        if ($user->wasChanged(['city', 'state', 'zip', 'country'])) {
            $address = $this->geocodingService->buildAddressString(
                $user->city,
                $user->state,
                $user->zip,
                $user->country,
            );

            if ($address) {
                $coordinates = $this->geocodingService->geocode($address);
                $user->update([
                    'latitude' => $coordinates['latitude'] ?? null,
                    'longitude' => $coordinates['longitude'] ?? null,
                ]);
            }
        }

        // If the email changed, mark it as unverified and sync with Stripe
        if ($emailChanged) {
            $user->update(['email_verified_at' => null]);
            $user->sendEmailVerificationNotification();
        }

        // Sync updated name/email to Stripe if the user has a Stripe customer ID
        if ($user->stripe_customer_id) {
            try {
                $this->stripeService->updateCustomer($user);
            } catch (\Stripe\Exception\ApiErrorException $e) {
                // Log the error but don't block the profile update
                logger()->warning('Failed to sync user profile to Stripe', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Update wallet passes if name changed
        if ($user->wasChanged(['first_name', 'last_name'])) {
            app(WalletPassService::class)->updateAllPassesForUser($user);
        }

        return redirect()->route('account.edit')
            ->with('success', 'Your profile has been updated.');
    }
}
