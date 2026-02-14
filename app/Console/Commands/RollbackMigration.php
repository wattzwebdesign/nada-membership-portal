<?php

namespace App\Console\Commands;

use App\Models\Certificate;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RollbackMigration extends Command
{
    protected $signature = 'nada:rollback-migration {--confirm : Confirm rollback}';
    protected $description = 'Rollback imported data using logged record IDs';

    public function handle(): int
    {
        if (!$this->option('confirm')) {
            $this->warn('This will delete all imported records. Run with --confirm to proceed.');
            return Command::FAILURE;
        }

        // Rollback certificates
        if (Storage::exists('migration/import-certificates-log.json')) {
            $log = json_decode(Storage::get('migration/import-certificates-log.json'), true);
            $certIds = collect($log)->where('type', 'certificate')->pluck('id');
            $deleted = Certificate::whereIn('id', $certIds)->delete();
            $this->info("Deleted {$deleted} imported certificates");
        }

        // Rollback subscriptions and users
        if (Storage::exists('migration/import-subscriptions-log.json')) {
            $log = json_decode(Storage::get('migration/import-subscriptions-log.json'), true);

            $subIds = collect($log)->where('type', 'subscription')->pluck('id');
            $deletedSubs = Subscription::whereIn('id', $subIds)->delete();
            $this->info("Deleted {$deletedSubs} imported subscriptions");

            $userIds = collect($log)->where('type', 'user')->pluck('id');
            $deletedUsers = User::whereIn('id', $userIds)->forceDelete();
            $this->info("Deleted {$deletedUsers} imported users");
        }

        $this->info('Rollback complete. Stripe data was NOT modified.');
        return Command::SUCCESS;
    }
}
