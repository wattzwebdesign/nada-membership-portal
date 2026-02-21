<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Clinical Log: {{ $log->user->full_name }}
            </h2>
            <a href="{{ route('trainer.clinical-logs.index') }}" class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 hover:bg-gray-50">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to List
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

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

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Left Column: Log Details (2/3) --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Member Info --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4 text-brand-primary">Member Information</h3>
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $log->user->full_name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $log->user->email }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Created</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $log->created_at->format('M j, Y \a\t g:i A') }}</dd>
                                </div>
                                @if ($log->completed_at)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Submitted for Review</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $log->completed_at->format('M j, Y \a\t g:i A') }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    {{-- Hours Progress --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4 text-brand-primary">Hours Summary</h3>
                            @php
                                $totalHours = $log->total_hours;
                                $threshold = $log->hours_threshold;
                                $percent = $threshold > 0 ? min(100, round(($totalHours / $threshold) * 100, 1)) : 100;
                            @endphp
                            <div class="flex justify-between text-sm text-gray-500 mb-1">
                                <span>Total: <strong class="text-gray-900">{{ number_format($totalHours, 1) }}</strong> hrs</span>
                                <span>Threshold: <strong class="text-gray-900">{{ number_format($threshold, 0) }}</strong> hrs</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="h-3 rounded-full {{ $percent >= 100 ? 'bg-green-500' : 'bg-brand-secondary' }}" style="width: {{ $percent }}%"></div>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">{{ $log->entries->count() }} {{ Str::plural('entry', $log->entries->count()) }}</p>
                        </div>
                    </div>

                    {{-- Entries Table --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4 text-brand-primary">Clinical Entries</h3>

                            @if ($log->entries->count() > 0)
                                <div class="hidden md:block overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Protocol</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hours</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Files</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            @foreach ($log->entries as $entry)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $entry->date->format('M j, Y') }}</td>
                                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $entry->location }}</td>
                                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $entry->protocol }}</td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ number_format($entry->hours, 2) }}</td>
                                                    <td class="px-4 py-3 text-sm">
                                                        @php $media = $entry->getMedia('entry_attachments'); @endphp
                                                        @if ($media->count() > 0)
                                                            <div class="space-y-1">
                                                                @foreach ($media as $file)
                                                                    <a href="{{ $file->getUrl() }}" target="_blank" class="flex items-center gap-1 text-xs text-brand-primary hover:underline">
                                                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                                        {{ $file->file_name }}
                                                                    </a>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <span class="text-gray-400">--</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @if ($entry->notes)
                                                    <tr class="bg-gray-50/50">
                                                        <td colspan="5" class="px-4 py-2 text-xs text-gray-500">
                                                            <strong>Notes:</strong> {{ $entry->notes }}
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                        <tfoot class="bg-gray-50">
                                            <tr>
                                                <td colspan="3" class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">Total</td>
                                                <td class="px-4 py-3 text-sm font-bold text-gray-900">{{ number_format($totalHours, 2) }} hrs</td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                {{-- Mobile Cards --}}
                                <div class="md:hidden space-y-3">
                                    @foreach ($log->entries as $entry)
                                        <div class="border border-gray-200 rounded-lg p-3">
                                            <div class="flex justify-between mb-1">
                                                <span class="text-sm font-medium text-gray-900">{{ $entry->date->format('M j, Y') }}</span>
                                                <span class="text-sm font-bold">{{ number_format($entry->hours, 2) }} hrs</span>
                                            </div>
                                            <p class="text-xs text-gray-700">{{ $entry->location }}</p>
                                            <p class="text-xs text-gray-500">{{ $entry->protocol }}</p>
                                            @if ($entry->notes)
                                                <p class="text-xs text-gray-400 mt-1">{{ $entry->notes }}</p>
                                            @endif
                                            @php $media = $entry->getMedia('entry_attachments'); @endphp
                                            @if ($media->count() > 0)
                                                <div class="mt-2 flex flex-wrap gap-2">
                                                    @foreach ($media as $file)
                                                        <a href="{{ $file->getUrl() }}" target="_blank" class="text-xs text-brand-primary hover:underline">{{ $file->file_name }}</a>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-gray-500">No entries in this log book.</p>
                            @endif
                        </div>
                    </div>

                    {{-- Review Status --}}
                    @if ($log->reviewer)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold mb-4 text-brand-primary">Review History</h3>
                                <p class="text-sm text-gray-500">
                                    Reviewed by <span class="font-medium text-gray-700">{{ $log->reviewer->full_name }}</span>
                                    on {{ $log->reviewed_at->format('M j, Y \a\t g:i A') }}
                                </p>
                                @if ($log->reviewer_notes)
                                    <p class="text-sm text-gray-700 mt-2 whitespace-pre-line">{{ $log->reviewer_notes }}</p>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Right Column: Actions & Certificate Sidebar (1/3) --}}
                <div class="lg:col-span-1 space-y-6 lg:sticky lg:top-8 lg:self-start">

                    {{-- Status --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4 text-brand-primary">Status</h3>
                            @php
                                $statusColors = [
                                    'in_progress' => 'bg-blue-100 text-blue-800',
                                    'completed' => 'bg-yellow-100 text-yellow-800',
                                    'approved' => 'bg-green-100 text-green-800',
                                    'rejected' => 'bg-red-100 text-red-800',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusColors[$log->status->value] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ $log->status->label() }}
                            </span>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    @if ($log->status === \App\Enums\ClinicalLogStatus::Completed)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold mb-4 text-brand-primary">Actions</h3>
                                <div class="flex flex-col gap-3">
                                    <form method="POST" action="{{ route('trainer.clinical-logs.approve', $log) }}">
                                        @csrf
                                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 transition">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Approve
                                        </button>
                                    </form>

                                    <div x-data="{ showReject: false }">
                                        <button @click="showReject = !showReject" type="button" class="w-full inline-flex items-center justify-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 transition">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Reject & Return
                                        </button>

                                        <div x-show="showReject" x-cloak class="mt-3">
                                            <form method="POST" action="{{ route('trainer.clinical-logs.reject', $log) }}">
                                                @csrf
                                                <div class="mb-3">
                                                    <label for="reviewer_notes" class="block text-sm font-medium text-gray-700 mb-1">Notes for Member</label>
                                                    <textarea name="reviewer_notes" id="reviewer_notes" rows="3" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Explain what needs to be corrected..."></textarea>
                                                    @error('reviewer_notes')
                                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 transition">
                                                    Confirm Return
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Certificate --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4 text-brand-primary">Certificate</h3>

                            @if ($log->status === \App\Enums\ClinicalLogStatus::Approved && $hasCertificate)
                                <div class="text-center">
                                    <div class="mx-auto w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mb-3">
                                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900">Certificate Issued</p>
                                    <p class="text-xs text-gray-500 mt-1">This member has been issued their NADA certificate.</p>
                                </div>

                            @elseif ($log->status === \App\Enums\ClinicalLogStatus::Approved && !$hasCertificate)
                                <div class="text-center">
                                    <div class="mx-auto w-16 h-16 rounded-full flex items-center justify-center mb-3 bg-brand-secondary/10">
                                        <svg class="w-8 h-8 text-brand-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.125 2.25h-4.5c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9zm3.75 11.625l-2.625 2.625L9.75 15"/></svg>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900">Ready to Issue</p>
                                    <p class="text-xs text-gray-500 mt-1 mb-4">Log book approved. Issue the NADA certificate for this member.</p>
                                    <form method="POST" action="{{ route('trainer.clinical-logs.issue-certificate', $log) }}" onsubmit="return confirm('Are you sure you want to issue a certificate for this member? This action cannot be undone.')">
                                        @csrf
                                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-md text-white hover:opacity-90 transition bg-brand-secondary">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.125 2.25h-4.5c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9zm3.75 11.625l-2.625 2.625L9.75 15"/></svg>
                                            Issue Certificate
                                        </button>
                                    </form>
                                </div>

                            @elseif ($log->status === \App\Enums\ClinicalLogStatus::Completed)
                                <div class="text-center">
                                    <div class="mx-auto w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900">Awaiting Review</p>
                                    <p class="text-xs text-gray-500 mt-1">Approve this log book to issue a certificate.</p>
                                </div>

                            @else
                                <div class="text-center">
                                    <div class="mx-auto w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900">{{ $log->status->label() }}</p>
                                    <p class="text-xs text-gray-500 mt-1">The member is still working on this log book.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
