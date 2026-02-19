<?php

namespace App\Notifications;

use App\Models\User;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Password;

class CustomerSetPasswordNotification extends Notification
{
    use UsesEmailTemplate;

    protected string $token;

    public function __construct(
        public User $user,
    ) {
        $this->token = Password::createToken($user);
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $this->user->email,
        ], false));

        return $this->buildFromTemplate('customer_set_password', [
            'user_name' => $this->user->first_name,
            'user_email' => $this->user->email,
            'reset_url' => $resetUrl,
        ], fn () => (new MailMessage)
            ->subject('Set Your Password - NADA')
            ->greeting("Welcome, {$this->user->first_name}!")
            ->line('An account has been created for you based on your recent purchase from the NADA store.')
            ->line('Click the button below to set your password and access your order history.')
            ->action('Set Your Password', $resetUrl)
            ->line('This link will expire in 60 minutes. If it expires, you can use the "Forgot Password" link on the login page.')
            ->line('Thank you for shopping with NADA!'));
    }
}
