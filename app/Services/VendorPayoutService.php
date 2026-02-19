<?php

namespace App\Services;

use App\Models\PayoutSetting;
use App\Models\User;
use App\Models\VendorOrderSplit;
use Carbon\Carbon;

class VendorPayoutService
{
    public function calculateSplit(int $amountCents, int $vendorUserId): array
    {
        $settings = PayoutSetting::getForVendor($vendorUserId);

        $platformAmount = (int) round($amountCents * ($settings->platform_percentage / 100));
        $vendorAmount = $amountCents - $platformAmount;

        return [
            'total' => $amountCents,
            'platform_amount' => $platformAmount,
            'vendor_amount' => $vendorAmount,
            'platform_percentage' => $settings->platform_percentage,
            'vendor_percentage' => $settings->payee_percentage,
        ];
    }

    public function getEarningsReport(User $user, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $vendorProfile = $user->vendorProfile;

        if (! $vendorProfile) {
            return [
                'total_revenue' => 0,
                'platform_fees' => 0,
                'vendor_earnings' => 0,
                'per_order' => collect(),
                'period' => ['from' => $from?->toDateString(), 'to' => $to?->toDateString()],
            ];
        }

        $query = VendorOrderSplit::where('vendor_profile_id', $vendorProfile->id)
            ->where('status', '!=', 'canceled');

        if ($from) {
            $query->where('created_at', '>=', $from);
        }
        if ($to) {
            $query->where('created_at', '<=', $to);
        }

        $splits = $query->with('order')->get();

        $totalRevenue = $splits->sum('subtotal_cents');
        $platformFees = $splits->sum('platform_fee_cents');
        $vendorEarnings = $splits->sum('vendor_payout_cents');

        return [
            'total_revenue' => $totalRevenue,
            'platform_fees' => $platformFees,
            'vendor_earnings' => $vendorEarnings,
            'per_order' => $splits->map(fn ($split) => [
                'order_id' => $split->order_id,
                'order_number' => $split->order->order_number ?? 'N/A',
                'subtotal' => $split->subtotal_cents,
                'platform_fee' => $split->platform_fee_cents,
                'vendor_payout' => $split->vendor_payout_cents,
                'status' => $split->status,
                'date' => $split->created_at->toDateString(),
            ])->values(),
            'period' => [
                'from' => $from?->toDateString(),
                'to' => $to?->toDateString(),
            ],
        ];
    }
}
