<x-public-layout>
    <x-slot name="title">{{ $category->name }} - Resource Library - NADA</x-slot>

    {{-- Hero Banner --}}
    <div class="py-10 text-center text-white bg-brand-primary">
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
                <div class="bg-white rounded-lg border border-gray-200 p-5 hover:shadow-md hover:border-gray-300 transition-all duration-150">
                    <div class="flex items-start justify-between">
                        <a href="{{ route('public.resources.show', [$category, $resource]) }}" class="flex-1 min-w-0">
                            <h3 class="text-base font-semibold text-gray-900">{{ $resource->title }}</h3>
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                @foreach ($resource->categories as $cat)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium text-white bg-brand-secondary">
                                        {{ $cat->name }}
                                    </span>
                                @endforeach
                            </div>
                        </a>
                        <div class="ml-3 flex-shrink-0 flex items-center gap-2">
                            @auth
                                <div x-data="{ bookmarked: {{ in_array($resource->id, $bookmarkedIds) ? 'true' : 'false' }}, loading: false }">
                                    <button
                                        @click.prevent="
                                            if (loading) return;
                                            loading = true;
                                            fetch('{{ route('bookmarks.toggle', $resource) }}', {
                                                method: 'POST',
                                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                                            })
                                            .then(r => r.json())
                                            .then(data => { bookmarked = data.bookmarked; })
                                            .finally(() => { loading = false; })
                                        "
                                        class="p-1 rounded hover:bg-gray-100 transition-colors"
                                        :title="bookmarked ? 'Remove bookmark' : 'Bookmark this resource'"
                                    >
                                        <svg x-show="bookmarked" class="w-5 h-5 text-brand-secondary" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M5 2h14a1 1 0 011 1v19.143a.5.5 0 01-.766.424L12 18.03l-7.234 4.536A.5.5 0 014 22.143V3a1 1 0 011-1z"/>
                                        </svg>
                                        <svg x-show="!bookmarked" class="w-5 h-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                        </svg>
                                    </button>
                                </div>
                            @endauth
                            @if ($resource->is_members_only)
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    Members Only
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
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
