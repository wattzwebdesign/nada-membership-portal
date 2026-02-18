@if(config('chatbot.enabled'))
<div x-data="{ open: false }" class="fixed bottom-6 right-6 z-50" style="font-family: 'Figtree', sans-serif;">
    {{-- Chat Panel --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-4"
        x-cloak
        class="mb-4 w-[380px] max-w-[calc(100vw-2rem)] rounded-2xl shadow-2xl overflow-hidden flex flex-col bg-white border border-gray-200"
        style="height: 500px; max-height: calc(100vh - 8rem);"
    >
        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3 text-white shrink-0" style="background-color: #374269;">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
                <span class="font-semibold text-sm">NADA Support</span>
            </div>
            <div class="flex items-center gap-1">
                @if(count($messages) > 0)
                    <button wire:click="clearConversation" class="p-1.5 rounded-lg hover:bg-white/20 transition" title="Clear conversation">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                @endif
                <button @click="open = false" class="p-1.5 rounded-lg hover:bg-white/20 transition" title="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Messages --}}
        <div
            class="flex-1 overflow-y-auto px-4 py-3 space-y-3"
            x-ref="chatMessages"
            @chat-updated.window="$nextTick(() => $refs.chatMessages.scrollTop = $refs.chatMessages.scrollHeight)"
        >
            {{-- Welcome message when empty --}}
            @if(count($messages) === 0 && !$isLoading)
                <div class="flex justify-start">
                    <div class="rounded-2xl rounded-tl-sm px-4 py-2.5 max-w-[85%] text-sm bg-gray-100 text-gray-800">
                        <p>Hi! I can help you with questions about membership, trainings, certificates, and navigating the portal.</p>
                        <p class="mt-2 text-gray-500 text-xs">Ask me anything to get started.</p>
                    </div>
                </div>
            @endif

            @foreach($messages as $message)
                @if($message['role'] === 'user')
                    <div class="flex justify-end">
                        <div class="rounded-2xl rounded-tr-sm px-4 py-2.5 max-w-[85%] text-sm text-white" style="background-color: #374269;">
                            {{ $message['content'] }}
                        </div>
                    </div>
                @else
                    <div class="flex justify-start">
                        <div class="rounded-2xl rounded-tl-sm px-4 py-2.5 max-w-[85%] text-sm bg-gray-100 text-gray-800 prose prose-sm prose-a:text-blue-600">
                            {!! \Illuminate\Support\Str::markdown($message['content']) !!}
                        </div>
                    </div>
                @endif
            @endforeach

            {{-- Typing indicator --}}
            @if($isLoading)
                <div class="flex justify-start">
                    <div class="rounded-2xl rounded-tl-sm px-4 py-3 bg-gray-100">
                        <div class="flex items-center gap-1">
                            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms;"></span>
                            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms;"></span>
                            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms;"></span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Error --}}
        @if($hasError)
            <div class="px-4 py-2 bg-red-50 border-t border-red-100 shrink-0">
                <p class="text-xs text-red-600">{{ $errorMessage }}</p>
            </div>
        @endif

        {{-- Input --}}
        <div class="px-3 py-3 border-t border-gray-200 shrink-0">
            <div class="flex items-center gap-2">
                <input
                    wire:model="userInput"
                    wire:keydown.enter="sendMessage"
                    type="text"
                    placeholder="Type your question..."
                    maxlength="500"
                    class="flex-1 rounded-full border border-gray-300 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#374269] focus:border-transparent"
                    @if($isLoading) disabled @endif
                    autocomplete="off"
                />
                <button
                    wire:click="sendMessage"
                    type="button"
                    class="shrink-0 w-9 h-9 rounded-full flex items-center justify-center text-white transition hover:opacity-90 disabled:opacity-50"
                    style="background-color: #d39c27;"
                    @if($isLoading) disabled @endif
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Floating Button --}}
    <button
        @click="open = !open"
        class="ml-auto flex items-center justify-center w-14 h-14 rounded-full text-white shadow-lg transition hover:scale-105 hover:shadow-xl"
        style="background-color: #374269;"
        title="Chat with NADA Support"
    >
        <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
        </svg>
        <svg x-show="open" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</div>
@endif
