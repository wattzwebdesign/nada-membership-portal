<?php

namespace App\Notifications;

use App\Models\Invoice;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceCreatedNotification extends Notification
{
    use UsesEmailTemplate;

    public function __construct(
        public Invoice $invoice,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return $this->buildFromTemplate('invoice_created', [
            'user_name' => $notifiable->name,
            'invoice_number' => $this->invoice->number,
            'amount_due' => '$' . number_format($this->invoice->amount_due, 2),
            'invoice_id' => $this->invoice->id,
        ], fn () => (new MailMessage)
            ->subject("New Invoice {$this->invoice->number}")
            ->greeting("Hello {$notifiable->name},")
            ->line("A new invoice has been created for your account.")
            ->line("Invoice: {$this->invoice->number}")
            ->line("Amount Due: \${$this->invoice->amount_due}")
            ->action('View Invoice', url("/invoices/{$this->invoice->id}"))
            ->line('Please review and submit payment at your earliest convenience.'));
    }
}
