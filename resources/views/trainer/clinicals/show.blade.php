<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Clinical Submission: {{ $clinical->first_name }} {{ $clinical->last_name }}
            </h2>
            <a href="{{ route('trainer.clinicals.index') }}" class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 hover:bg-gray-50">
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
                {{-- Left Column: Clinical Details (2/3) --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Member Info --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4 text-brand-primary">Member Information</h3>
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $clinical->first_name }} {{ $clinical->last_name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $clinical->email }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Submitted</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $clinical->created_at->format('M j, Y \a\t g:i A') }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    {{-- Training Details --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4 text-brand-primary">Training Details</h3>
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Estimated Training Date</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $clinical->estimated_training_date?->format('M j, Y') ?? 'Not provided' }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    {{-- Treatment Logs --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4 text-brand-primary">Treatment Logs</h3>
                            @php
                                $media = $clinical->getMedia('treatment_logs');
                            @endphp
                            @if ($media->count() > 0)
                                <div class="space-y-2">
                                    @foreach ($media as $file)
                                        <a href="{{ $file->getUrl() }}" target="_blank" class="flex items-center gap-2 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                                            <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate">{{ $file->file_name }}</p>
                                                <p class="text-xs text-gray-500">{{ number_format($file->size / 1024, 1) }} KB</p>
                                            </div>
                                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-gray-500">No treatment logs uploaded.</p>
                            @endif
                        </div>
                    </div>

                    {{-- Notes --}}
                    @if ($clinical->notes)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold mb-4 text-brand-primary">Notes</h3>
                                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $clinical->notes }}</p>
                            </div>
                        </div>
                    @endif

                    {{-- Review Status --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4 text-brand-primary">Review Status</h3>
                            <div class="flex items-center gap-3 mb-4">
                                @if ($clinical->status === 'submitted')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">Submitted</span>
                                @elseif ($clinical->status === 'under_review')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">Under Review</span>
                                @elseif ($clinical->status === 'approved')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">Approved</span>
                                @elseif ($clinical->status === 'rejected')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">Rejected</span>
                                @endif
                            </div>

                            @if ($clinical->reviewer)
                                <p class="text-sm text-gray-500">
                                    Reviewed by <span class="font-medium text-gray-700">{{ $clinical->reviewer->full_name }}</span>
                                    on {{ $clinical->reviewed_at->format('M j, Y \a\t g:i A') }}
                                </p>
                            @endif
                        </div>
                    </div>


                </div>

                {{-- Right Column: Actions & Certificate Sidebar (1/3) --}}
                <div class="lg:col-span-1 space-y-6 lg:sticky lg:top-8 lg:self-start">

                    {{-- Action Buttons --}}
                    @if (in_array($clinical->status, ['submitted', 'under_review']))
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold mb-4 text-brand-primary">Actions</h3>
                                <div class="flex flex-col gap-3">
                                    {{-- Approve --}}
                                    <form method="POST" action="{{ route('trainer.clinicals.approve', $clinical) }}">
                                        @csrf
                                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 transition">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Approve
                                        </button>
                                    </form>

                                    {{-- Reject --}}
                                    <div x-data="{ showReject: false }">
                                        <button @click="showReject = !showReject" type="button" class="w-full inline-flex items-center justify-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 transition">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Reject
                                        </button>

                                        <div x-show="showReject" x-cloak class="mt-3">
                                            <form method="POST" action="{{ route('trainer.clinicals.reject', $clinical) }}">
                                                @csrf
                                                <div class="mb-3">
                                                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Rejection Reason</label>
                                                    <textarea name="notes" id="notes" rows="3" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Explain why this submission is being rejected..."></textarea>
                                                    @error('notes')
                                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 transition">
                                                    Confirm Rejection
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

                            @if ($clinical->status === 'approved' && $hasCertificate)
                                {{-- Certificate already issued --}}
                                <div class="text-center">
                                    <div class="mx-auto w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mb-3">
                                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900">Certificate Issued</p>
                                    <p class="text-xs text-gray-500 mt-1">This member has been issued their NADA certificate.</p>
                                </div>

                            @elseif ($clinical->status === 'approved' && !$hasCertificate)
                                {{-- Ready to issue --}}
                                <div class="text-center">
                                    <div class="mx-auto w-16 h-16 rounded-full flex items-center justify-center mb-3 bg-brand-secondary/10">
                                        <svg class="w-8 h-8 text-brand-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.125 2.25h-4.5c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9zm3.75 11.625l-2.625 2.625L9.75 15"/></svg>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900">Ready to Issue</p>
                                    <p class="text-xs text-gray-500 mt-1 mb-4">Clinicals approved. Issue the NADA certificate for this member.</p>
                                    <form method="POST" action="{{ route('trainer.clinicals.issue-certificate', $clinical) }}" onsubmit="return confirm('Are you sure you want to issue a certificate for this member? This action cannot be undone.')">
                                        @csrf
                                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-md text-white hover:opacity-90 transition bg-brand-secondary">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.125 2.25h-4.5c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9zm3.75 11.625l-2.625 2.625L9.75 15"/></svg>
                                            Issue Certificate
                                        </button>
                                    </form>
                                </div>

                            @elseif ($clinical->status === 'rejected')
                                {{-- Rejected --}}
                                <div class="text-center">
                                    <div class="mx-auto w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mb-3">
                                        <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900">Submission Rejected</p>
                                    <p class="text-xs text-gray-500 mt-1">This clinical was rejected and cannot receive a certificate.</p>
                                </div>

                            @else
                                {{-- Pending review --}}
                                <div class="text-center">
                                    <div class="mx-auto w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900">Awaiting Review</p>
                                    <p class="text-xs text-gray-500 mt-1">Approve these clinicals to issue a certificate.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
