<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Bookmarks</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            @if ($resources->isEmpty())
                <div class="text-center py-12">
                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">No bookmarks yet</h3>
                    <p class="text-sm text-gray-500 mb-6">You haven't bookmarked any resources yet.</p>
                    <a href="{{ route('public.resources.index') }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-brand-primary">
                        Browse Resource Library
                    </a>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($resources as $resource)
                        <div class="bg-white rounded-lg border border-gray-200 p-5 hover:shadow-md hover:border-gray-300 transition-all duration-150"
                             x-data="{ removed: false }" x-show="!removed" x-transition>
                            <div class="flex items-start justify-between">
                                <a href="{{ route('public.resources.show', [$resource->categories->first(), $resource]) }}" target="_blank" rel="noopener noreferrer" class="flex-1 min-w-0">
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
                                    <button
                                        @click="
                                            fetch('{{ route('bookmarks.toggle', $resource) }}', {
                                                method: 'POST',
                                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                                            }).then(() => { removed = true; })
                                        "
                                        class="p-1 rounded hover:bg-gray-100 transition-colors"
                                        title="Remove bookmark"
                                    >
                                        <svg class="w-5 h-5 text-brand-secondary" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M5 2h14a1 1 0 011 1v19.143a.5.5 0 01-.766.424L12 18.03l-7.234 4.536A.5.5 0 014 22.143V3a1 1 0 011-1z"/>
                                        </svg>
                                    </button>
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
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $resources->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
