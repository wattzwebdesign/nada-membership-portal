<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Broadcast Email') }}
        </h2>
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

                {{-- Compose Form --}}
                <div class="lg:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6" x-data="broadcastForm()">
                            <h3 class="text-lg font-semibold mb-4" style="color: #374269;">Compose Broadcast</h3>

                            <form method="POST" action="{{ route('trainer.broadcasts.store') }}" @submit="return confirmSend($event)">
                                @csrf

                                {{-- Training Picker --}}
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Trainings</label>

                                    {{-- Search --}}
                                    <input type="text" x-model="search" placeholder="Search trainings..."
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm mb-2">

                                    {{-- Selected Pills --}}
                                    <div class="flex flex-wrap gap-1.5 mb-2" x-show="selectedIds.length > 0">
                                        <template x-for="id in selectedIds" :key="id">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium text-white" style="background-color: #374269;">
                                                <span x-text="getTrainingTitle(id)"></span>
                                                <button type="button" @click="toggleTraining(id)" class="hover:text-gray-200">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </span>
                                        </template>
                                    </div>

                                    {{-- Checkbox List --}}
                                    <div class="border border-gray-200 rounded-md max-h-48 overflow-y-auto">
                                        @forelse ($trainings as $training)
                                            <label x-show="'{{ strtolower(addslashes($training->title)) }}'.includes(search.toLowerCase()) || search === ''"
                                                   class="flex items-center gap-3 px-3 py-2.5 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-0">
                                                <input type="checkbox" name="training_ids[]" value="{{ $training->id }}"
                                                       :checked="selectedIds.includes({{ $training->id }})"
                                                       @change="toggleTraining({{ $training->id }})"
                                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $training->title }}</p>
                                                    <p class="text-xs text-gray-500">{{ $training->start_date->format('M j, Y') }} &middot; {{ $training->registrations_count }} registrant(s)</p>
                                                </div>
                                            </label>
                                        @empty
                                            <div class="px-3 py-4 text-center text-sm text-gray-500">
                                                No published or completed trainings found.
                                            </div>
                                        @endforelse
                                    </div>

                                    @error('training_ids')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Recipient Count --}}
                                <div class="mb-4 flex items-center gap-2">
                                    <span class="text-sm text-gray-600">Recipients:</span>
                                    <span class="text-sm font-semibold" :class="recipientCount > 0 ? 'text-green-700' : 'text-gray-400'" x-text="loading ? '...' : recipientCount"></span>
                                </div>

                                {{-- Subject --}}
                                <div class="mb-4">
                                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                                    <input type="text" id="subject" name="subject" value="{{ old('subject') }}" required
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                           placeholder="Enter email subject...">
                                    @error('subject')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Body --}}
                                <div class="mb-4">
                                    <label for="body" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                                    <textarea id="body" name="body" rows="8" required
                                              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                              placeholder="Write your message here. Blank lines will create new paragraphs.">{{ old('body') }}</textarea>
                                    <p class="mt-1 text-xs text-gray-500">Plain text only. Separate paragraphs with a blank line.</p>
                                    @error('body')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Submit --}}
                                <button type="submit"
                                        :disabled="selectedIds.length === 0 || recipientCount === 0"
                                        class="w-full inline-flex justify-center items-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-md text-white transition disabled:opacity-50 disabled:cursor-not-allowed"
                                        style="background-color: #374269;">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    Send Broadcast
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Sent History Sidebar --}}
                <div class="lg:col-span-1">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h4 class="text-lg font-semibold mb-4" style="color: #374269;">Sent History</h4>

                            @forelse ($broadcasts as $broadcast)
                                <div class="border-b border-gray-100 py-3 last:border-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $broadcast->subject }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $broadcast->sent_at->format('M j, Y \a\t g:i A') }} &middot; {{ $broadcast->recipient_count }} recipient(s)</p>
                                    <div class="flex flex-wrap gap-1 mt-1.5">
                                        @foreach ($broadcast->trainings as $training)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600 truncate max-w-[150px]">
                                                {{ $training->title }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-6">
                                    <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    <p class="mt-2 text-sm text-gray-500">No broadcasts sent yet.</p>
                                </div>
                            @endforelse

                            @if ($broadcasts->hasPages())
                                <div class="mt-4">
                                    {{ $broadcasts->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        function broadcastForm() {
            const preselected = @json($preselectedTrainingId ? [(int) $preselectedTrainingId] : []);
            const trainingsData = @json($trainings->map(fn ($t) => ['id' => $t->id, 'title' => $t->title]));

            return {
                search: '',
                selectedIds: preselected,
                recipientCount: 0,
                loading: false,

                init() {
                    if (this.selectedIds.length > 0) {
                        this.fetchRecipientCount();
                    }
                },

                getTrainingTitle(id) {
                    const t = trainingsData.find(t => t.id === id);
                    return t ? t.title : '';
                },

                toggleTraining(id) {
                    const idx = this.selectedIds.indexOf(id);
                    if (idx > -1) {
                        this.selectedIds.splice(idx, 1);
                    } else {
                        this.selectedIds.push(id);
                    }
                    this.fetchRecipientCount();
                },

                async fetchRecipientCount() {
                    if (this.selectedIds.length === 0) {
                        this.recipientCount = 0;
                        return;
                    }

                    this.loading = true;
                    try {
                        const res = await fetch('{{ route("trainer.broadcasts.recipient-count") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ training_ids: this.selectedIds }),
                        });
                        const data = await res.json();
                        this.recipientCount = data.count || 0;
                    } catch {
                        this.recipientCount = 0;
                    } finally {
                        this.loading = false;
                    }
                },

                confirmSend(e) {
                    if (!confirm(`Send this broadcast to ${this.recipientCount} recipient(s)?`)) {
                        e.preventDefault();
                        return false;
                    }
                    return true;
                },
            };
        }
    </script>
</x-app-layout>
