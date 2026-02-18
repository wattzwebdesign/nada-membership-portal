<x-public-layout>
    <x-slot name="title">{{ $category->name }} - Resource Library - NADA</x-slot>

    {{-- Hero Banner --}}
    <div class="py-10 text-center text-white" style="background-color: #374269;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold">{{ $category->name }}</h1>
            <p class="mt-2 text-blue-100 text-lg">{{ $resources->total() }} {{ Str::plural('resource', $resources->total()) }}</p>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Back Link --}}
        <a href="{{ route('public.resources.index') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 mb-6">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            All Categories
        </a>

        {{-- Resource List --}}
        <div class="space-y-4">
            @forelse ($resources as $resource)
                <a href="{{ route('public.resources.show', [$category, $resource]) }}"
                   class="block bg-white rounded-lg border border-gray-200 p-5 hover:shadow-md hover:border-gray-300 transition-all duration-150">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <h3 class="text-base font-semibold text-gray-900">{{ $resource->title }}</h3>
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                @foreach ($resource->categories as $cat)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium text-white" style="background-color: #d39c27;">
                                        {{ $cat->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        @if ($resource->is_members_only)
                            <span class="ml-3 flex-shrink-0 inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                Members Only
                            </span>
                        @endif
                    </div>
                </a>
            @empty
                <div class="text-center py-12 text-gray-500">
                    <p>No resources in this category yet.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $resources->links() }}
        </div>
    </div>
</x-public-layout>
