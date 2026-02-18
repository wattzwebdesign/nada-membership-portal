<?php

namespace App\Notifications;

use App\Models\Invoice;
use App\Models\Subscription;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentOverdueNotification extends Notification implements ShouldQueue
{
    use Queueable, UsesEmailTemplate;

    public function __construct(
        public Subscription $subscription,
        public Invoice $invoice,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $amount = '$' . number_format($this->invoice->amount_due, 2);
        $failedDate = $this->invoice->created_at->format('F j, Y');

        return $this->buildFromTemplate('payment_overdue', [
            'user_name' => $notifiable->name,
            'amount' => $amount,
            'failed_date' => $failedDate,
        ], fn () => (new MailMessage)
            ->subject('Payment Overdue - Action Required')
            ->greeting("Hello {$notifiable->name},")
            ->line("Your membership payment of **{$amount}** failed on {$failedDate}.")
            ->line('Please pay the outstanding invoice or add a new card to keep your membership active.')
            ->action('Pay Now', url('/membership/invoices/' . $this->invoice->id))
            ->line('You can also add a new card at your [billing page](' . url('/membership/billing') . ') — Stripe will automatically retry the payment.')
            ->line('If you need help, contact financial@acudetox.com.'));
    }
}
