<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Trainer Setup Notices --}}
            @if(auth()->user()->isTrainer())
                @php
                    $hasStripe = auth()->user()->hasConnectedStripeAccount();
                    $hasPlan = auth()->user()->hasActiveTrainerPlan();
                @endphp
                @if(!$hasStripe || !$hasPlan)
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-5">
                        <div class="flex items-start gap-3">
                            <svg class="h-6 w-6 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                            <div class="flex-1">
                                <h3 class="text-sm font-semibold text-amber-800">Complete your Trainer setup</h3>
                                <p class="text-sm text-amber-700 mt-1">You need to complete the following before you can create and manage trainings:</p>
                                <ul class="mt-3 space-y-2">
                                    @if(!$hasStripe)
                                        <li class="flex items-center gap-2 text-sm">
                                            <svg class="h-4 w-4 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            <span class="text-amber-800">Connect your Stripe account for payouts</span>
                                            <a href="{{ route('trainer.payouts.index') }}" class="ml-auto text-xs font-medium px-2.5 py-1 rounded text-white" style="background-color: #374269;">Connect Stripe</a>
                                        </li>
                                    @endif
                                    @if(!$hasPlan)
                                        <li class="flex items-center gap-2 text-sm">
                                            <svg class="h-4 w-4 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            <span class="text-amber-800">Subscribe to a Registered Trainer plan</span>
                                            <a href="{{ route('membership.plans') }}" class="ml-auto text-xs font-medium px-2.5 py-1 rounded text-white" style="background-color: #374269;">View Plans</a>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            {{-- Top Row: Membership Status & Certificates --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Membership Status Card --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold" style="color: #374269;">Membership Status</h3>
                            @if ($subscription)
                                @php
                                    $statusColors = [
                                        'active' => 'bg-green-100 text-green-800',
                                        'past_due' => 'bg-yellow-100 text-yellow-800',
                                        'canceled' => 'bg-red-100 text-red-800',
                                        'trialing' => 'bg-blue-100 text-blue-800',
                                        'incomplete' => 'bg-gray-100 text-gray-800',
                                        'unpaid' => 'bg-red-100 text-red-800',
                                        'paused' => 'bg-gray-100 text-gray-800',
                                    ];
                                    $statusColor = $statusColors[$subscription->status] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                    {{ ucfirst(str_replace('_', ' ', $subscription->status)) }}
                                </span>
                            @endif
                        </div>

                        @if ($subscription)
                            <div class="space-y-3">
                                <div>
                                    <p class="text-sm text-gray-500">Current Plan</p>
                                    <p class="text-base font-medium text-gray-900">{{ $subscription->plan->name ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Renewal Date</p>
                                    <p class="text-base font-medium text-gray-900">
                                        {{ $subscription->current_period_end ? $subscription->current_period_end->format('F j, Y') : 'N/A' }}
                                    </p>
                                </div>
                                @if ($subscription->cancel_at_period_end)
                                    <div class="bg-yellow-50 border border-yellow-200 rounded-md p-3">
                                        <p class="text-sm text-yellow-700">Your subscription will cancel at the end of the current period.</p>
                                    </div>
                                @endif
                            </div>
                            <div class="mt-5">
                                <a href="{{ route('billing.index') }}" class="inline-flex items-center text-sm font-medium hover:underline" style="color: #d39c27;">
                                    Manage Billing
                                    <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <p class="text-gray-500 mb-4">You do not have an active membership.</p>
                                <a href="{{ route('membership.plans') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white" style="background-color: #374269;">
                                    View Plans
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Certificates Card --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold" style="color: #374269;">Certificates</h3>
                            @if ($certificates->count() > 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ $certificates->where('status', 'active')->count() }} Active
                                </span>
                            @endif
                        </div>

                        @if ($certificates->count() > 0)
                            <div class="space-y-3">
                                @foreach ($certificates->take(3) as $certificate)
                                    <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $certificate->certificate_code }}</p>
                                            <p class="text-xs text-gray-500">Issued {{ $certificate->date_issued->format('M j, Y') }}</p>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            @if ($certificate->status === 'active')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Active</span>
                                            @elseif ($certificate->status === 'expired')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Expired</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">{{ ucfirst($certificate->status) }}</span>
                                            @endif
                                            <a href="{{ route('certificates.download', $certificate) }}" class="text-gray-400 hover:text-gray-600" title="Download">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('certificates.index') }}" class="inline-flex items-center text-sm font-medium hover:underline" style="color: #d39c27;">
                                    View All Certificates
                                    <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                <p class="mt-2 text-sm text-gray-500">No certificates yet.</p>
                                <p class="text-xs text-gray-400">Complete a training to earn your certificate.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Second Row: Upcoming Trainings & Quick Actions --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Upcoming Trainings Card --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold" style="color: #374269;">Upcoming Trainings</h3>
                        </div>

                        @if ($upcomingTrainings->count() > 0)
                            <div class="space-y-3">
                                @foreach ($upcomingTrainings->take(3) as $registration)
                                    <div class="border border-gray-100 rounded-lg p-3">
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ $registration->training->title }}</p>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    {{ $registration->training->start_date->format('M j, Y \a\t g:i A') }}
                                                </p>
                                            </div>
                                            @php
                                                $typeBadgeColors = [
                                                    'in_person' => 'bg-blue-100 text-blue-800',
                                                    'virtual' => 'bg-purple-100 text-purple-800',
                                                    'hybrid' => 'bg-indigo-100 text-indigo-800',
                                                ];
                                                $typeBadgeColor = $typeBadgeColors[$registration->training->type] ?? 'bg-gray-100 text-gray-800';
                                            @endphp
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $typeBadgeColor }}">
                                                {{ ucfirst(str_replace('_', ' ', $registration->training->type)) }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('trainings.my-registrations') }}" class="inline-flex items-center text-sm font-medium hover:underline" style="color: #d39c27;">
                                    View All Registrations
                                    <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                                <p class="mt-2 text-sm text-gray-500">No upcoming trainings.</p>
                                <a href="{{ route('trainings.index') }}" class="mt-1 text-xs font-medium hover:underline" style="color: #d39c27;">Browse Trainings</a>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Quick Actions Card --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4" style="color: #374269;">Quick Actions</h3>

                        <div class="grid grid-cols-2 gap-3">
                            @if ($certificates->where('status', 'active')->count() > 0)
                                <a href="{{ route('certificates.index') }}" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:border-gray-300 hover:bg-gray-50 transition">
                                    <svg class="w-6 h-6 mb-2" style="color: #374269;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <span class="text-sm font-medium text-gray-700">Download Certificate</span>
                                </a>
                            @endif

                            <a href="{{ route('trainings.index') }}" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:border-gray-300 hover:bg-gray-50 transition">
                                <svg class="w-6 h-6 mb-2" style="color: #374269;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span class="text-sm font-medium text-gray-700">Register for Training</span>
                            </a>

                            <a href="{{ route('clinicals.create') }}" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:border-gray-300 hover:bg-gray-50 transition">
                                <svg class="w-6 h-6 mb-2" style="color: #374269;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span class="text-sm font-medium text-gray-700">Submit Clinicals</span>
                            </a>

                            <a href="{{ route('discount.request.create') }}" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:border-gray-300 hover:bg-gray-50 transition">
                                <svg class="w-6 h-6 mb-2" style="color: #374269;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                                <span class="text-sm font-medium text-gray-700">Request Discount</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
