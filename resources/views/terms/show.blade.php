<x-public-layout :title="'Terms & Conditions — NADA'">
    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 sm:p-8">
                    @if ($agreement)
                        <div class="mb-6 border-b border-gray-200 pb-4">
                            <h1 class="text-2xl font-bold text-brand-primary">{{ $agreement->title }}</h1>
                            <p class="text-sm text-gray-500 mt-1">Version {{ $agreement->version }} — Last updated {{ $agreement->published_at?->format('F j, Y') ?? $agreement->updated_at->format('F j, Y') }}</p>
                        </div>

                        <div class="prose max-w-none text-gray-700">
                            {!! $agreement->content !!}
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">Terms & Conditions are not currently available.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-public-layout>
