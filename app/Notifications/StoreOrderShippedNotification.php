<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\VendorOrderSplit;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StoreOrderShippedNotification extends Notification
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
        $message = (new MailMessage)
            ->subject("Your Order Has Shipped - {$this->order->order_number}")
            ->greeting("Hello, {$this->order->customer_first_name}!")
            ->line("Great news! Your order **{$this->order->order_number}** has been shipped.");

        if ($this->split->tracking_number) {
            $message->line("Tracking Number: {$this->split->tracking_number}");
        }

        $message->line('Thank you for shopping with NADA!');

        return $this->buildFromTemplate('store_order_shipped', [
            'customer_name' => $this->order->customer_full_name,
            'order_number' => $this->order->order_number,
            'tracking_number' => $this->split->tracking_number,
        ], fn () => $message);
    }
}
