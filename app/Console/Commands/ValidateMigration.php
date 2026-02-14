<?php

namespace App\Console\Commands;

use App\Models\Certificate;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Console\Command;

class ValidateMigration extends Command
{
    protected $signature = 'nada:validate-migration';
    protected $description = 'Run validation checks on migrated data';

    public function handle(): int
    {
        $this->info('Running migration validation checks...');
        $passed = 0;
        $failed = 0;

        // Check 1: Every subscription has a valid plan_id
        $this->info('Check 1: Subscriptions have valid plan_id...');
        $orphanedSubs = Subscription::whereNull('plan_id')->orWhere('plan_id', 0)->count();
        if ($orphanedSubs === 0) {
            $this->line('  PASS');
            $passed++;
        } else {
            $this->error("  FAIL: {$orphanedSubs} subscriptions with invalid plan_id");
            $failed++;
        }

        // Check 2: Every user with a subscription has a stripe_customer_id
        $this->info('Check 2: Users with subscriptions have stripe_customer_id...');
        $usersWithoutStripe = User::whereHas('subscriptions')
            ->whereNull('stripe_customer_id')
            ->count();
        if ($usersWithoutStripe === 0) {
            $this->line('  PASS');
            $passed++;
        } else {
            $this->error("  FAIL: {$usersWithoutStripe} users missing stripe_customer_id");
            $failed++;
        }

        // Check 3: All certificate codes are unique
        $this->info('Check 3: Certificate codes are unique...');
        $totalCerts = Certificate::count();
        $uniqueCodes = Certificate::distinct('certificate_code')->count('certificate_code');
        if ($totalCerts === $uniqueCodes) {
            $this->line('  PASS');
            $passed++;
        } else {
            $this->error("  FAIL: {$totalCerts} certs but only {$uniqueCodes} unique codes");
            $failed++;
        }

        // Check 4: Certificate expiration dates align with subscription
        $this->info('Check 4: Certificate expirations match subscriptions...');
        $mismatches = 0;
        $certsWithUsers = Certificate::with('user.activeSubscription')->where('status', 'active')->get();
        foreach ($certsWithUsers as $cert) {
            $sub = $cert->user?->activeSubscription;
            if ($sub && $cert->expiration_date && $sub->current_period_end) {
                if ($cert->expiration_date->toDateString() !== $sub->current_period_end->toDateString()) {
                    $mismatches++;
                }
            }
        }
        if ($mismatches === 0) {
            $this->line('  PASS');
            $passed++;
        } else {
            $this->warn("  WARN: {$mismatches} certificate expiration mismatches");
            $failed++;
        }

        // Check 5: Plan mapping covers all active prices
        $this->info('Check 5: All active Stripe prices mapped...');
        $activePriceIds = Subscription::active()->pluck('stripe_price_id')->unique();
        $mappedPriceIds = Plan::pluck('stripe_price_id');
        $unmapped = $activePriceIds->diff($mappedPriceIds);
        if ($unmapped->isEmpty()) {
            $this->line('  PASS');
            $passed++;
        } else {
            $this->error("  FAIL: {$unmapped->count()} unmapped price IDs: " . $unmapped->implode(', '));
            $failed++;
        }

        // Check 6: Role assignments for trainer plans
        $this->info('Check 6: Users on trainer plans have trainer role...');
        $trainerPlanIds = Plan::where('plan_type', 'trainer')->pluck('id');
        $usersOnTrainerPlans = Subscription::active()
            ->whereIn('plan_id', $trainerPlanIds)
            ->pluck('user_id')
            ->unique();
        $missingRole = 0;
        foreach ($usersOnTrainerPlans as $userId) {
            $user = User::find($userId);
            if ($user && !$user->hasRole('registered_trainer')) {
                $missingRole++;
            }
        }
        if ($missingRole === 0) {
            $this->line('  PASS');
            $passed++;
        } else {
            $this->error("  FAIL: {$missingRole} users on trainer plans without trainer role");
            $failed++;
        }

        // Check 7: No orphaned subscriptions
        $this->info('Check 7: No orphaned subscriptions...');
        $orphanedSubs = Subscription::whereDoesntHave('user')->count();
        if ($orphanedSubs === 0) {
            $this->line('  PASS');
            $passed++;
        } else {
            $this->error("  FAIL: {$orphanedSubs} orphaned subscriptions");
            $failed++;
        }

        // Check 8: No orphaned certificates
        $this->info('Check 8: No orphaned certificates...');
        $orphanedCerts = Certificate::whereDoesntHave('user')->count();
        if ($orphanedCerts === 0) {
            $this->line('  PASS');
            $passed++;
        } else {
            $this->error("  FAIL: {$orphanedCerts} orphaned certificates");
            $failed++;
        }

        // Check 9: User count sanity
        $this->info('Check 9: User count sanity...');
        $usersWithSubs = User::whereHas('subscriptions', fn ($q) => $q->active())->count();
        $activeSubs = Subscription::active()->count();
        $this->line("  Users with active subs: {$usersWithSubs}, Active subs: {$activeSubs}");
        $passed++;

        $this->newLine();
        $this->info("Validation complete: {$passed} passed, {$failed} failed");

        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
