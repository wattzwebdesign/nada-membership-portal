<x-public-layout>
    <x-slot name="title">Resource Library - NADA</x-slot>

    {{-- Hero Banner --}}
    <div class="py-10 text-center text-white bg-brand-primary">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold">Resource Library</h1>
            <p class="mt-2 text-blue-100 text-lg">Articles, research, videos, and more from NADA</p>
        </div>
    </div>

    {{-- Category Grid --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($categories as $category)
                <a href="{{ route('public.resources.category', $category) }}"
                   class="block bg-white rounded-lg border border-gray-200 p-6 hover:shadow-md hover:border-gray-300 transition-all duration-150 text-center">
                    <img src="{{ asset('images/resource-icon.svg') }}" alt="" class="w-10 h-10 mx-auto">
                    <h3 class="text-base font-semibold text-gray-900 mt-3">{{ $category->name }}</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ $category->published_resources_count }} {{ Str::plural('article', $category->published_resources_count) }}
                    </p>
                </a>
            @endforeach
        </div>

        @if ($categories->isEmpty())
            <div class="text-center py-12 text-gray-500">
                <p>No resource categories available yet.</p>
            </div>
        @endif
    </div>
</x-public-layout>
