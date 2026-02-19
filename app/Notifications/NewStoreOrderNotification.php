<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\VendorOrderSplit;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewStoreOrderNotification extends Notification
{
    use UsesEmailTemplate;

    public function __construct(
        public Order $order,
        public VendorOrderSplit $split,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return $this->buildFromTemplate('new_store_order', [
            'order_number' => $this->order->order_number,
            'vendor_payout' => $this->split->vendor_payout_formatted,
            'customer_name' => $this->order->customer_full_name,
        ], fn () => (new MailMessage)
            ->subject("New Order - {$this->order->order_number}")
            ->greeting('You have a new order!')
            ->line("Order #{$this->order->order_number} has been placed.")
            ->line("Customer: {$this->order->customer_full_name}")
            ->line("Your payout: {$this->split->vendor_payout_formatted}")
            ->action('View Order', url("/vendor/orders/{$this->order->id}"))
            ->line('Please process this order promptly.'));
    }
}
