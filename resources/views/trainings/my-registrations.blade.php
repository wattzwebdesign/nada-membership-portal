<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Training Registrations') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-brand-primary">My Registrations</h3>
                        <a href="{{ route('trainings.index') }}" class="inline-flex items-center text-sm font-medium text-brand-primary">
                            Browse Trainings
                            <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>

                    @if ($registrations->count() > 0)
                        {{-- Desktop Table --}}
                        <div class="hidden md:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Training</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount Paid</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($registrations as $registration)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4">
                                                <a href="{{ route('trainings.show', $registration->training) }}" class="text-sm font-medium hover:underline text-brand-primary">
                                                    {{ $registration->training->title }}
                                                </a>
                                                <p class="text-xs text-gray-500">{{ $registration->training->trainer->full_name ?? '' }}</p>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $registration->training->start_date->format('M j, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $typeBadgeColors = [
                                                        'in_person' => 'bg-blue-100 text-blue-800',
                                                        'virtual' => 'bg-purple-100 text-purple-800',
                                                        'hybrid' => 'bg-indigo-100 text-indigo-800',
                                                    ];
                                                    $typeBadgeColor = $typeBadgeColors[$registration->training->type->value] ?? 'bg-gray-100 text-gray-800';
                                                @endphp
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $typeBadgeColor }}">
                                                    {{ $registration->training->type->label() }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $regStatusColors = [
                                                        'registered' => 'bg-blue-100 text-blue-800',
                                                        'attended' => 'bg-yellow-100 text-yellow-800',
                                                        'completed' => 'bg-green-100 text-green-800',
                                                        'no_show' => 'bg-red-100 text-red-800',
                                                        'canceled' => 'bg-gray-100 text-gray-800',
                                                    ];
                                                    $regStatusColor = $regStatusColors[$registration->status->value] ?? 'bg-gray-100 text-gray-800';
                                                @endphp
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $regStatusColor }}">
                                                    {{ $registration->status->label() }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @if ($registration->amount_paid_cents > 0)
                                                    ${{ number_format($registration->amount_paid_cents / 100, 2) }}
                                                @else
                                                    Free
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-3">
                                                @if ($registration->status === App\Enums\RegistrationStatus::Registered && $registration->training->start_date->isFuture())
                                                    <a href="{{ route('trainings.wallet.apple', $registration->training) }}" class="inline-block" title="Add to Apple Wallet">
                                                        <img src="{{ asset('images/add-to-apple-wallet.svg') }}" alt="Add to Apple Wallet" class="h-8 inline">
                                                    </a>
                                                    <a href="{{ route('trainings.wallet.google', $registration->training) }}" class="inline-block" title="Add to Google Wallet">
                                                        <img src="{{ asset('images/add-to-google-wallet.svg') }}" alt="Add to Google Wallet" class="h-8 inline">
                                                    </a>
                                                    <form method="POST" action="{{ route('trainings.cancel-registration', $registration->training) }}" onsubmit="return confirm('Cancel your registration?');" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">Cancel</button>
                                                    </form>
                                                @endif
                                                @if ($registration->invoice_id)
                                                    <a href="{{ route('invoices.show', $registration->invoice_id) }}" class="text-sm font-medium text-brand-primary">
                                                        View Invoice
                                                    </a>
                                                @endif
                                                @if ($registration->certificate_id)
                                                    <a href="{{ route('certificates.download', $registration->certificate_id) }}" class="text-sm font-medium text-brand-primary">
                                                        Download Certificate
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Mobile Cards --}}
                        <div class="md:hidden space-y-3">
                            @foreach ($registrations as $registration)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <a href="{{ route('trainings.show', $registration->training) }}" class="text-sm font-medium hover:underline text-brand-primary">
                                            {{ $registration->training->title }}
                                        </a>
                                        @php
                                            $regStatusColor = $regStatusColors[$registration->status->value] ?? 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $regStatusColor }}">
                                            {{ $registration->status->label() }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500">{{ $registration->training->start_date->format('M j, Y \a\t g:i A') }}</p>
                                    <p class="text-xs text-gray-400">Trainer: {{ $registration->training->trainer->full_name ?? '' }}</p>
                                    @if ($registration->amount_paid_cents > 0)
                                        <p class="text-xs text-gray-500 mt-1">Paid: ${{ number_format($registration->amount_paid_cents / 100, 2) }}</p>
                                    @endif
                                    <div class="flex flex-wrap items-center gap-3 mt-3 pt-2 border-t border-gray-100">
                                        @if ($registration->status === App\Enums\RegistrationStatus::Registered && $registration->training->start_date->isFuture())
                                            @if ($registration->walletPasses->isNotEmpty())
                                                <a href="{{ route('trainings.wallet.apple', $registration->training) }}" title="Add to Apple Wallet">
                                                    <img src="{{ asset('images/add-to-apple-wallet.svg') }}" alt="Add to Apple Wallet" class="h-7">
                                                </a>
                                                <a href="{{ route('trainings.wallet.google', $registration->training) }}" title="Add to Google Wallet">
                                                    <img src="{{ asset('images/add-to-google-wallet.svg') }}" alt="Add to Google Wallet" class="h-7">
                                                </a>
                                            @endif
                                            <form method="POST" action="{{ route('trainings.cancel-registration', $registration->training) }}" onsubmit="return confirm('Cancel your registration?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium">Cancel</button>
                                            </form>
                                        @endif
                                        @if ($registration->invoice_id)
                                            <a href="{{ route('invoices.show', $registration->invoice_id) }}" class="text-xs font-medium text-brand-primary">
                                                View Invoice
                                            </a>
                                        @endif
                                        @if ($registration->certificate_id)
                                            <a href="{{ route('certificates.download', $registration->certificate_id) }}" class="text-xs font-medium text-brand-primary">
                                                Download Certificate
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if ($registrations->hasPages())
                            <div class="mt-6">
                                {{ $registrations->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                            <h3 class="mt-3 text-sm font-medium text-gray-900">No Registrations</h3>
                            <p class="mt-1 text-sm text-gray-500">You haven't registered for any trainings yet.</p>
                            <div class="mt-6">
                                <a href="{{ route('trainings.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-brand-primary">
                                    Browse Trainings
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
