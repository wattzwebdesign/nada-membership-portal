<?php

namespace App\Http\Controllers;

use App\Models\DiscountRequest;
use App\Notifications\Concerns\SafelyNotifies;
use App\Notifications\DiscountApprovedNotification;
use App\Notifications\DiscountDeniedNotification;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiscountApprovalController extends Controller
{
    use SafelyNotifies;
    /**
     * Approve a discount request via a token-based link (from admin email).
     */
    public function approve(Request $request, string $token): View
    {
        $discountRequest = DiscountRequest::where('approval_token', $token)->firstOrFail();

        if (!$discountRequest->isTokenValid()) {
            return view('discount-approvals.invalid', [
                'reason' => $discountRequest->status !== 'pending'
                    ? 'This discount request has already been ' . $discountRequest->status . '.'
                    : 'This approval link has expired.',
            ]);
        }

        $discountRequest->update([
            'status' => 'approved',
            'reviewed_at' => now(),
            'approval_token' => null,
            'token_expires_at' => null,
        ]);

        // Update the user's discount status
        $discountRequest->user->update([
            'discount_type' => $discountRequest->discount_type,
            'discount_approved' => true,
            'discount_approved_at' => now(),
        ]);

        $this->safeNotify($discountRequest->user, new DiscountApprovedNotification($discountRequest));

        return view('discount-approvals.approved', [
            'discountRequest' => $discountRequest->load('user'),
        ]);
    }

    /**
     * Deny a discount request via a token-based link (from admin email).
     */
    public function deny(Request $request, string $token): View
    {
        $discountRequest = DiscountRequest::where('approval_token', $token)->firstOrFail();

        if (!$discountRequest->isTokenValid()) {
            return view('discount-approvals.invalid', [
                'reason' => $discountRequest->status !== 'pending'
                    ? 'This discount request has already been ' . $discountRequest->status . '.'
                    : 'This approval link has expired.',
            ]);
        }

        $discountRequest->update([
            'status' => 'denied',
            'reviewed_at' => now(),
            'approval_token' => null,
            'token_expires_at' => null,
        ]);

        $this->safeNotify($discountRequest->user, new DiscountDeniedNotification($discountRequest));

        return view('discount-approvals.denied', [
            'discountRequest' => $discountRequest->load('user'),
        ]);
    }
}
