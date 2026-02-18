<div>
    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-2xl font-bold mb-2 text-brand-primary">Request a Discount</h2>
                    <p class="text-gray-600 mb-6">If you qualify for a student or senior discount, please submit proof documentation below.</p>

                    @if (session('success'))
                        <div class="mb-6 rounded-md bg-green-50 p-4">
                            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                        </div>
                    @endif

                    <form wire:submit="submit" class="space-y-6">
                        {{-- Discount Type --}}
                        <fieldset>
                            <legend class="block text-sm font-medium text-gray-700 mb-3">Discount Type</legend>
                            <div class="space-y-3">
                                <label class="flex items-center p-4 border rounded-lg cursor-pointer transition-colors {{ $discount_type === 'student' ? 'border-2 border-brand-primary' : 'border-gray-200 hover:bg-gray-50' }}"
                                       style="{{ $discount_type === 'student' ? 'background-color: #f8f9fc;' : '' }}">
                                    <input type="radio" wire:model.live="discount_type" value="student"
                                           class="h-4 w-4 text-brand-primary">
                                    <div class="ml-3">
                                        <span class="block text-sm font-medium text-gray-900">Student Discount</span>
                                        <span class="block text-sm text-gray-500">For currently enrolled students with valid student ID.</span>
                                    </div>
                                </label>
                                <label class="flex items-center p-4 border rounded-lg cursor-pointer transition-colors {{ $discount_type === 'senior' ? 'border-2 border-brand-primary' : 'border-gray-200 hover:bg-gray-50' }}"
                                       style="{{ $discount_type === 'senior' ? 'background-color: #f8f9fc;' : '' }}">
                                    <input type="radio" wire:model.live="discount_type" value="senior"
                                           class="h-4 w-4 text-brand-primary">
                                    <div class="ml-3">
                                        <span class="block text-sm font-medium text-gray-900">Senior Discount</span>
                                        <span class="block text-sm text-gray-500">For individuals aged 65 and older.</span>
                                    </div>
                                </label>
                            </div>
                            @error('discount_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </fieldset>

                        {{-- Proof Description --}}
                        <div>
                            <label for="proof_description" class="block text-sm font-medium text-gray-700">Description of Proof</label>
                            <textarea id="proof_description" wire:model="proof_description" rows="3"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm"
                                      placeholder="Describe your uploaded proof documents (e.g., student ID, enrollment letter, government-issued ID)..."></textarea>
                            @error('proof_description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Proof Documents Upload --}}
                        <div>
                            <label for="proof_documents" class="block text-sm font-medium text-gray-700">Proof Documents</label>
                            <p class="mt-1 text-xs text-gray-500">Upload supporting documents (max 10MB each).</p>
                            <input type="file" id="proof_documents" wire:model="proof_documents" multiple
                                   class="mt-2 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:text-white file:cursor-pointer file:bg-brand-primary">

                            <div wire:loading wire:target="proof_documents" class="mt-2 text-sm text-gray-500">
                                Uploading files...
                            </div>

                            @error('proof_documents')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            @error('proof_documents.*')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror

                            @if ($proof_documents)
                                <ul class="mt-2 space-y-1">
                                    @foreach ($proof_documents as $file)
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

                        {{-- Submit --}}
                        <div class="flex justify-end">
                            <button type="submit"
                                    data-guide="discount-submit"
                                    class="inline-flex items-center px-6 py-2.5 rounded-md text-white text-sm font-medium shadow-sm hover:opacity-90 transition-opacity bg-brand-primary"
                                    wire:loading.attr="disabled">
                                <svg wire:loading wire:target="submit" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Submit Discount Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
