<div class="space-y-4">
    <div class="flex items-center gap-2">
        @if ($this->stripeInfo['configured'] && isset($this->stripeInfo['mode']))
            @if ($this->stripeInfo['mode'] === 'Test')
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800 border border-yellow-300">
                    TEST MODE
                </span>
            @else
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800 border border-green-300">
                    LIVE
                </span>
            @endif
        @endif
    </div>

    @if (!empty($this->stripeInfo['error']) && !$this->stripeInfo['configured'])
        <div class="rounded-lg border border-red-200 bg-red-50 p-4">
            <p class="text-sm text-red-700">{{ $this->stripeInfo['error'] }}</p>
        </div>
    @elseif ($this->stripeInfo['configured'])
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            {{-- Account Details --}}
            @if (isset($this->stripeInfo['account']))
                <div class="rounded-lg border border-gray-200 bg-white dark:bg-gray-900 dark:border-gray-700 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Account Details</h4>
                    </div>
                    <dl class="divide-y divide-gray-200 dark:divide-gray-700">
                        <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Account ID</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:col-span-2 sm:mt-0 font-mono">{{ $this->stripeInfo['account']['id'] }}</dd>
                        </div>
                        <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Business Name</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:col-span-2 sm:mt-0">{{ $this->stripeInfo['account']['business_name'] }}</dd>
                        </div>
                        <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:col-span-2 sm:mt-0">{{ $this->stripeInfo['account']['email'] }}</dd>
                        </div>
                        <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Country / Currency</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:col-span-2 sm:mt-0">{{ $this->stripeInfo['account']['country'] }} / {{ $this->stripeInfo['account']['default_currency'] }}</dd>
                        </div>
                        <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Charges Enabled</dt>
                            <dd class="mt-1 text-sm sm:col-span-2 sm:mt-0">
                                @if ($this->stripeInfo['account']['charges_enabled'])
                                    <span class="inline-flex items-center gap-1 text-green-700"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg> Yes</span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-red-700"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg> No</span>
                                @endif
                            </dd>
                        </div>
                        <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Payouts Enabled</dt>
                            <dd class="mt-1 text-sm sm:col-span-2 sm:mt-0">
                                @if ($this->stripeInfo['account']['payouts_enabled'])
                                    <span class="inline-flex items-center gap-1 text-green-700"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg> Yes</span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-red-700"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg> No</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            @endif

            {{-- API Keys --}}
            <div class="rounded-lg border border-gray-200 bg-white dark:bg-gray-900 dark:border-gray-700 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white">API Configuration</h4>
                </div>
                <dl class="divide-y divide-gray-200 dark:divide-gray-700">
                    <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Publishable Key</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:col-span-2 sm:mt-0 font-mono">{{ $this->stripeInfo['publishable_key_last4'] }}</dd>
                    </div>
                    <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Secret Key</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:col-span-2 sm:mt-0 font-mono">{{ $this->stripeInfo['secret_key_last4'] }}</dd>
                    </div>
                    <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Webhook Secret</dt>
                        <dd class="mt-1 text-sm sm:col-span-2 sm:mt-0">
                            @if ($this->stripeInfo['webhook_configured'])
                                <span class="inline-flex items-center gap-1 text-green-700"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg> Configured</span>
                            @else
                                <span class="inline-flex items-center gap-1 text-yellow-700"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg> Not configured</span>
                            @endif
                        </dd>
                    </div>
                    <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Connect Webhook Secret</dt>
                        <dd class="mt-1 text-sm sm:col-span-2 sm:mt-0">
                            @if ($this->stripeInfo['connect_webhook_configured'])
                                <span class="inline-flex items-center gap-1 text-green-700"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg> Configured</span>
                            @else
                                <span class="inline-flex items-center gap-1 text-yellow-700"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg> Not configured</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Webhook Endpoints --}}
        @if (isset($this->stripeInfo['webhooks']))
            <div class="rounded-lg border border-gray-200 bg-white dark:bg-gray-900 dark:border-gray-700 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Webhook Endpoints ({{ count($this->stripeInfo['webhooks']) }})</h4>
                </div>

                @if (count($this->stripeInfo['webhooks']) === 0)
                    <div class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                        No webhook endpoints configured in Stripe.
                    </div>
                @else
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($this->stripeInfo['webhooks'] as $webhook)
                            <div class="px-4 py-4" x-data="{ open: false }">
                                <div class="flex items-center justify-between">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-mono text-gray-900 dark:text-white truncate">{{ $webhook['url'] }}</p>
                                        <div class="mt-1 flex items-center gap-3">
                                            @if ($webhook['status'] === 'enabled')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Enabled</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">{{ ucfirst($webhook['status']) }}</span>
                                            @endif
                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ count($webhook['enabled_events']) }} event{{ count($webhook['enabled_events']) !== 1 ? 's' : '' }}</span>
                                            @if ($webhook['api_version'])
                                                <span class="text-xs text-gray-400 dark:text-gray-500">API {{ $webhook['api_version'] }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <button type="button" @click="open = !open" class="ml-3 flex-shrink-0 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                        <span x-show="!open">Show events</span>
                                        <span x-show="open" x-cloak>Hide events</span>
                                    </button>
                                </div>

                                <div x-show="open" x-cloak x-transition class="mt-3">
                                    @if (in_array('*', $webhook['enabled_events']))
                                        <p class="text-xs text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-800 rounded px-3 py-2">All events (<code>*</code>)</p>
                                    @else
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach ($webhook['enabled_events'] as $event)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">{{ $event }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        {{-- Error fetching API data --}}
        @if (!empty($this->stripeInfo['error']))
            <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4">
                <p class="text-sm text-yellow-700">{{ $this->stripeInfo['error'] }}</p>
            </div>
        @endif

        {{-- Quick Link --}}
        <div class="flex">
            <a href="https://dashboard.stripe.com/{{ $this->stripeInfo['mode'] === 'Test' ? 'test/' : '' }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                Open Stripe Dashboard
            </a>
        </div>
    @endif
</div>
