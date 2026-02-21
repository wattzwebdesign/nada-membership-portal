<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventPricingPackage;
use App\Models\User;

class EventPricingCalculator
{
    /**
     * Calculate pricing for selected packages.
     *
     * @param  array  $selectedPackageIds  Array of package IDs
     * @param  bool  $isMember  Whether to apply member pricing
     * @return array{line_items: array, total_cents: int}
     */
    public function calculate(array $selectedPackageIds, bool $isMember = false): array
    {
        $lineItems = [];
        $totalCents = 0;

        $packages = EventPricingPackage::with('category')
            ->whereIn('id', $selectedPackageIds)
            ->get();

        foreach ($packages as $package) {
            $priceCents = $package->getCurrentPrice($isMember);
            $isEarlyBird = $package->isEarlyBird();

            $lineItems[] = [
                'package_id' => $package->id,
                'category_id' => $package->event_pricing_category_id,
                'category_name' => $package->category->name,
                'package_name' => $package->name,
                'price_cents' => $priceCents,
                'is_member_pricing' => $isMember && $package->member_price_cents !== null,
                'is_early_bird' => $isEarlyBird,
            ];

            $totalCents += $priceCents;
        }

        return [
            'line_items' => $lineItems,
            'total_cents' => $totalCents,
        ];
    }

    /**
     * Validate that required categories have a selection.
     */
    public function validateRequiredCategories(Event $event, array $selectedPackageIds): array
    {
        $errors = [];

        $requiredCategories = $event->pricingCategories()
            ->where('is_required', true)
            ->where('is_active', true)
            ->with('packages')
            ->get();

        $selectedPackages = EventPricingPackage::whereIn('id', $selectedPackageIds)->get();
        $selectedCategoryIds = $selectedPackages->pluck('event_pricing_category_id')->unique();

        foreach ($requiredCategories as $category) {
            if (! $selectedCategoryIds->contains($category->id)) {
                $errors[] = "Please select an option for \"{$category->name}\".";
            }
        }

        return $errors;
    }
}
