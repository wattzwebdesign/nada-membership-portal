<x-public-layout>
    <x-slot name="title">Payment Confirmed — NADA Group Training</x-slot>

    <div class="max-w-2xl mx-auto py-16 px-4 sm:px-6 lg:px-8 text-center">
        <div class="bg-white shadow-sm rounded-lg p-8">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-6">
                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>

            <h1 class="text-2xl font-bold mb-2 text-brand-primary">Payment Confirmed!</h1>
            <p class="text-gray-600 mb-6">Your group training registration has been submitted and payment received.</p>

            @if ($groupRequest)
                <div class="bg-gray-50 rounded-md p-6 text-left mb-6">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Booking Summary</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-600">Training</dt>
                            <dd class="font-medium text-gray-900">{{ $groupRequest->training_name }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-600">Date</dt>
                            <dd class="font-medium text-gray-900">{{ $groupRequest->training_date->format('F j, Y') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-600">Location</dt>
                            <dd class="font-medium text-gray-900">{{ $groupRequest->training_city }}, {{ $groupRequest->training_state }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-600">Trainer</dt>
                            <dd class="font-medium text-gray-900">{{ $groupRequest->trainer->full_name }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-600">Tickets</dt>
                            <dd class="font-medium text-gray-900">{{ $groupRequest->number_of_tickets }}</dd>
                        </div>
                        <div class="flex justify-between border-t border-gray-200 pt-2 mt-2">
                            <dt class="font-semibold text-brand-primary">Total Paid</dt>
                            <dd class="font-semibold text-brand-primary">{{ $groupRequest->total_formatted }}</dd>
                        </div>
                    </dl>
                </div>
            @endif

            <p class="text-sm text-gray-500">Your assigned trainer will be in touch with additional details about the training session. A confirmation email has been sent to your email address.</p>
        </div>
    </div>
</x-public-layout>
