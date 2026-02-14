<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckExpiringMemberships extends Command
{
    protected $signature = 'nada:check-expiring-memberships';
    protected $description = 'Notify admin of memberships expiring in next 30 days';

    public function handle(): int
    {
        $expiring = Subscription::where('status', 'active')
            ->where('current_period_end', '<=', now()->addDays(30))
            ->where('current_period_end', '>', now())
            ->with('user', 'plan')
            ->get();

        if ($expiring->isEmpty()) {
            $this->info('No memberships expiring in the next 30 days.');
            return Command::SUCCESS;
        }

        $this->info("Found {$expiring->count()} memberships expiring in next 30 days");

        $adminEmail = config('app.nada_admin_email');

        Mail::raw(
            "NADA Membership Expiration Alert\n\n" .
            "{$expiring->count()} memberships are expiring in the next 30 days.\n\n" .
            $expiring->map(function ($sub) {
                return "- {$sub->user->full_name} ({$sub->user->email}) — {$sub->plan->name} — Expires: {$sub->current_period_end->format('M j, Y')}";
            })->implode("\n"),
            function ($message) use ($adminEmail) {
                $message->to($adminEmail)
                    ->subject('NADA: Memberships Expiring Soon');
            }
        );

        $this->info("Admin notification sent to {$adminEmail}");
        return Command::SUCCESS;
    }
}
