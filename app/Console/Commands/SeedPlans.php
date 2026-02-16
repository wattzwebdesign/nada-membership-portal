<?php

namespace App\Console\Commands;

use App\Enums\PlanType;
use App\Models\Plan;
use App\Services\StripeService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SeedPlans extends Command
{
    protected $signature = 'nada:seed-plans';

    protected $description = 'Seed the 18 membership and trainer plans and create corresponding Stripe products/prices';

    public function handle(StripeService $stripeService): int
    {
        $plans = [
            ['name' => 'NADA 1 Year',          'price_cents' => 10000, 'billing_interval_count' => 1, 'plan_type' => PlanType::Membership, 'discount_required' => null],
            ['name' => 'NADA 2 Years',          'price_cents' => 18500, 'billing_interval_count' => 2, 'plan_type' => PlanType::Membership, 'discount_required' => null],
            ['name' => 'NADA 3 Years',          'price_cents' => 27000, 'billing_interval_count' => 3, 'plan_type' => PlanType::Membership, 'discount_required' => null],
            ['name' => 'NADA Student 1 Year',   'price_cents' => 7000,  'billing_interval_count' => 1, 'plan_type' => PlanType::Student,    'discount_required' => 'student'],
            ['name' => 'NADA Student 2 Years',  'price_cents' => 12950, 'billing_interval_count' => 2, 'plan_type' => PlanType::Student,    'discount_required' => 'student'],
            ['name' => 'NADA Student 3 Years',  'price_cents' => 18900, 'billing_interval_count' => 3, 'plan_type' => PlanType::Student,    'discount_required' => 'student'],
            ['name' => 'NADA Senior 1 Year',    'price_cents' => 7000,  'billing_interval_count' => 1, 'plan_type' => PlanType::Senior,     'discount_required' => 'senior'],
            ['name' => 'NADA Senior 2 Years',   'price_cents' => 12950, 'billing_interval_count' => 2, 'plan_type' => PlanType::Senior,     'discount_required' => 'senior'],
            ['name' => 'NADA Senior 3 Years',   'price_cents' => 18900, 'billing_interval_count' => 3, 'plan_type' => PlanType::Senior,     'discount_required' => 'senior'],
            // Trainer plans
            ['name' => 'Registered Trainer 1 Year',          'price_cents' => 30000, 'billing_interval_count' => 1, 'plan_type' => PlanType::Trainer, 'discount_required' => null,      'role_required' => 'registered_trainer'],
            ['name' => 'Registered Trainer 2 Years',         'price_cents' => 55500, 'billing_interval_count' => 2, 'plan_type' => PlanType::Trainer, 'discount_required' => null,      'role_required' => 'registered_trainer'],
            ['name' => 'Registered Trainer 3 Years',         'price_cents' => 81000, 'billing_interval_count' => 3, 'plan_type' => PlanType::Trainer, 'discount_required' => null,      'role_required' => 'registered_trainer'],
            ['name' => 'Registered Trainer Student 1 Year',  'price_cents' => 21000, 'billing_interval_count' => 1, 'plan_type' => PlanType::Trainer, 'discount_required' => 'student', 'role_required' => 'registered_trainer'],
            ['name' => 'Registered Trainer Student 2 Years', 'price_cents' => 38850, 'billing_interval_count' => 2, 'plan_type' => PlanType::Trainer, 'discount_required' => 'student', 'role_required' => 'registered_trainer'],
            ['name' => 'Registered Trainer Student 3 Years', 'price_cents' => 56700, 'billing_interval_count' => 3, 'plan_type' => PlanType::Trainer, 'discount_required' => 'student', 'role_required' => 'registered_trainer'],
            ['name' => 'Registered Trainer Senior 1 Year',   'price_cents' => 21000, 'billing_interval_count' => 1, 'plan_type' => PlanType::Trainer, 'discount_required' => 'senior',  'role_required' => 'registered_trainer'],
            ['name' => 'Registered Trainer Senior 2 Years',  'price_cents' => 38850, 'billing_interval_count' => 2, 'plan_type' => PlanType::Trainer, 'discount_required' => 'senior',  'role_required' => 'registered_trainer'],
            ['name' => 'Registered Trainer Senior 3 Years',  'price_cents' => 56700, 'billing_interval_count' => 3, 'plan_type' => PlanType::Trainer, 'discount_required' => 'senior',  'role_required' => 'registered_trainer'],
        ];

        $created = 0;
        $skipped = 0;

        foreach ($plans as $index => $data) {
            if (Plan::where('name', $data['name'])->exists()) {
                $this->components->warn("Skipped: {$data['name']} (already exists)");
                $skipped++;
                continue;
            }

            $plan = Plan::create([
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'price_cents' => $data['price_cents'],
                'currency' => 'usd',
                'billing_interval' => 'year',
                'billing_interval_count' => $data['billing_interval_count'],
                'plan_type' => $data['plan_type'],
                'discount_required' => $data['discount_required'],
                'role_required' => $data['role_required'] ?? null,
                'is_visible' => true,
                'is_active' => true,
                'sort_order' => $index + 1,
            ]);

            $stripeService->createStripeProductAndPrice($plan);

            $this->components->info("Created: {$plan->name} → Stripe product {$plan->stripe_product_id}");
            $created++;
        }

        $this->newLine();
        $this->components->info("Done. Created: {$created}, Skipped: {$skipped}");

        return self::SUCCESS;
    }
}
