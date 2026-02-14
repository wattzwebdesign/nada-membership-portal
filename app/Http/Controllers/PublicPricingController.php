<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicPricingController extends Controller
{
    /**
     * Show the public pricing page with all visible, active plans.
     */
    public function index(Request $request): View
    {
        $plans = Plan::visible()
            ->whereNull('discount_required')
            ->orderBy('sort_order')
            ->orderBy('price_cents')
            ->get();

        // Group plans by type for display
        $membershipPlans = $plans->where('plan_type', 'membership')->values();
        $trainerPlans = $plans->where('plan_type', 'trainer')->values();

        return view('public.pricing', compact('plans', 'membershipPlans', 'trainerPlans'));
    }
}
