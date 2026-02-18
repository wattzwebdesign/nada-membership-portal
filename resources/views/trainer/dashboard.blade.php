<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Trainer Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Trainer Setup Notices --}}
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
                                        <a href="{{ route('trainer.payouts.index') }}" class="ml-auto text-xs font-medium px-2.5 py-1 rounded text-white bg-brand-primary">Connect Stripe</a>
                                    </li>
                                @endif
                                @if(!$hasPlan)
                                    <li class="flex items-center gap-2 text-sm">
                                        <svg class="h-4 w-4 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        <span class="text-amber-800">Subscribe to a Registered Trainer plan</span>
                                        <a href="{{ route('membership.plans') }}" class="ml-auto text-xs font-medium px-2.5 py-1 rounded text-white bg-brand-primary">View Plans</a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Stats Row --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 rounded-lg bg-brand-primary/10"
                                <svg class="w-6 h-6 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Upcoming Trainings</p>
                                <p class="text-2xl font-bold text-brand-primary">{{ $upcomingTrainings->count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 rounded-lg bg-green-50">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Completions</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $totalCompletions }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 rounded-lg bg-brand-secondary/10"
                                <svg class="w-6 h-6 text-brand-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Earnings</p>
                                <p class="text-2xl font-bold text-brand-secondary">${{ number_format(($earningsSummary['trainer_earnings'] ?? 0) / 100, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Actions Grid --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-3">
                <a href="{{ route('trainer.profile.edit') }}" class="flex flex-col items-center p-4 bg-white shadow-sm sm:rounded-lg border border-gray-200 hover:bg-gray-50 transition text-center">
                    <div class="p-2 rounded-lg bg-brand-secondary/10"
                        <svg class="w-5 h-5 text-brand-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <p class="mt-2 text-xs font-medium text-gray-900">Edit Profile</p>
                </a>

                <a href="{{ route('trainer.trainings.index') }}" class="flex flex-col items-center p-4 bg-white shadow-sm sm:rounded-lg border border-gray-200 hover:bg-gray-50 transition text-center">
                    <div class="p-2 rounded-lg bg-blue-50">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    </div>
                    <p class="mt-2 text-xs font-medium text-gray-900">Trainings</p>
                </a>

                <a href="{{ route('trainer.registrations.index') }}" class="flex flex-col items-center p-4 bg-white shadow-sm sm:rounded-lg border border-gray-200 hover:bg-gray-50 transition text-center">
                    <div class="p-2 rounded-lg bg-blue-50">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <p class="mt-2 text-xs font-medium text-gray-900">Registrations</p>
                </a>

                <a href="{{ route('trainer.clinicals.index') }}" class="flex flex-col items-center p-4 bg-white shadow-sm sm:rounded-lg border border-gray-200 hover:bg-gray-50 transition text-center">
                    <div class="p-2 rounded-lg bg-green-50">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="mt-2 text-xs font-medium text-gray-900">Clinicals</p>
                </a>

                <a href="{{ route('trainer.payouts.index') }}" class="flex flex-col items-center p-4 bg-white shadow-sm sm:rounded-lg border border-gray-200 hover:bg-gray-50 transition text-center">
                    <div class="p-2 rounded-lg bg-brand-secondary/10"
                        <svg class="w-5 h-5 text-brand-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="mt-2 text-xs font-medium text-gray-900">Payouts</p>
                </a>

                <a href="{{ route('trainer.payouts.reports') }}" class="flex flex-col items-center p-4 bg-white shadow-sm sm:rounded-lg border border-gray-200 hover:bg-gray-50 transition text-center">
                    <div class="p-2 rounded-lg bg-purple-50">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <p class="mt-2 text-xs font-medium text-gray-900">Reports</p>
                </a>

                <a href="{{ route('trainer.broadcasts.index') }}" class="flex flex-col items-center p-4 bg-white shadow-sm sm:rounded-lg border border-gray-200 hover:bg-gray-50 transition text-center">
                    <div class="p-2 rounded-lg bg-indigo-50">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <p class="mt-2 text-xs font-medium text-gray-900">Broadcasts</p>
                </a>
            </div>

            {{-- Group Training Link Builder --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" x-data="groupTrainingLinkBuilder()">
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="flex-shrink-0 p-2 rounded-lg bg-brand-primary/10"
                            <svg class="w-5 h-5 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-brand-primary">Group Training Link</h3>
                            <p class="text-xs text-gray-500">Share this link with companies to pre-fill your group training form.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Price Per Ticket ($) <span class="text-gray-400">(optional)</span></label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-400 text-sm">$</span>
                                </div>
                                <input type="number" step="0.01" min="0" x-model="priceDollars" placeholder="0.00"
                                       class="pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Number of Tickets <span class="text-gray-400">(optional)</span></label>
                            <input type="number" min="1" x-model="tickets" placeholder="10"
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="text" readonly :value="generatedUrl"
                               class="flex-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm text-sm text-gray-700 font-mono">
                        <button type="button" @click="copyUrl()" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md bg-white hover:bg-gray-50 transition"
                                :class="copied ? 'text-green-600 border-green-300' : 'text-gray-700'">
                            <template x-if="!copied">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </template>
                            <template x-if="copied">
                                <span class="text-xs font-medium">Copied!</span>
                            </template>
                        </button>
                    </div>
                </div>
            </div>

            <script>
                function groupTrainingLinkBuilder() {
                    return {
                        priceDollars: '',
                        tickets: '',
                        copied: false,
                        get generatedUrl() {
                            let url = '{{ url("/group-training") }}?trainer={{ auth()->id() }}';
                            const priceCents = Math.round(parseFloat(this.priceDollars || 0) * 100);
                            if (priceCents > 0) url += '&price=' + priceCents;
                            if (parseInt(this.tickets) > 0) url += '&tickets=' + parseInt(this.tickets);
                            return url;
                        },
                        copyUrl() {
                            navigator.clipboard.writeText(this.generatedUrl);
                            this.copied = true;
                            setTimeout(() => this.copied = false, 2000);
                        }
                    };
                }
            </script>

            {{-- Pending / Denied Trainings --}}
            @if ($pendingTrainings->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-yellow-400">
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <h3 class="text-lg font-semibold text-brand-primary">Trainings Awaiting Action</h3>
                        </div>

                        @foreach ($pendingTrainings as $training)
                            @php
                                $tStatus = is_object($training->status) ? $training->status->value : $training->status;
                            @endphp
                            <div class="border-b border-gray-100 py-3 last:border-0">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1 min-w-0">
                                        <a href="{{ route('trainer.trainings.show', $training) }}" class="text-sm font-medium hover:underline line-clamp-1 text-brand-primary">
                                            {{ $training->title }}
                                        </a>
                                        <div class="flex items-center gap-2 mt-1">
                                            <p class="text-xs text-gray-500">{{ $training->start_date->format('M j, Y \a\t g:i A') }}</p>
                                            @if ($training->is_group)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-700">Group</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 ml-3">
                                        @if ($tStatus === 'pending_approval')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Pending Approval</span>
                                        @elseif ($tStatus === 'denied')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Denied</span>
                                        @endif
                                        <a href="{{ route('trainer.trainings.show', $training) }}" class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                                            View
                                        </a>
                                    </div>
                                </div>
                                @if ($tStatus === 'denied' && $training->denied_reason)
                                    <p class="mt-1 text-xs text-red-600 line-clamp-1">Reason: {{ $training->denied_reason }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Upcoming Trainings --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-brand-primary">Upcoming Trainings</h3>
                            <a href="{{ route('trainer.trainings.create') }}" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-brand-primary">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                New Training
                            </a>
                        </div>

                        @forelse ($upcomingTrainings as $training)
                            <div class="border-b border-gray-100 py-3 last:border-0">
                                <div>
                                    <a href="{{ route('trainer.trainings.show', $training) }}" class="text-sm font-medium hover:underline line-clamp-2 text-brand-primary">
                                        {{ $training->title }}
                                    </a>
                                    <div class="flex items-center justify-between mt-1.5">
                                        <div class="flex items-center gap-2">
                                            <p class="text-xs text-gray-500">{{ $training->start_date->format('M j, Y \a\t g:i A') }}</p>
                                            @if ($training->is_group)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-700">Group</span>
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">Private</span>
                                            @endif
                                        </div>
                                        <a href="{{ route('trainer.attendees.index', $training) }}" class="inline-flex items-center gap-1 text-xs font-medium text-blue-700 hover:text-blue-900">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            {{ $training->registrations_count ?? 0 }} registered
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-6">
                                <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                                <p class="mt-2 text-sm text-gray-500">No upcoming trainings.</p>
                            </div>
                        @endforelse

                        @if ($upcomingTrainings->count() > 0)
                            <div class="mt-4">
                                <a href="{{ route('trainer.trainings.index') }}" class="text-sm font-medium hover:underline text-brand-secondary">View All Trainings</a>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Clinicals Pending Review --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-brand-primary">Clinicals Pending Review</h3>
                            <a href="{{ route('trainer.clinicals.index') }}" class="text-sm font-medium hover:underline text-brand-secondary">View All</a>
                        </div>

                        @forelse ($pendingClinicals as $clinical)
                            <div class="border-b border-gray-100 py-3 last:border-0">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <a href="{{ route('trainer.clinicals.show', $clinical) }}" class="text-sm font-medium hover:underline text-brand-primary">
                                            {{ $clinical->user->full_name }}
                                        </a>
                                        <p class="text-xs text-gray-500 mt-0.5">Submitted {{ $clinical->created_at->diffForHumans() }}</p>
                                    </div>
                                    <a href="{{ route('trainer.clinicals.show', $clinical) }}" class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                                        Review
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-6">
                                <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p class="mt-2 text-sm text-gray-500">No clinicals pending review.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Recent Completions --}}
            @if (isset($recentCompletions) && $recentCompletions->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4 text-brand-primary">Recent Completions</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendee</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Training</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Completed</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($recentCompletions->take(5) as $completion)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $completion->user->full_name ?? 'N/A' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $completion->training->title ?? 'N/A' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $completion->completed_at ? $completion->completed_at->format('M j, Y') : 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
