<?php

namespace App\Notifications\Concerns;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

trait SafelyNotifies
{
    /**
     * Send a notification without letting mail errors crash the request.
     */
    protected function safeNotify(object $notifiable, $notification): void
    {
        try {
            $notifiable->notify($notification);
        } catch (\Throwable $e) {
            Log::error('Failed to send notification: ' . class_basename($notification), [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send an on-demand notification (e.g. to an email address) safely.
     */
    protected function safeNotifyRoute(string $email, $notification): void
    {
        try {
            Notification::route('mail', $email)->notify($notification);
        } catch (\Throwable $e) {
            Log::error('Failed to send notification: ' . class_basename($notification), [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
