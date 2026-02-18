<?php

namespace App\Livewire;

use App\Services\ChatbotService;
use Livewire\Component;

class SupportChat extends Component
{
    public array $messages = [];

    public string $userInput = '';

    public bool $isLoading = false;

    public bool $hasError = false;

    public string $errorMessage = '';

    public function sendMessage(): void
    {
        $this->hasError = false;
        $this->errorMessage = '';

        $input = trim($this->userInput);

        if ($input === '') {
            return;
        }

        if (mb_strlen($input) > 500) {
            $this->hasError = true;
            $this->errorMessage = 'Messages must be 500 characters or less.';

            return;
        }

        $maxMessages = config('chatbot.max_messages', 20);
        $userMessageCount = count(array_filter($this->messages, fn ($m) => $m['role'] === 'user'));

        if ($userMessageCount >= $maxMessages) {
            $this->hasError = true;
            $this->errorMessage = 'Conversation limit reached. Please clear the chat to continue.';

            return;
        }

        $chatbot = app(ChatbotService::class);
        $ip = request()->ip();

        if ($chatbot->checkRateLimit($ip)) {
            $this->hasError = true;
            $this->errorMessage = 'Too many requests. Please try again later.';

            return;
        }

        $this->messages[] = ['role' => 'user', 'content' => $input];
        $this->userInput = '';
        $this->isLoading = true;

        try {
            $chatbot->hitRateLimit($ip);
            $response = $chatbot->sendMessage($this->messages);
            $this->messages[] = ['role' => 'assistant', 'content' => $response];
        } catch (\Throwable $e) {
            $this->hasError = true;
            $this->errorMessage = 'Something went wrong. Please try again.';
            array_pop($this->messages);
            report($e);
        } finally {
            $this->isLoading = false;
        }

        $this->dispatch('chat-updated');
    }

    public function clearConversation(): void
    {
        $this->messages = [];
        $this->hasError = false;
        $this->errorMessage = '';
    }

    public function render()
    {
        return view('livewire.support-chat');
    }
}
