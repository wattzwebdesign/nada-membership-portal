<x-public-layout>
    <x-slot name="title">{{ $resource->title }} - NADA</x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Back Link --}}
        <a href="{{ route('public.resources.category', $category) }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 mb-6">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            {{ $category->name }}
        </a>

        {{-- Title & Category Pills --}}
        <h1 class="text-2xl font-bold text-gray-900">{{ $resource->title }}</h1>
        <div class="mt-3 flex flex-wrap gap-1.5">
            @foreach ($resource->categories as $cat)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium text-white" style="background-color: #d39c27;">
                    {{ $cat->name }}
                </span>
            @endforeach
        </div>

        @if ($canViewFull)
            {{-- Full Content --}}
            <div class="mt-8">
                @if ($resource->body)
                    <div class="max-w-none text-gray-700 leading-relaxed [&>p]:mb-4 [&>h2]:text-xl [&>h2]:font-semibold [&>h2]:text-gray-900 [&>h2]:mt-6 [&>h2]:mb-3 [&>h3]:text-lg [&>h3]:font-semibold [&>h3]:text-gray-900 [&>h3]:mt-5 [&>h3]:mb-2 [&>ul]:list-disc [&>ul]:pl-6 [&>ul]:mb-4 [&>ol]:list-decimal [&>ol]:pl-6 [&>ol]:mb-4 [&>a]:text-blue-600 [&>a]:underline [&>blockquote]:border-l-4 [&>blockquote]:border-gray-300 [&>blockquote]:pl-4 [&>blockquote]:italic [&>blockquote]:my-4">
                        {!! $resource->body !!}
                    </div>
                @endif

                @if ($resource->video_embed)
                    <div class="mt-8">
                        <h2 class="text-lg font-semibold text-gray-900 mb-3">Video</h2>
                        <div class="aspect-video rounded-lg overflow-hidden bg-gray-100">
                            <iframe src="https://www.youtube.com/embed/{{ $resource->video_embed }}" class="w-full h-full" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                        </div>
                    </div>
                @endif

                @if ($resource->external_link)
                    <div class="mt-8">
                        <a href="{{ $resource->external_link }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white" style="background-color: #374269;">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            View External Resource
                        </a>
                    </div>
                @endif

                @php
                    $attachments = $resource->getMedia('attachments');
                @endphp
                @if ($attachments->isNotEmpty())
                    <div class="mt-8">
                        <h2 class="text-lg font-semibold text-gray-900 mb-3">Files</h2>
                        <div class="space-y-2">
                            @foreach ($attachments as $media)
                                <a href="{{ $media->getUrl() }}" target="_blank" rel="noopener noreferrer"
                                   class="flex items-center p-3 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 transition-colors">
                                    <svg class="w-5 h-5 text-gray-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $media->file_name }}</p>
                                        <p class="text-xs text-gray-500">{{ strtoupper($media->extension) }} &middot; {{ number_format($media->size / 1024, 0) }} KB</p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @else
            {{-- Members-Only Gate --}}
            @if ($resource->excerpt)
                <div class="mt-6 text-gray-600">
                    <p>{{ $resource->excerpt }}</p>
                </div>
            @endif

            <div class="mt-8 rounded-lg border-2 p-8 text-center" style="border-color: #d39c27; background-color: rgba(211, 156, 39, 0.05);">
                <svg class="w-12 h-12 mx-auto mb-4" style="color: #d39c27;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">This resource is for NADA members only</h3>
                <p class="text-sm text-gray-600 mb-6">Log in with your member account or sign up for a membership to access this resource.</p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                    <a href="{{ route('login') }}" class="inline-flex items-center px-5 py-2.5 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Log In
                    </a>
                    <a href="{{ route('register') }}" class="inline-flex items-center px-5 py-2.5 border border-transparent text-sm font-medium rounded-md text-white" style="background-color: #374269;">
                        Sign Up
                    </a>
                    <a href="{{ route('public.pricing') }}" class="inline-flex items-center px-5 py-2.5 border border-transparent text-sm font-medium rounded-md text-white" style="background-color: #d39c27;">
                        View Plans
                    </a>
                </div>
            </div>
        @endif
    </div>
</x-public-layout>
