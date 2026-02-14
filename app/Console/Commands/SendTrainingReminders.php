<?php

namespace App\Console\Commands;

use App\Models\Training;
use App\Notifications\TrainingReminderNotification;
use Illuminate\Console\Command;

class SendTrainingReminders extends Command
{
    protected $signature = 'nada:send-training-reminders';
    protected $description = 'Send 24-hour training reminder emails';

    public function handle(): int
    {
        $this->info('Sending training reminders...');

        $tomorrow = now()->addDay();
        $trainings = Training::published()
            ->whereBetween('start_date', [
                $tomorrow->startOfDay(),
                $tomorrow->endOfDay(),
            ])
            ->with('registrations.user')
            ->get();

        $sent = 0;
        foreach ($trainings as $training) {
            foreach ($training->registrations as $registration) {
                if ($registration->status->value === 'registered' && $registration->user) {
                    $registration->user->notify(new TrainingReminderNotification($training, $registration));
                    $sent++;
                }
            }
        }

        $this->info("Sent {$sent} training reminders for {$trainings->count()} trainings");
        return Command::SUCCESS;
    }
}
