<?php

namespace App\Notifications;

use App\Models\GroupTrainingRequest;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GroupTrainingConfirmationNotification extends Notification
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
        $trainer = $r->trainer;

        return $this->buildFromTemplate('group_training_confirmation', [
            'company_name' => $r->company_full_name,
            'training_name' => $r->training_name,
            'training_date' => $r->training_date->format('F j, Y'),
            'training_location' => $r->training_city . ', ' . $r->training_state,
            'trainer_name' => $trainer->full_name ?? 'Your trainer',
            'ticket_count' => $r->number_of_tickets,
            'total_paid' => $r->total_formatted,
        ], fn () => (new MailMessage)
            ->subject('Group Training Confirmation: ' . $r->training_name)
            ->greeting('Hello ' . $r->company_first_name . '!')
            ->line('Your group training registration has been confirmed.')
            ->line('**Training:** ' . $r->training_name)
            ->line('**Date:** ' . $r->training_date->format('F j, Y'))
            ->line('**Location:** ' . $r->training_city . ', ' . $r->training_state)
            ->line('**Trainer:** ' . ($trainer->full_name ?? 'TBD'))
            ->line('**Tickets:** ' . $r->number_of_tickets)
            ->line('**Total Paid:** ' . $r->total_formatted)
            ->line('Your trainer will be in touch with additional details about the training session. Thank you for your registration!'));
    }
}
