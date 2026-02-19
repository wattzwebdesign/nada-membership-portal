<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\VendorProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();
        $vendorProfile = $user->vendorProfile;

        return view('store-vendor.profile', compact('user', 'vendorProfile'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'url', 'max:255'],
            'default_shipping_fee' => ['required', 'numeric', 'min:0', 'max:999.99'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'gallery.*' => ['nullable', 'image', 'max:5120'],
        ]);

        $vendorProfile = $user->vendorProfile;

        $profileData = [
            'business_name' => $validated['business_name'],
            'description' => $validated['description'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'website' => $validated['website'] ?? null,
            'default_shipping_fee_cents' => (int) round($validated['default_shipping_fee'] * 100),
        ];

        if ($vendorProfile) {
            $vendorProfile->update($profileData);
        } else {
            $profileData['user_id'] = $user->id;
            $profileData['slug'] = Str::slug($validated['business_name']) . '-' . $user->id;
            $vendorProfile = VendorProfile::create($profileData);
        }

        if ($request->hasFile('logo')) {
            $vendorProfile->clearMediaCollection('logo');
            $vendorProfile->addMediaFromRequest('logo')->toMediaCollection('logo');
        }

        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $image) {
                $vendorProfile->addMedia($image)->toMediaCollection('gallery');
            }
        }

        return redirect()->route('vendor.profile.edit')
            ->with('success', 'Vendor profile updated successfully.');
    }
}
