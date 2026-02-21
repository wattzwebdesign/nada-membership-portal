<div>
    {{-- Progress bar --}}
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
        <div class="p-6">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold text-brand-primary">Clinical Entries</h3>
                @if (! $this->isReadOnly)
                    <button wire:click="openForm" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-brand-primary hover:opacity-90 transition">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Entry
                    </button>
                @endif
            </div>

            <div class="flex justify-between text-sm text-gray-500 mb-1">
                <span>Total: <strong class="text-gray-900">{{ number_format($this->totalHours, 1) }}</strong> hrs</span>
                <span>Threshold: <strong class="text-gray-900">{{ number_format($this->threshold, 0) }}</strong> hrs</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div class="h-2.5 rounded-full transition-all {{ $this->progressPercent >= 100 ? 'bg-green-500' : 'bg-brand-secondary' }}" style="width: {{ $this->progressPercent }}%"></div>
            </div>
        </div>
    </div>

    {{-- Add/Edit Entry Form --}}
    @if ($showForm && ! $this->isReadOnly)
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6">
                <h4 class="text-sm font-semibold text-gray-900 mb-4">{{ $editingEntryId ? 'Edit Entry' : 'New Entry' }}</h4>

                <form wire:submit="{{ $editingEntryId ? 'updateEntry' : 'addEntry' }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                            <input type="date" wire:model="date" id="date" max="{{ date('Y-m-d') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary text-sm">
                            @error('date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="hours" class="block text-sm font-medium text-gray-700 mb-1">Hours *</label>
                            <input type="number" wire:model="hours" id="hours" step="0.25" min="0.25" placeholder="e.g. 2.5" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary text-sm">
                            @error('hours') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Location *</label>
                            <input type="text" wire:model="location" id="location" placeholder="Clinic name or address" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary text-sm">
                            @error('location') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="protocol" class="block text-sm font-medium text-gray-700 mb-1">Protocol *</label>
                            <input type="text" wire:model="protocol" id="protocol" placeholder="e.g. NADA 5-Point Protocol" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary text-sm">
                            @error('protocol') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea wire:model="notes" id="notes" rows="2" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary text-sm" placeholder="Optional notes about this session..."></textarea>
                        @error('notes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Attachments</label>
                        <input type="file" wire:model="attachments" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                        <p class="mt-1 text-xs text-gray-400">PDF, JPG, PNG, DOC, DOCX (max 10MB each)</p>
                        @error('attachments.*') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-brand-primary hover:opacity-90 transition">
                            {{ $editingEntryId ? 'Update Entry' : 'Save Entry' }}
                        </button>
                        <button type="button" wire:click="closeForm" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Entries Table --}}
    @if ($entries->count() > 0)
        {{-- Desktop Table --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Protocol</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hours</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Files</th>
                            @if (! $this->isReadOnly)
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($entries as $entry)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $entry->date->format('M j, Y') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">{{ $entry->location }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">{{ $entry->protocol }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ number_format($entry->hours, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @php $media = $entry->getMedia('entry_attachments'); @endphp
                                    @if ($media->count() > 0)
                                        <div class="space-y-1">
                                            @foreach ($media as $file)
                                                <div class="flex items-center gap-1">
                                                    <a href="{{ $file->getUrl() }}" target="_blank" class="text-brand-primary hover:underline text-xs truncate max-w-[120px]">{{ $file->file_name }}</a>
                                                    @if (! $this->isReadOnly)
                                                        <button wire:click="removeAttachment({{ $entry->id }}, {{ $file->id }})" wire:confirm="Remove this file?" class="text-red-400 hover:text-red-600 shrink-0">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                        </button>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-gray-400">--</span>
                                    @endif
                                </td>
                                @if (! $this->isReadOnly)
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                        <button wire:click="editEntry({{ $entry->id }})" class="text-brand-primary hover:underline mr-2">Edit</button>
                                        <button wire:click="deleteEntry({{ $entry->id }})" wire:confirm="Delete this entry and all its attachments?" class="text-red-600 hover:underline">Delete</button>
                                    </td>
                                @endif
                            </tr>
                            @if ($entry->notes)
                                <tr class="bg-gray-50/50">
                                    <td colspan="{{ $this->isReadOnly ? 5 : 6 }}" class="px-6 py-2 text-xs text-gray-500">
                                        <strong>Notes:</strong> {{ $entry->notes }}
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="3" class="px-6 py-3 text-sm font-semibold text-gray-900 text-right">Total</td>
                            <td class="px-6 py-3 text-sm font-bold text-gray-900">{{ number_format($this->totalHours, 2) }} hrs</td>
                            <td colspan="{{ $this->isReadOnly ? 1 : 2 }}"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Mobile Cards --}}
            <div class="md:hidden divide-y divide-gray-200">
                @foreach ($entries as $entry)
                    <div class="p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-900">{{ $entry->date->format('M j, Y') }}</span>
                            <span class="text-sm font-bold text-gray-900">{{ number_format($entry->hours, 2) }} hrs</span>
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

                        @if (! $this->isReadOnly)
                            <div class="mt-2 flex gap-3 pt-2 border-t border-gray-100">
                                <button wire:click="editEntry({{ $entry->id }})" class="text-xs text-brand-primary hover:underline">Edit</button>
                                <button wire:click="deleteEntry({{ $entry->id }})" wire:confirm="Delete this entry?" class="text-xs text-red-600 hover:underline">Delete</button>
                            </div>
                        @endif
                    </div>
                @endforeach

                <div class="p-4 bg-gray-50 flex justify-between">
                    <span class="text-sm font-semibold text-gray-900">Total</span>
                    <span class="text-sm font-bold text-gray-900">{{ number_format($this->totalHours, 2) }} hrs</span>
                </div>
            </div>
        </div>
    @else
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-center py-10">
                <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                <p class="mt-2 text-sm text-gray-500">No entries yet. Add your first clinical entry to start tracking hours.</p>
            </div>
        </div>
    @endif
</div>
