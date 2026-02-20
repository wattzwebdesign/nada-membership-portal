<?php

namespace App\Notifications;

use App\Models\GroupTrainingRequest;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GroupTrainingPaidNotification extends Notification
{
    use UsesEmailTemplate;

    public function __construct(
        public GroupTrainingRequest $groupRequest,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $r = $this->groupRequest;

        return $this->buildFromTemplate('group_training_paid', [
            'trainer_name' => $notifiable->full_name ?? ($notifiable->first_name ?? ''),
            'company_name' => $r->company_full_name,
            'company_email' => $r->company_email,
            'training_name' => $r->training_name,
            'training_date' => $r->training_date->format('F j, Y'),
            'training_location' => $r->training_city . ', ' . $r->training_state,
            'ticket_count' => $r->number_of_tickets,
            'total_paid' => $r->total_formatted,
            'request_url' => route('trainer.group-requests.show', $r),
        ], fn () => (new MailMessage)
            ->subject('New Group Training Request: ' . $r->training_name)
            ->greeting('Hello ' . ($notifiable->first_name ?? '') . '!')
            ->line('A new group training request has been submitted and paid.')
            ->line('**Training:** ' . $r->training_name)
            ->line('**Date:** ' . $r->training_date->format('F j, Y'))
            ->line('**Location:** ' . $r->training_city . ', ' . $r->training_state)
            ->line('**Company Contact:** ' . $r->company_full_name . ' (' . $r->company_email . ')')
            ->line('**Tickets:** ' . $r->number_of_tickets)
            ->line('**Total Paid:** ' . $r->total_formatted)
            ->action('View Request & Create Training', route('trainer.group-requests.show', $r))
            ->line('Please review the request details and create a training from it in your portal.'));
    }
}
