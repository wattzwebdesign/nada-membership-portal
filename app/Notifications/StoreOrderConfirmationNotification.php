<?php

namespace App\Notifications;

use App\Models\Order;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StoreOrderConfirmationNotification extends Notification
{
    use UsesEmailTemplate;

    public function __construct(
        public Order $order,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $itemsList = $this->order->items->map(fn ($item) =>
            "{$item->product_title} x{$item->quantity} - \${$this->formatCents($item->total_cents)}"
        )->implode("\n");

        return $this->buildFromTemplate('store_order_confirmation', [
            'customer_name' => $this->order->customer_full_name,
            'order_number' => $this->order->order_number,
            'total' => $this->order->total_formatted,
        ], fn () => (new MailMessage)
            ->subject("Order Confirmation - {$this->order->order_number}")
            ->greeting("Thank you for your order, {$this->order->customer_first_name}!")
            ->line("Your order **{$this->order->order_number}** has been confirmed.")
            ->line("Order Total: {$this->order->total_formatted}")
            ->line($itemsList)
            ->line('You will receive updates as your order is processed.'));
    }

    protected function formatCents(int $cents): string
    {
        return number_format($cents / 100, 2);
    }
}
