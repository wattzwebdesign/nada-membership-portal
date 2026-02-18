<x-public-layout>
    <div class="py-16">
        <div class="max-w-lg mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-lg rounded-xl overflow-hidden">
                <div class="px-6 py-4 bg-brand-primary">
                    <div class="flex items-center justify-center text-white">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-lg font-semibold">Discount Approved</span>
                    </div>
                </div>
                <div class="p-8 text-center">
                    <p class="text-gray-700 text-lg">The <strong>{{ ucfirst($discountRequest->discount_type) }}</strong> discount for <strong>{{ $discountRequest->user->full_name }}</strong> has been approved.</p>
                    <p class="mt-4 text-sm text-gray-500">The member can now access discounted plan pricing.</p>
                </div>
            </div>
        </div>
    </div>
</x-public-layout>
