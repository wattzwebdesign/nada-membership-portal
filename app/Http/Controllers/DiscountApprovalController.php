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
     * Show the approval confirmation page (GET).
     */
    public function showApprove(Request $request, string $token): View
    {
        $discountRequest = DiscountRequest::where('approval_token', $token)->firstOrFail();

        if (!$discountRequest->isTokenValid()) {
            return view('discount-approvals.invalid', [
                'reason' => $discountRequest->status !== 'pending'
                    ? 'This discount request has already been ' . $discountRequest->status . '.'
                    : 'This approval link has expired.',
            ]);
        }

        return view('discount-approvals.confirm', [
            'discountRequest' => $discountRequest->load('user'),
            'action' => 'approve',
            'token' => $token,
        ]);
    }

    /**
     * Show the denial confirmation page (GET).
     */
    public function showDeny(Request $request, string $token): View
    {
        $discountRequest = DiscountRequest::where('approval_token', $token)->firstOrFail();

        if (!$discountRequest->isTokenValid()) {
            return view('discount-approvals.invalid', [
                'reason' => $discountRequest->status !== 'pending'
                    ? 'This discount request has already been ' . $discountRequest->status . '.'
                    : 'This approval link has expired.',
            ]);
        }

        return view('discount-approvals.confirm', [
            'discountRequest' => $discountRequest->load('user'),
            'action' => 'deny',
            'token' => $token,
        ]);
    }

    /**
     * Approve a discount request (POST).
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
        $user = $discountRequest->user;
        $user->discount_type = $discountRequest->discount_type;
        $user->discount_approved = true;
        $user->discount_approved_at = now();
        $user->save();

        $this->safeNotify($discountRequest->user, new DiscountApprovedNotification($discountRequest));

        return view('discount-approvals.approved', [
            'discountRequest' => $discountRequest->load('user'),
        ]);
    }

    /**
     * Deny a discount request (POST).
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
