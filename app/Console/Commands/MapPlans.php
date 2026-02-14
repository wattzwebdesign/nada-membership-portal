<?php

namespace App\Console\Commands;

use App\Models\Plan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MapPlans extends Command
{
    protected $signature = 'nada:map-plans';
    protected $description = 'Create Plan records from Stripe audit data';

    public function handle(): int
    {
        if (!Storage::exists('migration/stripe-audit.json')) {
            $this->error('Run nada:stripe-audit first.');
            return Command::FAILURE;
        }

        $audit = json_decode(Storage::get('migration/stripe-audit.json'), true);
        $prices = collect($audit['prices']);
        $subscriptions = collect($audit['subscriptions']);

        // Find prices that have active subscriptions
        $activePriceIds = $subscriptions->where('status', 'active')->pluck('price_id')->unique();

        $created = 0;
        $skipped = 0;

        foreach ($prices as $price) {
            if (Plan::where('stripe_price_id', $price['id'])->exists()) {
                $skipped++;
                continue;
            }

            $productName = $price['product_name'] ?? 'Unknown';
            $planType = $this->inferPlanType($productName);
            $hasActiveSubs = $activePriceIds->contains($price['id']);

            $name = $productName;
            if ($price['recurring_interval_count'] && $price['recurring_interval_count'] > 1) {
                $name .= " — {$price['recurring_interval_count']} Year";
            } elseif ($price['recurring_interval'] === 'year') {
                $name .= ' — 1 Year';
            }

            Plan::create([
                'name' => $name,
                'slug' => Str::slug($name . '-' . Str::random(4)),
                'stripe_product_id' => $price['product_id'],
                'stripe_price_id' => $price['id'],
                'price_cents' => $price['unit_amount'] ?? 0,
                'currency' => $price['currency'] ?? 'usd',
                'billing_interval' => $price['recurring_interval'] ?? 'year',
                'billing_interval_count' => $price['recurring_interval_count'] ?? 1,
                'plan_type' => $planType,
                'role_required' => $planType === 'trainer' ? 'registered_trainer' : null,
                'discount_required' => in_array($planType, ['student', 'senior']) ? $planType : null,
                'is_visible' => $planType !== 'comped',
                'is_active' => $price['active'] ?? true,
                'sort_order' => 0,
            ]);
            $created++;
        }

        $this->info("Plans created: {$created}, skipped (already exist): {$skipped}");
        return Command::SUCCESS;
    }

    private function inferPlanType(string $productName): string
    {
        $lower = strtolower($productName);

        if (str_contains($lower, 'comped')) return 'comped';
        if (str_contains($lower, 'trainer') || str_contains($lower, 'registered trainer')) return 'trainer';
        if (str_contains($lower, 'senior')) return 'senior';
        if (str_contains($lower, 'student')) return 'student';

        return 'membership';
    }
}
