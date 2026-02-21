<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Clinical Log Book #{{ $log->id }}</h2>
            <a href="{{ route('clinical-logs.index') }}" class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 hover:bg-gray-50">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Log Books
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

            {{-- Header card: status, trainer, progress --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {{-- Status --}}
                        <div>
                            <p class="text-sm font-medium text-gray-500 mb-1">Status</p>
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

                        {{-- Trainer --}}
                        <div>
                            <p class="text-sm font-medium text-gray-500 mb-1">Assigned Trainer</p>
                            <p class="text-sm text-gray-900">{{ $log->trainer?->full_name ?? 'Not assigned' }}</p>
                        </div>

                        {{-- Progress --}}
                        <div>
                            <p class="text-sm font-medium text-gray-500 mb-1">Hours Progress</p>
                            @php
                                $totalHours = $log->total_hours;
                                $percent = $threshold > 0 ? min(100, round(($totalHours / $threshold) * 100, 1)) : 100;
                            @endphp
                            <div class="flex justify-between text-xs text-gray-500 mb-1">
                                <span>{{ number_format($totalHours, 1) }} / {{ number_format($threshold, 0) }} hrs</span>
                                <span>{{ $percent }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div class="h-2.5 rounded-full transition-all {{ $percent >= 100 ? 'bg-green-500' : 'bg-brand-secondary' }}" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Reviewer notes (if rejected) --}}
                    @if ($log->reviewer_notes && $log->status === \App\Enums\ClinicalLogStatus::InProgress)
                        <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-md">
                            <p class="text-sm font-medium text-red-800 mb-1">Reviewer Notes (revision requested):</p>
                            <p class="text-sm text-red-700 whitespace-pre-line">{{ $log->reviewer_notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Trainer assignment update (only when in progress) --}}
            @if ($log->status === \App\Enums\ClinicalLogStatus::InProgress)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-sm font-semibold text-brand-primary mb-3">Update Trainer Assignment</h3>
                        <form method="POST" action="{{ route('clinical-logs.update', $log) }}" class="flex items-end gap-3">
                            @csrf
                            @method('PUT')
                            <div class="flex-1">
                                <select name="trainer_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary text-sm">
                                    <option value="">-- No trainer --</option>
                                    @foreach ($trainers as $trainer)
                                        <option value="{{ $trainer->id }}" {{ $log->trainer_id == $trainer->id ? 'selected' : '' }}>
                                            {{ $trainer->last_name }}, {{ $trainer->first_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition">
                                Update
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Livewire entry manager --}}
            <livewire:clinical-log-entry-manager :clinicalLog="$log" />

            {{-- Mark Complete button --}}
            @if ($log->status === \App\Enums\ClinicalLogStatus::InProgress && $log->meets_threshold)
                <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <div class="mx-auto w-12 h-12 rounded-full bg-green-100 flex items-center justify-center mb-3">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h3 class="text-sm font-medium text-gray-900 mb-1">Ready to Submit</h3>
                        <p class="text-xs text-gray-500 mb-4">You've met the {{ number_format($threshold, 0) }}-hour requirement. Submit your log book for trainer review.</p>
                        <form method="POST" action="{{ route('clinical-logs.complete', $log) }}" onsubmit="return confirm('Are you sure? Once submitted, you cannot add or edit entries until a trainer reviews it.')">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-6 py-2.5 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 transition">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Mark Complete & Submit for Review
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Delete log (only if in progress with no entries) --}}
            @if ($log->status === \App\Enums\ClinicalLogStatus::InProgress && $log->entries->isEmpty())
                <div class="mt-4 text-right">
                    <form method="POST" action="{{ route('clinical-logs.destroy', $log) }}" onsubmit="return confirm('Are you sure you want to delete this log book?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm text-red-600 hover:text-red-800 transition">
                            Delete this log book
                        </button>
                    </form>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
