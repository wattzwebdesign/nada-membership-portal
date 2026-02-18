<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;

class ChatbotService
{
    public function sendMessage(array $conversationHistory, ?string $userContext = null): string
    {
        $systemPrompt = $this->loadSystemPrompt();

        if ($userContext) {
            $systemPrompt .= "\n\n" . $userContext;
        }

        $response = Http::withHeaders([
            'x-api-key' => config('chatbot.api_key'),
            'anthropic-version' => '2023-06-01',
        ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
            'model' => config('chatbot.model'),
            'max_tokens' => config('chatbot.max_tokens'),
            'system' => $systemPrompt,
            'messages' => $conversationHistory,
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('Chatbot API request failed: ' . $response->status());
        }

        $data = $response->json();

        return $data['content'][0]['text'] ?? 'Sorry, I was unable to generate a response.';
    }

    public function checkRateLimit(string $ipKey): bool
    {
        $key = 'chatbot:' . $ipKey;

        return RateLimiter::tooManyAttempts($key, config('chatbot.rate_limit'));
    }

    public function hitRateLimit(string $ipKey): void
    {
        $key = 'chatbot:' . $ipKey;

        RateLimiter::hit($key, 3600);
    }

    public function loadSystemPrompt(): string
    {
        return Cache::remember('chatbot:system-prompt', 3600, function () {
            $path = resource_path('chatbot/system-prompt.md');

            if (! file_exists($path)) {
                return 'You are a helpful support assistant for the NADA membership portal.';
            }

            return file_get_contents($path);
        });
    }
}
