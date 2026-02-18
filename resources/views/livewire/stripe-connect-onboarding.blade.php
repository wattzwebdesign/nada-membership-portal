<div>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-6 rounded-md bg-green-50 p-4">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 rounded-md bg-red-50 p-4">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-2xl font-bold mb-2 text-brand-primary">Stripe Connect</h2>
                    <p class="text-gray-600 mb-6">Connect your Stripe account to receive payments from training registrations.</p>

                    @if ($isConnected)
                        {{-- Connected State --}}
                        <div class="border border-green-200 bg-green-50 rounded-lg p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-10 w-10 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.745 3.745 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-green-800">Stripe Connected</h3>
                                    <p class="text-sm text-green-700 mt-1">Your account is fully set up and ready to receive payments.</p>
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-3 gap-4">
                                <div class="text-center p-3 bg-white rounded-md">
                                    <div class="flex items-center justify-center">
                                        @if ($stripeAccount->charges_enabled)
                                            <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                            </svg>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-600 mt-1">Charges</p>
                                </div>
                                <div class="text-center p-3 bg-white rounded-md">
                                    <div class="flex items-center justify-center">
                                        @if ($stripeAccount->payouts_enabled)
                                            <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                            </svg>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-600 mt-1">Payouts</p>
                                </div>
                                <div class="text-center p-3 bg-white rounded-md">
                                    <div class="flex items-center justify-center">
                                        @if ($stripeAccount->details_submitted)
                                            <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                            </svg>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-600 mt-1">Details</p>
                                </div>
                            </div>
                        </div>
                    @elseif ($stripeAccount)
                        {{-- Partially Onboarded --}}
                        <div class="border border-yellow-200 bg-yellow-50 rounded-lg p-6">
                            <div class="flex items-center mb-4">
                                <svg class="h-10 w-10 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                </svg>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-yellow-800">Onboarding Incomplete</h3>
                                    <p class="text-sm text-yellow-700 mt-1">Your Stripe account setup is not yet complete. Please finish the onboarding process.</p>
                                </div>
                            </div>
                            <button wire:click="startOnboarding"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center px-4 py-2 rounded-md text-white text-sm font-medium shadow-sm hover:opacity-90 transition-opacity bg-brand-secondary">
                                <span wire:loading.remove wire:target="startOnboarding">Continue Onboarding</span>
                                <span wire:loading wire:target="startOnboarding">Redirecting to Stripe...</span>
                            </button>
                        </div>
                    @else
                        {{-- Not Connected --}}
                        <div class="border border-gray-200 rounded-lg p-6 text-center">
                            <svg class="mx-auto h-16 w-16 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                            </svg>
                            <h3 class="mt-4 text-lg font-semibold text-gray-900">Connect Your Stripe Account</h3>
                            <p class="mt-2 text-sm text-gray-500 max-w-md mx-auto">
                                Set up your Stripe account to receive payments for your trainings. The process takes just a few minutes.
                            </p>
                            <button wire:click="startOnboarding"
                                    wire:loading.attr="disabled"
                                    class="mt-6 inline-flex items-center px-6 py-3 rounded-md text-white text-sm font-medium shadow-sm hover:opacity-90 transition-opacity bg-brand-primary">
                                <span wire:loading.remove wire:target="startOnboarding">Set Up Stripe Account</span>
                                <span wire:loading wire:target="startOnboarding">Redirecting to Stripe...</span>
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
