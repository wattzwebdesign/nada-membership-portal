<?php

namespace App\Console\Commands;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportSubscriptions extends Command
{
    protected $signature = 'nada:import-subscriptions
        {--limit=0 : Limit number of subscriptions to import (0 = all)}
        {--dry-run : Preview what would happen without writing to DB}';

    protected $description = 'Import customers and subscriptions from Stripe audit data';

    public function handle(): int
    {
        // Belt-and-suspenders: ensure no emails are sent during import
        config(['mail.default' => 'log']);

        $dryRun = $this->option('dry-run');
        $limit = (int) $this->option('limit');

        if ($dryRun) {
            $this->components->warn('DRY RUN — no database writes will be made.');
        }

        if (!Storage::exists('migration/stripe-audit.json')) {
            $this->error('Run nada:stripe-audit first.');
            return Command::FAILURE;
        }

        $audit = json_decode(Storage::get('migration/stripe-audit.json'), true);
        $subscriptions = collect($audit['subscriptions'])->where('status', 'active');
        $customers = collect($audit['customers'])->keyBy('id');
        $prices = collect($audit['prices'])->keyBy('id');

        // Build price-to-plan mapping
        $priceMap = $this->buildPriceMap($subscriptions, $prices);

        // Show mapping summary
        $this->showMappingSummary($priceMap);

        $unmappedCount = collect($priceMap)->whereNull('plan_id')->count();
        if ($unmappedCount > 0) {
            $this->components->warn("{$unmappedCount} price(s) could not be mapped to a plan — those subscriptions will be skipped.");
        }

        // Apply limit
        if ($limit > 0) {
            $subscriptions = $subscriptions->take($limit);
            $this->components->info("Limited to {$limit} subscriptions.");
        }

        $importedUsers = 0;
        $importedSubs = 0;
        $skippedExisting = 0;
        $skippedUnmapped = 0;
        $errors = 0;
        $importLog = [];

        $bar = $this->output->createProgressBar($subscriptions->count());

        foreach ($subscriptions as $sub) {
            try {
                $customer = $customers->get($sub['customer']);
                if (!$customer || !$customer['email']) {
                    $errors++;
                    $bar->advance();
                    continue;
                }

                // Check price mapping — skip if unmapped
                $mapping = $priceMap[$sub['price_id']] ?? null;
                if (!$mapping || !$mapping['plan_id']) {
                    $skippedUnmapped++;
                    $bar->advance();
                    continue;
                }

                $planId = $mapping['plan_id'];
                $planType = $mapping['plan_type'];

                if ($dryRun) {
                    $importedSubs++;
                    $bar->advance();
                    continue;
                }

                // Create or find user
                $user = User::where('email', $customer['email'])->first();
                if (!$user) {
                    $nameParts = $this->parseName($customer['name'] ?? '');
                    $user = User::create([
                        'first_name' => $nameParts['first'],
                        'last_name' => $nameParts['last'],
                        'email' => $customer['email'],
                        'password' => Hash::make(Str::random(24)),
                        'email_verified_at' => now(),
                    ]);
                    $user->stripe_customer_id = $customer['id'];
                    $user->save();
                    $user->assignRole('member');
                    $importedUsers++;
                    $importLog[] = ['type' => 'user', 'id' => $user->id];
                } elseif (!$user->stripe_customer_id) {
                    $user->stripe_customer_id = $customer['id'];
                    $user->save();
                }

                // Create subscription
                if (Subscription::where('stripe_subscription_id', $sub['id'])->exists()) {
                    $skippedExisting++;
                    $bar->advance();
                    continue;
                }

                $subscription = Subscription::create([
                    'user_id' => $user->id,
                    'plan_id' => $planId,
                    'stripe_subscription_id' => $sub['id'],
                    'stripe_price_id' => $sub['price_id'] ?? '',
                    'status' => $sub['status'],
                    'current_period_start' => isset($sub['current_period_start'])
                        ? Carbon::createFromTimestamp($sub['current_period_start'])
                        : null,
                    'current_period_end' => isset($sub['current_period_end'])
                        ? Carbon::createFromTimestamp($sub['current_period_end'])
                        : null,
                ]);

                // Assign trainer role if on a trainer plan
                if ($planType === 'trainer' && !$user->hasRole('registered_trainer')) {
                    $user->assignRole('registered_trainer');
                }

                $importedSubs++;
                $importLog[] = ['type' => 'subscription', 'id' => $subscription->id];
            } catch (\Exception $e) {
                $errors++;
                $this->error("Error processing sub {$sub['id']}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Save import log (skip in dry-run mode)
        if (!$dryRun) {
            Storage::put('migration/import-subscriptions-log.json', json_encode($importLog, JSON_PRETTY_PRINT));
        }

        $this->info(($dryRun ? 'DRY RUN ' : '') . 'Import complete:');
        $this->line("  Users created: {$importedUsers}");
        $this->line("  Subscriptions imported: {$importedSubs}");
        $this->line("  Skipped (existing): {$skippedExisting}");
        $this->line("  Skipped (unmapped price): {$skippedUnmapped}");
        $this->line("  Errors: {$errors}");

        return Command::SUCCESS;
    }

    /**
     * Build a mapping of old Stripe price IDs → local Plan records.
     * Matches by price_cents + billing_interval_count. When multiple plans
     * match (student/senior same price), defaults to student.
     */
    private function buildPriceMap($subscriptions, $prices): array
    {
        $activePriceIds = $subscriptions->pluck('price_id')->unique();
        $priceMap = [];

        foreach ($activePriceIds as $priceId) {
            $price = $prices->get($priceId);

            if (!$price) {
                $priceMap[$priceId] = [
                    'price_id' => $priceId,
                    'product_name' => '(price not found in audit)',
                    'plan_type' => null,
                    'amount' => null,
                    'interval_count' => null,
                    'plan_id' => null,
                    'plan_name' => null,
                    'note' => 'not in audit',
                ];
                continue;
            }

            $productName = $price['product_name'] ?? 'Unknown';
            $isTrainer = str_contains(strtolower($productName), 'trainer');
            $amount = $price['unit_amount'] ?? 0;

            // Normalize month-based intervals to years
            $interval = $price['recurring_interval'] ?? 'year';
            $intervalCount = $price['recurring_interval_count'] ?? 1;
            if ($interval === 'month') {
                $intervalCount = (int) round($intervalCount / 12);
            }

            // Match by price + interval, scoped to trainer vs non-trainer
            $query = Plan::where('price_cents', $amount)
                ->where('billing_interval_count', $intervalCount);

            if ($isTrainer) {
                $query->where('plan_type', 'trainer');
            } else {
                $query->where('plan_type', '!=', 'trainer');
            }

            $matches = $query->get();
            $plan = null;
            $note = null;

            if ($matches->count() === 1) {
                $plan = $matches->first();
            } elseif ($matches->count() > 1) {
                // Ambiguous (student/senior) — default to student
                $plan = $matches->where('discount_required', 'student')->first()
                    ?? $matches->first();
                $note = 'ambiguous → defaulted to student';
            }

            $priceMap[$priceId] = [
                'price_id' => $priceId,
                'product_name' => $productName,
                'plan_type' => $plan?->plan_type?->value,
                'amount' => $amount,
                'interval_count' => $intervalCount,
                'plan_id' => $plan?->id,
                'plan_name' => $plan?->name,
                'note' => $note,
            ];
        }

        return $priceMap;
    }

    private function showMappingSummary(array $priceMap): void
    {
        $this->newLine();
        $this->components->info('Price → Plan Mapping:');

        $rows = [];
        foreach ($priceMap as $entry) {
            $rows[] = [
                $entry['price_id'],
                $entry['product_name'],
                $entry['amount'] !== null ? '$' . number_format($entry['amount'] / 100, 2) : '—',
                $entry['interval_count'] ? $entry['interval_count'] . 'yr' : '—',
                $entry['plan_name'] ?? '<UNMAPPED>',
                $entry['note'] ?? '',
            ];
        }

        $this->table(
            ['Old Price ID', 'Product Name', 'Amount', 'Interval', 'Mapped Plan', 'Note'],
            $rows
        );
    }

    private function parseName(?string $name): array
    {
        if (!$name) {
            return ['first' => 'Unknown', 'last' => 'User'];
        }

        $parts = explode(' ', trim($name), 2);
        return [
            'first' => $parts[0] ?? 'Unknown',
            'last' => $parts[1] ?? '',
        ];
    }
}
