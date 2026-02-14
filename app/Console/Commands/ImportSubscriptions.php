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
    protected $signature = 'nada:import-subscriptions';
    protected $description = 'Import customers and subscriptions from Stripe audit data';

    public function handle(): int
    {
        if (!Storage::exists('migration/stripe-audit.json')) {
            $this->error('Run nada:stripe-audit first.');
            return Command::FAILURE;
        }

        $audit = json_decode(Storage::get('migration/stripe-audit.json'), true);
        $subscriptions = collect($audit['subscriptions'])->where('status', 'active');
        $customers = collect($audit['customers'])->keyBy('id');

        $importedUsers = 0;
        $importedSubs = 0;
        $skipped = 0;
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

                // Create or find user
                $user = User::where('email', $customer['email'])->first();
                if (!$user) {
                    $nameParts = $this->parseName($customer['name'] ?? '');
                    $user = User::create([
                        'first_name' => $nameParts['first'],
                        'last_name' => $nameParts['last'],
                        'email' => $customer['email'],
                        'password' => Hash::make(Str::random(24)),
                        'stripe_customer_id' => $customer['id'],
                        'email_verified_at' => now(),
                    ]);
                    $user->assignRole('member');
                    $importedUsers++;
                    $importLog[] = ['type' => 'user', 'id' => $user->id];
                } elseif (!$user->stripe_customer_id) {
                    $user->update(['stripe_customer_id' => $customer['id']]);
                }

                // Create subscription
                if (Subscription::where('stripe_subscription_id', $sub['id'])->exists()) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                $plan = Plan::where('stripe_price_id', $sub['price_id'])->first();

                $subscription = Subscription::create([
                    'user_id' => $user->id,
                    'plan_id' => $plan?->id ?? 0,
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
                if ($plan && $plan->plan_type->value === 'trainer' && !$user->hasRole('registered_trainer')) {
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

        // Save import log
        Storage::put('migration/import-subscriptions-log.json', json_encode($importLog, JSON_PRETTY_PRINT));

        $this->info("Import complete:");
        $this->line("  Users created: {$importedUsers}");
        $this->line("  Subscriptions imported: {$importedSubs}");
        $this->line("  Skipped (existing): {$skipped}");
        $this->line("  Errors: {$errors}");

        return Command::SUCCESS;
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
