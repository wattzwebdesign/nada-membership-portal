<x-public-layout>
    <div class="py-16">
        <div class="max-w-lg mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-lg rounded-xl overflow-hidden">
                <div class="px-6 py-4 bg-yellow-500">
                    <div class="flex items-center justify-center text-white">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                        <span class="text-lg font-semibold">Invalid Link</span>
                    </div>
                </div>
                <div class="p-8 text-center">
                    <p class="text-gray-700 text-lg">{{ $reason }}</p>
                    <p class="mt-4 text-sm text-gray-500">If you believe this is an error, please check the admin panel for the current status of this request.</p>
                </div>
            </div>
        </div>
    </div>
</x-public-layout>
