@if(config('chatbot.enabled'))
<div
    x-data="{
        open: false,
        messages: [],
        userInput: '',
        isLoading: false,
        hasError: false,
        errorMessage: '',
        maxMessages: {{ config('chatbot.max_messages', 20) }},

        async sendMessage() {
            const input = this.userInput.trim();
            if (!input || this.isLoading) return;

            if (input.length > 500) {
                this.hasError = true;
                this.errorMessage = 'Messages must be 500 characters or less.';
                return;
            }

            const userCount = this.messages.filter(m => m.role === 'user').length;
            if (userCount >= this.maxMessages) {
                this.hasError = true;
                this.errorMessage = 'Conversation limit reached. Please clear the chat to continue.';
                return;
            }

            this.hasError = false;
            this.messages.push({ role: 'user', content: input });
            this.userInput = '';
            this.isLoading = true;
            this.$nextTick(() => this.scrollToBottom());

            try {
                const response = await fetch('{{ route('chat.send') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ messages: this.messages }),
                });

                const data = await response.json();

                if (!response.ok) {
                    this.hasError = true;
                    this.errorMessage = data.error || 'Something went wrong. Please try again.';
                    this.messages.pop();
                    return;
                }

                this.messages.push({ role: 'assistant', content: data.content });
            } catch (e) {
                this.hasError = true;
                this.errorMessage = 'Something went wrong. Please try again.';
                this.messages.pop();
            } finally {
                this.isLoading = false;
                this.$nextTick(() => this.scrollToBottom());
            }
        },

        clearConversation() {
            this.messages = [];
            this.hasError = false;
            this.errorMessage = '';
        },

        scrollToBottom() {
            if (this.$refs.chatMessages) {
                this.$refs.chatMessages.scrollTop = this.$refs.chatMessages.scrollHeight;
            }
        },

        renderMarkdown(text) {
            return text
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>')
                .replace(/`(.*?)`/g, '<code class=&quot;bg-gray-200 px-1 rounded text-xs&quot;>$1</code>')
                .replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href=&quot;$2&quot; class=&quot;text-blue-600 underline&quot;>$1</a>')
                .replace(/\n/g, '<br>');
        }
    }"
    class="fixed bottom-6 right-6 z-50"
    style="font-family: 'Figtree', sans-serif;"
>
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
                <button x-show="messages.length > 0" @click="clearConversation()" class="p-1.5 rounded-lg hover:bg-white/20 transition" title="Clear conversation">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
                <button @click="open = false" class="p-1.5 rounded-lg hover:bg-white/20 transition" title="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Messages --}}
        <div class="flex-1 overflow-y-auto px-4 py-3 space-y-3" x-ref="chatMessages">
            {{-- Welcome message --}}
            <div x-show="messages.length === 0 && !isLoading" class="flex justify-start">
                <div class="rounded-2xl rounded-tl-sm px-4 py-2.5 max-w-[85%] text-sm bg-gray-100 text-gray-800">
                    <p>Hi! I can help you with questions about membership, trainings, certificates, and navigating the portal.</p>
                    <p class="mt-2 text-gray-500 text-xs">Ask me anything to get started.</p>
                </div>
            </div>

            <template x-for="(message, index) in messages" :key="index">
                <div>
                    {{-- User message --}}
                    <div x-show="message.role === 'user'" class="flex justify-end">
                        <div class="rounded-2xl rounded-tr-sm px-4 py-2.5 max-w-[85%] text-sm text-white" style="background-color: #374269;" x-text="message.content"></div>
                    </div>
                    {{-- Assistant message --}}
                    <div x-show="message.role === 'assistant'" class="flex justify-start">
                        <div class="rounded-2xl rounded-tl-sm px-4 py-2.5 max-w-[85%] text-sm bg-gray-100 text-gray-800 prose prose-sm prose-a:text-blue-600" x-html="renderMarkdown(message.content)"></div>
                    </div>
                </div>
            </template>

            {{-- Typing indicator --}}
            <div x-show="isLoading" class="flex justify-start">
                <div class="rounded-2xl rounded-tl-sm px-4 py-3 bg-gray-100">
                    <div class="flex items-center gap-1">
                        <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms;"></span>
                        <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms;"></span>
                        <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms;"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Error --}}
        <div x-show="hasError" x-cloak class="px-4 py-2 bg-red-50 border-t border-red-100 shrink-0">
            <p class="text-xs text-red-600" x-text="errorMessage"></p>
        </div>

        {{-- Input --}}
        <div class="px-3 py-3 border-t border-gray-200 shrink-0">
            <div class="flex items-center gap-2">
                <input
                    x-model="userInput"
                    @keydown.enter="sendMessage()"
                    type="text"
                    placeholder="Type your question..."
                    maxlength="500"
                    :disabled="isLoading"
                    class="flex-1 rounded-full border border-gray-300 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#374269] focus:border-transparent"
                    autocomplete="off"
                />
                <button
                    @click="sendMessage()"
                    type="button"
                    :disabled="isLoading"
                    class="shrink-0 w-9 h-9 rounded-full flex items-center justify-center text-white transition hover:opacity-90 disabled:opacity-50"
                    style="background-color: #d39c27;"
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
