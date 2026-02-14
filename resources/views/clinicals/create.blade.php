<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Submit Clinicals') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-2" style="color: #374269;">Clinical Submission Form</h3>
                    <p class="text-sm text-gray-500 mb-6">Submit your clinical treatment logs for review. All fields marked with * are required.</p>

                    <form method="POST" action="{{ route('clinicals.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="space-y-6">
                            {{-- Name Row --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="first_name" class="block text-sm font-medium text-gray-700">First Name *</label>
                                    <input type="text" name="first_name" id="first_name" value="{{ old('first_name', auth()->user()->first_name) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" style="focus:border-color: #374269;">
                                    @error('first_name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name *</label>
                                    <input type="text" name="last_name" id="last_name" value="{{ old('last_name', auth()->user()->last_name) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                                    @error('last_name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Email --}}
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email Address *</label>
                                <input type="email" name="email" id="email" value="{{ old('email', auth()->user()->email) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Estimated Training Date --}}
                            <div>
                                <label for="estimated_training_date" class="block text-sm font-medium text-gray-700">Estimated Training Date *</label>
                                <input type="date" name="estimated_training_date" id="estimated_training_date" value="{{ old('estimated_training_date') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                                @error('estimated_training_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Trainer Dropdown --}}
                            <div>
                                <label for="trainer_id" class="block text-sm font-medium text-gray-700">Select Trainer *</label>
                                <select name="trainer_id" id="trainer_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                                    <option value="">-- Select a Trainer --</option>
                                    @foreach ($trainers as $trainer)
                                        <option value="{{ $trainer->id }}" {{ old('trainer_id') == $trainer->id ? 'selected' : '' }}>
                                            {{ $trainer->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('trainer_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- File Upload --}}
                            <div x-data="{
                                dragging: false,
                                files: [],
                                handleFiles(fileList) {
                                    const input = $refs.fileInput;
                                    const dt = new DataTransfer();
                                    // Keep existing files
                                    for (const f of this.files) { dt.items.add(f); }
                                    // Add new files
                                    for (const f of fileList) { dt.items.add(f); }
                                    input.files = dt.files;
                                    this.files = Array.from(dt.files);
                                },
                                removeFile(index) {
                                    this.files.splice(index, 1);
                                    const dt = new DataTransfer();
                                    for (const f of this.files) { dt.items.add(f); }
                                    $refs.fileInput.files = dt.files;
                                },
                                formatSize(bytes) {
                                    if (bytes < 1024) return bytes + ' B';
                                    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
                                    return (bytes / 1048576).toFixed(1) + ' MB';
                                }
                            }">
                                <label class="block text-sm font-medium text-gray-700">Treatment Log Files *</label>
                                <p class="text-xs text-gray-500 mb-2">Upload your treatment logs. Accepted formats: PDF, JPG, PNG, DOC, DOCX. Max 10MB per file.</p>

                                <div
                                    x-on:click="$refs.fileInput.click()"
                                    x-on:dragover.prevent="dragging = true"
                                    x-on:dragleave.prevent="dragging = false"
                                    x-on:drop.prevent="dragging = false; handleFiles($event.dataTransfer.files)"
                                    :class="dragging ? 'border-[#374269] bg-blue-50' : 'border-gray-300'"
                                    class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed rounded-md cursor-pointer hover:border-gray-400 transition-colors"
                                >
                                    <div class="space-y-1 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-sm text-gray-600">
                                            <span class="font-medium hover:underline" style="color: #374269;">Upload files</span>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500">PDF, JPG, PNG, DOC, DOCX up to 10MB each</p>
                                    </div>
                                </div>

                                <input
                                    x-ref="fileInput"
                                    name="treatment_logs[]"
                                    type="file"
                                    class="hidden"
                                    multiple
                                    accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                    x-on:change="handleFiles($event.target.files)"
                                >

                                {{-- Selected files list --}}
                                <template x-if="files.length > 0">
                                    <ul class="mt-3 space-y-2">
                                        <template x-for="(file, index) in files" :key="index">
                                            <li class="flex items-center justify-between text-sm bg-gray-50 rounded-md px-3 py-2">
                                                <div class="flex items-center min-w-0">
                                                    <svg class="w-4 h-4 mr-2 text-green-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                    </svg>
                                                    <span class="text-gray-700 truncate" x-text="file.name"></span>
                                                    <span class="ml-2 text-gray-400 text-xs shrink-0" x-text="formatSize(file.size)"></span>
                                                </div>
                                                <button type="button" x-on:click.stop="removeFile(index)" class="ml-3 text-gray-400 hover:text-red-500 shrink-0">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </li>
                                        </template>
                                    </ul>
                                </template>

                                @error('treatment_logs')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                @error('treatment_logs.*')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Notes --}}
                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700">Additional Notes</label>
                                <textarea name="notes" id="notes" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" placeholder="Any additional information you'd like to include...">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Submit --}}
                        <div class="mt-6 flex items-center justify-between">
                            <a href="{{ route('clinicals.index') }}" class="text-sm text-gray-500 hover:text-gray-700">View Submission History</a>
                            <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-md text-white" style="background-color: #374269;">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                Submit Clinicals
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
