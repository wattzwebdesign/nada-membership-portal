<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Enums\OrderStatus;
use App\Services\VendorPayoutService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected VendorPayoutService $vendorPayoutService,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $vendorProfile = $user->vendorProfile;

        if (! $vendorProfile) {
            return view('store-vendor.dashboard', [
                'vendor' => $user,
                'vendorProfile' => null,
                'recentOrders' => collect(),
                'stats' => [],
            ]);
        }

        $recentOrders = $vendorProfile->vendorOrderSplits()
            ->with('order')
            ->whereHas('order', fn ($q) => $q->where('status', '!=', OrderStatus::Pending))
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $totalOrders = $vendorProfile->vendorOrderSplits()
            ->whereHas('order', fn ($q) => $q->where('status', '!=', OrderStatus::Pending))
            ->count();

        $totalRevenue = $vendorProfile->vendorOrderSplits()
            ->where('status', '!=', 'canceled')
            ->sum('vendor_payout_cents');

        $activeProducts = $vendorProfile->products()->active()->count();

        $pendingShipments = $vendorProfile->vendorOrderSplits()
            ->whereNull('shipped_at')
            ->whereNull('canceled_at')
            ->whereHas('order', fn ($q) => $q->whereIn('status', [OrderStatus::Paid, OrderStatus::Processing]))
            ->count();

        $earningsReport = $this->vendorPayoutService->getEarningsReport($user);

        return view('store-vendor.dashboard', [
            'vendor' => $user,
            'vendorProfile' => $vendorProfile,
            'recentOrders' => $recentOrders,
            'stats' => [
                'total_orders' => $totalOrders,
                'total_revenue' => $totalRevenue,
                'active_products' => $activeProducts,
                'pending_shipments' => $pendingShipments,
            ],
            'earningsReport' => $earningsReport,
        ]);
    }
}
