<x-public-layout>
    <x-slot name="title">Application Submitted - NADA</x-slot>

    <div class="min-h-[60vh] flex items-center justify-center px-4 sm:px-6 lg:px-8">
        <div class="max-w-lg w-full text-center">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 sm:p-12">
                <svg class="w-20 h-20 mx-auto text-green-500 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>

                <h1 class="text-2xl font-bold text-gray-900">Application Submitted</h1>
                <p class="mt-4 text-gray-600 leading-relaxed">
                    Thank you for your interest in selling on the NADA Marketplace. Your application has been submitted and is under review.
                </p>
                <p class="mt-3 text-gray-600 leading-relaxed">
                    Our team will review your application and notify you by email within a few business days. You'll receive instructions on how to set up your vendor profile and start listing products.
                </p>

                <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="{{ route('public.shop.index') }}" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-sm font-medium rounded-md text-white bg-brand-primary hover:bg-brand-primary-hover transition-colors">
                        Browse the Shop
                    </a>
                    <a href="/" class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                        Return Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-public-layout>
