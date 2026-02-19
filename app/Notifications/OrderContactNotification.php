<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\User;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderContactNotification extends Notification implements ShouldQueue
{
    use Queueable, UsesEmailTemplate;

    public function __construct(
        public Order $order,
        public User $customer,
        public string $subject,
        public string $contactMessage,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $default = (new MailMessage)
            ->subject("Order Inquiry: {$this->order->order_number} - {$this->subject}")
            ->replyTo($this->customer->email, $this->customer->first_name . ' ' . $this->customer->last_name)
            ->greeting("Hello {$notifiable->first_name},")
            ->line("You have received an order inquiry through the NADA store.")
            ->line("**Order:** {$this->order->order_number}")
            ->line("**From:** {$this->customer->first_name} {$this->customer->last_name}")
            ->line("**Subject:** {$this->subject}")
            ->line('**Message:**')
            ->line($this->contactMessage)
            ->line('You can reply directly to this email to respond to the customer.');

        return $this->buildFromTemplate('order_contact', [
            'vendor_name' => $notifiable->first_name,
            'customer_name' => $this->customer->first_name . ' ' . $this->customer->last_name,
            'order_number' => $this->order->order_number,
            'subject' => $this->subject,
            'message' => $this->contactMessage,
        ], fn () => $default);
    }
}
