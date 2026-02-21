<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Notifications\EventReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendEventReminders extends Command
{
    protected $signature = 'nada:send-event-reminders';
    protected $description = 'Send 24-hour event reminder emails';

    public function handle(): int
    {
        $this->info('Sending event reminders...');

        $tomorrow = now()->addDay();
        $events = Event::published()
            ->whereBetween('start_date', [
                $tomorrow->copy()->startOfDay(),
                $tomorrow->copy()->endOfDay(),
            ])
            ->with('registrations')
            ->get();

        $sent = 0;
        foreach ($events as $event) {
            foreach ($event->registrations as $registration) {
                if ($registration->status->value !== 'registered') {
                    continue;
                }

                if ($registration->reminder_sent_at) {
                    continue;
                }

                if ($registration->user) {
                    $registration->user->notify(new EventReminderNotification($event, $registration));
                } else {
                    Notification::route('mail', $registration->email)
                        ->notify(new EventReminderNotification($event, $registration));
                }

                $registration->update(['reminder_sent_at' => now()]);
                $sent++;
            }
        }

        $this->info("Sent {$sent} event reminders for {$events->count()} events");
        return Command::SUCCESS;
    }
}
