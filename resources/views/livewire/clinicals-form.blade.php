<div>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-2xl font-bold mb-6" style="color: #374269;">Submit Clinical Documentation</h2>

                    @if (session('success'))
                        <div class="mb-6 rounded-md bg-green-50 p-4">
                            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                        </div>
                    @endif

                    <form wire:submit="submit" class="space-y-6">
                        {{-- Name Fields --}}
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                                <input type="text" id="first_name" wire:model="first_name"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                                @error('first_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                                <input type="text" id="last_name" wire:model="last_name"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                                @error('last_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Email --}}
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" id="email" wire:model="email"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Estimated Training Date --}}
                        <div>
                            <label for="estimated_training_date" class="block text-sm font-medium text-gray-700">Estimated Training Date</label>
                            <input type="text" id="estimated_training_date" wire:model="estimated_training_date"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm"
                                   x-data x-init="flatpickr($el, {altInput:true,altFormat:'M j, Y',dateFormat:'Y-m-d',minDate:'today',onChange:(d,s)=>{$wire.set('estimated_training_date',s)}})">
                            @error('estimated_training_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Trainer Selection --}}
                        <div>
                            <label for="trainer_id" class="block text-sm font-medium text-gray-700">Trainer (Optional)</label>
                            <select id="trainer_id" wire:model="trainer_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                                <option value="">-- Select a Trainer --</option>
                                @foreach ($trainers as $trainer)
                                    <option value="{{ $trainer->id }}">{{ $trainer->last_name }}, {{ $trainer->first_name }}</option>
                                @endforeach
                            </select>
                            @error('trainer_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Treatment Logs Upload --}}
                        <div>
                            <label for="treatment_logs" class="block text-sm font-medium text-gray-700">Treatment Logs</label>
                            <p class="mt-1 text-xs text-gray-500">Upload PDF, JPG, PNG, DOC, or DOCX files (max 10MB each).</p>
                            <input type="file" id="treatment_logs" wire:model="treatment_logs" multiple
                                   class="mt-2 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:text-white file:cursor-pointer"
                                   style="file:background-color: #374269;"
                                   accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">

                            <div wire:loading wire:target="treatment_logs" class="mt-2 text-sm text-gray-500">
                                Uploading files...
                            </div>

                            @error('treatment_logs')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            @error('treatment_logs.*')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror

                            @if ($treatment_logs)
                                <ul class="mt-2 space-y-1">
                                    @foreach ($treatment_logs as $file)
                                        <li class="text-sm text-gray-600 flex items-center">
                                            <svg class="w-4 h-4 mr-1 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                            </svg>
                                            {{ $file->getClientOriginalName() }}
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>

                        {{-- Notes --}}
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">Notes (Optional)</label>
                            <textarea id="notes" wire:model="notes" rows="4"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm"
                                      placeholder="Any additional notes about your clinical submission..."></textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Submit Button --}}
                        <div class="flex justify-end">
                            <button type="submit"
                                    class="inline-flex items-center px-6 py-2.5 rounded-md text-white text-sm font-medium shadow-sm hover:opacity-90 transition-opacity"
                                    style="background-color: #374269;"
                                    wire:loading.attr="disabled">
                                <svg wire:loading wire:target="submit" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Submit Clinical Documentation
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
