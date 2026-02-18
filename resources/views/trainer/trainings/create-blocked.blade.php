<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Training') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8 text-center">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-yellow-100 mb-6">
                        <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Before you can create trainings</h3>
                    <p class="text-gray-600 mb-6">You need to complete the following to start hosting trainings:</p>

                    <div class="space-y-4 text-left max-w-md mx-auto">
                        <div class="flex items-center gap-3 p-3 rounded-lg {{ $hasStripe ? 'bg-green-50' : 'bg-red-50' }}">
                            @if($hasStripe)
                                <svg class="h-5 w-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span class="text-green-800">Stripe account connected</span>
                            @else
                                <svg class="h-5 w-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                <span class="text-red-800">Connect your Stripe account for payouts</span>
                            @endif
                        </div>

                        <div class="flex items-center gap-3 p-3 rounded-lg {{ $hasPlan ? 'bg-green-50' : 'bg-red-50' }}">
                            @if($hasPlan)
                                <svg class="h-5 w-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span class="text-green-800">Active Registered Trainer plan</span>
                            @else
                                <svg class="h-5 w-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                <span class="text-red-800">Subscribe to a Registered Trainer plan</span>
                            @endif
                        </div>
                    </div>

                    <div class="mt-8 flex justify-center gap-4">
                        @unless($hasStripe)
                            <a href="{{ route('trainer.payouts.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-brand-primary">
                                Connect Stripe
                            </a>
                        @endunless
                        @unless($hasPlan)
                            <a href="{{ route('membership.plans') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-brand-primary">
                                View Plans
                            </a>
                        @endunless
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
