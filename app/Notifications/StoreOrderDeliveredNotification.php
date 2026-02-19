<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\VendorOrderSplit;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StoreOrderDeliveredNotification extends Notification
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
            ->subject("Your Order Has Been Delivered - {$this->order->order_number}")
            ->greeting("Hello, {$this->order->customer_first_name}!")
            ->line("Your order **{$this->order->order_number}** has been delivered.")
            ->line('If you have any issues with your order, please don\'t hesitate to reach out.')
            ->line('Thank you for shopping with NADA!');

        return $this->buildFromTemplate('store_order_delivered', [
            'customer_name' => $this->order->customer_full_name,
            'order_number' => $this->order->order_number,
        ], fn () => $message);
    }
}
