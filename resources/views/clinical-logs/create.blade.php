<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">New Clinical Log Book</h2>
            <a href="{{ route('clinical-logs.index') }}" class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 hover:bg-gray-50">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <p class="text-sm text-gray-600 mb-6">Create a new log book to start tracking your clinical hours. You can optionally assign a trainer who will review your log when complete.</p>

                    <form method="POST" action="{{ route('clinical-logs.store') }}">
                        @csrf

                        <div class="mb-6" x-data="{ trainerNotListed: false }">
                            <label for="trainer_id" class="block text-sm font-medium text-gray-700 mb-1">Assign Trainer</label>

                            <div class="flex items-center gap-2 mb-2">
                                <input type="checkbox" id="trainer_not_listed" x-model="trainerNotListed" class="rounded border-gray-300 text-brand-primary focus:ring-brand-primary">
                                <label for="trainer_not_listed" class="text-sm text-gray-500">My trainer is not listed / I'll assign later</label>
                            </div>

                            <select name="trainer_id" id="trainer_id" x-show="!trainerNotListed" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary text-sm">
                                <option value="">-- Select a trainer --</option>
                                @foreach ($trainers as $trainer)
                                    <option value="{{ $trainer->id }}" {{ old('trainer_id') == $trainer->id ? 'selected' : '' }}>
                                        {{ $trainer->last_name }}, {{ $trainer->first_name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('trainer_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-6 py-2.5 border border-transparent text-sm font-medium rounded-md text-white bg-brand-primary hover:opacity-90 transition">
                                Create Log Book
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
