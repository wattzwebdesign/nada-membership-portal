<div>
    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-2xl font-bold mb-2" style="color: #374269;">Verify Certificate</h2>
                    <p class="text-gray-600 mb-6">Enter a certificate code to verify its authenticity and status.</p>

                    <form wire:submit="verify" class="mb-8">
                        <div class="flex gap-3">
                            <div class="flex-1">
                                <input type="text"
                                       wire:model="code"
                                       placeholder="Enter certificate code..."
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 text-sm"
                                       style="focus:border-color: #374269;">
                                @error('code')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 rounded-md text-white text-sm font-medium shadow-sm hover:opacity-90 transition-opacity"
                                    style="background-color: #374269;">
                                <svg wire:loading wire:target="verify" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Verify
                            </button>
                        </div>
                    </form>

                    @if ($searched)
                        @if ($certificate)
                            <div class="border rounded-lg p-6 {{ $certificate->isActive() ? 'border-green-300 bg-green-50' : 'border-red-300 bg-red-50' }}">
                                <div class="flex items-start">
                                    @if ($certificate->isActive())
                                        <svg class="h-6 w-6 text-green-600 mr-3 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.745 3.745 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                                        </svg>
                                    @else
                                        <svg class="h-6 w-6 text-red-600 mr-3 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                        </svg>
                                    @endif
                                    <div class="flex-1">
                                        <h3 class="text-lg font-semibold {{ $certificate->isActive() ? 'text-green-800' : 'text-red-800' }}">
                                            {{ $certificate->isActive() ? 'Valid Certificate' : 'Invalid / Expired Certificate' }}
                                        </h3>
                                        <dl class="mt-4 space-y-2">
                                            <div class="flex">
                                                <dt class="w-40 text-sm font-medium text-gray-600">Certificate Code:</dt>
                                                <dd class="text-sm text-gray-900">{{ $certificate->certificate_code }}</dd>
                                            </div>
                                            <div class="flex">
                                                <dt class="w-40 text-sm font-medium text-gray-600">Holder:</dt>
                                                <dd class="text-sm text-gray-900">{{ $certificate->user->full_name }}</dd>
                                            </div>
                                            <div class="flex">
                                                <dt class="w-40 text-sm font-medium text-gray-600">Date Issued:</dt>
                                                <dd class="text-sm text-gray-900">{{ $certificate->date_issued?->format('M d, Y') }}</dd>
                                            </div>
                                            <div class="flex">
                                                <dt class="w-40 text-sm font-medium text-gray-600">Expiration:</dt>
                                                <dd class="text-sm text-gray-900">{{ $certificate->expiration_date?->format('M d, Y') ?? 'No Expiration' }}</dd>
                                            </div>
                                            <div class="flex">
                                                <dt class="w-40 text-sm font-medium text-gray-600">Status:</dt>
                                                <dd>
                                                    @if ($certificate->isActive())
                                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-green-100 text-green-800">Active</span>
                                                    @else
                                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-red-100 text-red-800">{{ ucfirst($certificate->status) }}</span>
                                                    @endif
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="border border-yellow-300 bg-yellow-50 rounded-lg p-6">
                                <div class="flex items-center">
                                    <svg class="h-6 w-6 text-yellow-600 mr-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                    </svg>
                                    <div>
                                        <h3 class="text-lg font-semibold text-yellow-800">Certificate Not Found</h3>
                                        <p class="text-sm text-yellow-700 mt-1">No certificate was found with the code "{{ $code }}". Please check the code and try again.</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
