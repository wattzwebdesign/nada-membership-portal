<x-public-layout>
    <x-slot name="title">Resource Library - NADA</x-slot>

    {{-- Hero Banner --}}
    <div class="py-10 text-center text-white" style="background-color: #374269;">
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
                   class="block bg-white rounded-lg border border-gray-200 p-6 hover:shadow-md hover:border-gray-300 transition-all duration-150">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: #374269;">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">{{ $category->name }}</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ $category->published_resources_count }} {{ Str::plural('article', $category->published_resources_count) }}
                            </p>
                        </div>
                    </div>
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
