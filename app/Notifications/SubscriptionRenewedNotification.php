<?php

namespace App\Notifications;

use App\Models\Subscription;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionRenewedNotification extends Notification
{
    use UsesEmailTemplate;

    public function __construct(
        public Subscription $subscription,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $hasCertificate = $notifiable->certificates()->where('status', 'active')->exists();

        return $this->buildFromTemplate('subscription_renewed', [
            'user_name' => $notifiable->name,
            'plan_name' => $this->subscription->plan->name,
            'renewal_date' => $this->subscription->current_period_end->format('F j, Y'),
            'has_certificate' => $hasCertificate,
        ], function () use ($notifiable, $hasCertificate) {
            $message = (new MailMessage)
                ->subject('Membership Renewed')
                ->greeting("Hello {$notifiable->name}!")
                ->line('Your NADA membership has been successfully renewed.')
                ->line("Plan: {$this->subscription->plan->name}")
                ->line("Next renewal date: {$this->subscription->current_period_end->format('F j, Y')}");

            if ($hasCertificate) {
                $message->line('Your certificate expiration date has been updated. Please download your updated certificate.')
                    ->action('Download Certificate', url('/certificates'));
            } else {
                $message->action('View Membership', url('/membership'));
            }

            return $message->line('Thank you for continuing your membership with NADA!');
        });
    }
}
