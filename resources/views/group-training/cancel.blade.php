<x-public-layout>
    <x-slot name="title">Payment Canceled — NADA Group Training</x-slot>

    <div class="max-w-2xl mx-auto py-16 px-4 sm:px-6 lg:px-8 text-center">
        <div class="bg-white shadow-sm rounded-lg p-8">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-yellow-100 mb-6">
                <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>

            <h1 class="text-2xl font-bold mb-2" style="color: #374269;">Payment Canceled</h1>
            <p class="text-gray-600 mb-6">Your payment was not completed. No charges have been made.</p>

            <a href="{{ route('group-training.create') }}"
               class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white shadow-sm hover:opacity-90 transition"
               style="background-color: #374269;">
                Try Again
                <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </a>
        </div>
    </div>
</x-public-layout>
