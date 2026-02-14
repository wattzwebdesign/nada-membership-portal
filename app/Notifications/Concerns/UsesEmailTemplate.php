<?php

namespace App\Notifications\Concerns;

use App\Models\EmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;

trait UsesEmailTemplate
{
    /**
     * Build a MailMessage from a database template, falling back to the
     * provided default closure if the template is inactive or missing.
     */
    protected function buildFromTemplate(
        string $templateKey,
        array $variables,
        \Closure $default,
    ): MailMessage {
        $template = EmailTemplate::findByKey($templateKey);

        if (!$template) {
            return $default();
        }

        $message = (new MailMessage)
            ->subject($template->renderSubject($variables))
            ->greeting($template->renderGreeting($variables));

        foreach ($template->renderBodyLines($variables) as $line) {
            $message->line($line);
        }

        $actionText = $template->renderActionText($variables);
        $actionUrl = $template->renderActionUrl($variables);

        if ($actionText && $actionUrl) {
            $url = str_starts_with($actionUrl, 'http') ? $actionUrl : url($actionUrl);
            $message->action($actionText, $url);
        }

        $outro = $template->renderOutro($variables);
        if ($outro) {
            $message->line($outro);
        }

        return $message;
    }
}
