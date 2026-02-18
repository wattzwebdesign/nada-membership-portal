<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('nada:sync-certificate-expirations')
    ->daily()
    ->description('Sync certificate expiration dates with subscription periods');

Schedule::command('nada:send-training-reminders')
    ->dailyAt('09:00')
    ->description('Send 24-hour training reminders');

Schedule::command('nada:expire-discount-tokens')
    ->daily()
    ->description('Expire discount approval tokens older than 30 days');

Schedule::command('nada:check-expiring-memberships')
    ->weekly()
    ->description('Notify admin of memberships expiring in next 30 days');

Schedule::command('nada:send-renewal-reminders')
    ->dailyAt('09:00')
    ->description('Send renewal and payment overdue reminders');
