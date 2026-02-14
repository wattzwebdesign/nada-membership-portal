<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Connect Stripe Account') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8">
                    <div class="text-center">
                        {{-- Stripe Logo Area --}}
                        <div class="mx-auto w-20 h-20 rounded-full flex items-center justify-center mb-6" style="background-color: rgba(55, 66, 105, 0.1);">
                            <svg class="w-10 h-10" style="color: #374269;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        </div>

                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Stripe Connect Onboarding</h3>
                        <p class="text-gray-600 mb-8 max-w-lg mx-auto">
                            Connect your Stripe Express account to receive payouts for paid trainings. NADA will handle payment processing and send your share directly to your connected account.
                        </p>

                        {{-- Steps --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8 text-left">
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center mb-2">
                                    <span class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold text-white" style="background-color: #374269;">1</span>
                                    <span class="ml-2 text-sm font-semibold text-gray-900">Click Start</span>
                                </div>
                                <p class="text-xs text-gray-500">Begin the Stripe onboarding process by clicking the button below.</p>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center mb-2">
                                    <span class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold text-white" style="background-color: #374269;">2</span>
                                    <span class="ml-2 text-sm font-semibold text-gray-900">Complete Setup</span>
                                </div>
                                <p class="text-xs text-gray-500">Provide your identity, bank account, and tax information to Stripe.</p>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center mb-2">
                                    <span class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold text-white" style="background-color: #374269;">3</span>
                                    <span class="ml-2 text-sm font-semibold text-gray-900">Get Paid</span>
                                </div>
                                <p class="text-xs text-gray-500">Once connected, your training earnings will be deposited automatically.</p>
                            </div>
                        </div>

                        {{-- Security Note --}}
                        <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 mb-8 text-left">
                            <div class="flex">
                                <svg class="w-5 h-5 text-blue-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                <div>
                                    <p class="text-sm font-medium text-blue-800">Secure & Private</p>
                                    <p class="text-xs text-blue-600 mt-1">Your sensitive financial information is handled directly by Stripe. NADA never sees your bank details or social security number.</p>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('trainer.payouts.connect') }}?start=1" class="inline-flex items-center px-8 py-3 border border-transparent text-lg font-medium rounded-md text-white transition" style="background-color: #374269;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                            Start Onboarding
                        </a>

                        <p class="mt-4 text-xs text-gray-400">You will be redirected to Stripe to complete the setup process.</p>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <a href="{{ route('trainer.payouts.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Back to Payouts
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
