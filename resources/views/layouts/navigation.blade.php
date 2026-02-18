{{-- Desktop sidebar --}}
<aside class="hidden lg:flex lg:flex-col lg:fixed lg:inset-y-0 lg:left-0 lg:w-64 lg:z-30 print:hidden" style="background-color: #f0e8d3;">
    @include('layouts.partials.sidebar-content')
</aside>

{{-- Mobile top bar + slide-out drawer --}}
<div class="lg:hidden print:hidden" x-data="{ sidebarOpen: false }">
    {{-- Top bar --}}
    <div class="fixed top-0 left-0 right-0 h-14 z-40 bg-white border-b border-gray-200 flex items-center px-4">
        <button @click="sidebarOpen = true" class="text-gray-500 hover:text-gray-700 focus:outline-none">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
        </button>
        <a href="{{ route('dashboard') }}" class="ml-3">
            <img src="{{ asset('NADAWebsiteLogo.svg') }}" alt="NADA" class="h-8" />
        </a>
    </div>

    {{-- Backdrop --}}
    <div
        x-show="sidebarOpen"
        x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="sidebarOpen = false"
        class="fixed inset-0 z-40 bg-black/50"
        x-cloak
    ></div>

    {{-- Slide-out drawer --}}
    <div
        x-show="sidebarOpen"
        x-transition:enter="transition ease-in-out duration-300 transform"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in-out duration-300 transform"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="fixed inset-y-0 left-0 z-50 w-64 flex flex-col"
        style="background-color: #f0e8d3;"
        x-cloak
    >
        {{-- Close button --}}
        <div class="absolute top-0 right-0 pt-4 pr-3">
            <button @click="sidebarOpen = false" class="focus:outline-none" style="color: #242424;">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        @include('layouts.partials.sidebar-content')
    </div>
</div>
