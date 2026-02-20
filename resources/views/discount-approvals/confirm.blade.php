<x-public-layout>
    <div class="py-16">
        <div class="max-w-lg mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-lg rounded-xl overflow-hidden">
                <div class="px-6 py-4 {{ $action === 'approve' ? 'bg-brand-primary' : 'bg-red-600' }}">
                    <div class="flex items-center justify-center text-white">
                        @if ($action === 'approve')
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="text-lg font-semibold">Confirm Approval</span>
                        @else
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="text-lg font-semibold">Confirm Denial</span>
                        @endif
                    </div>
                </div>
                <div class="p-8">
                    <div class="text-center mb-6">
                        <p class="text-gray-700 text-lg">
                            Are you sure you want to <strong>{{ $action }}</strong> the
                            <strong>{{ ucfirst($discountRequest->discount_type) }}</strong> discount request
                            from <strong>{{ $discountRequest->user->full_name }}</strong>?
                        </p>
                    </div>

                    <form method="POST" action="{{ $action === 'approve' ? route('discount.approve.confirm', $token) : route('discount.deny.confirm', $token) }}">
                        @csrf
                        <button type="submit"
                                class="w-full inline-flex justify-center items-center px-4 py-3 border border-transparent text-sm font-semibold rounded-md text-white transition {{ $action === 'approve' ? 'bg-brand-primary hover:bg-brand-accent' : 'bg-red-600 hover:bg-red-700' }}">
                            {{ $action === 'approve' ? 'Yes, Approve Discount' : 'Yes, Deny Discount' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-public-layout>
