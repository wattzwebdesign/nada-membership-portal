<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? config('app.name', 'NADA') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
        <link rel="manifest" href="{{ asset('site.webmanifest') }}">
        <meta name="theme-color" content="#1C3519">
        <meta name="apple-mobile-web-app-title" content="NADA Portal">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body class="font-sans antialiased bg-gray-50">
        <nav x-data="{ open: false }" class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <a href="/">
                            <img src="{{ asset('NADAWebsiteLogo.svg') }}" alt="NADA" class="h-10" />
                        </a>
                    </div>

                    {{-- Desktop links --}}
                    <div class="hidden sm:flex sm:items-center sm:space-x-4">
                        <a href="https://acudetox.com" class="text-gray-600 hover:text-gray-900 text-sm font-medium">NADA Website</a>
                        <a href="{{ route('public.trainers.index') }}" class="text-gray-600 hover:text-gray-900 text-sm font-medium">Trainers</a>
                        <a href="{{ route('public.resources.index') }}" class="text-gray-600 hover:text-gray-900 text-sm font-medium">Resources</a>
                        <a href="{{ route('public.glossary.index') }}" class="text-gray-600 hover:text-gray-900 text-sm font-medium">Glossary</a>
                        <a href="{{ route('public.events.index') }}" class="text-gray-600 hover:text-gray-900 text-sm font-medium">Events</a>
                        <a href="{{ route('public.pricing') }}" class="text-gray-600 hover:text-gray-900 text-sm font-medium">Memberships</a>
                        <a href="{{ route('public.shop.index') }}" class="text-gray-600 hover:text-gray-900 text-sm font-medium">Shop</a>
                        <a href="{{ route('vendor-application.create') }}" class="text-gray-600 hover:text-gray-900 text-sm font-medium">Sell</a>
                        @livewire('cart-icon')
                        @auth
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-semibold bg-brand-primary">
                                                {{ strtoupper(substr(Auth::user()->first_name, 0, 1) . substr(Auth::user()->last_name, 0, 1)) }}
                                            </div>
                                            <span>{{ Auth::user()->full_name }}</span>
                                        </div>
                                        <svg class="ms-1 fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <x-dropdown-link :href="route('dashboard')">
                                        Dashboard
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('account.edit')">
                                        Account Settings
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('profile.edit')">
                                        Profile
                                    </x-dropdown-link>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                            Log Out
                                        </x-dropdown-link>
                                    </form>
                                </x-slot>
                            </x-dropdown>
                        @else
                            <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900 text-sm font-medium">Log In</a>
                            <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-brand-primary hover:bg-brand-primary-hover">Sign Up</a>
                        @endauth
                    </div>

                    {{-- Mobile hamburger button --}}
                    <div class="-me-2 flex items-center sm:hidden">
                        <button @click="open = !open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path :class="{'hidden': open, 'inline-flex': !open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Mobile menu --}}
            <div :class="{'block': open, 'hidden': !open}" class="hidden sm:hidden border-t border-gray-200">
                <div class="pt-2 pb-3 space-y-1">
                    <a href="https://acudetox.com" class="block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 transition duration-150 ease-in-out">NADA Website</a>
                    <a href="{{ route('public.trainers.index') }}" class="block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 transition duration-150 ease-in-out">Trainers</a>
                    <a href="{{ route('public.resources.index') }}" class="block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 transition duration-150 ease-in-out">Resources</a>
                    <a href="{{ route('public.glossary.index') }}" class="block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 transition duration-150 ease-in-out">Glossary</a>
                    <a href="{{ route('public.events.index') }}" class="block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 transition duration-150 ease-in-out">Events</a>
                    <a href="{{ route('public.pricing') }}" class="block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 transition duration-150 ease-in-out">Memberships</a>
                    <a href="{{ route('public.shop.index') }}" class="block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 transition duration-150 ease-in-out">Shop</a>
                    <a href="{{ route('vendor-application.create') }}" class="block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 transition duration-150 ease-in-out">Sell</a>
                </div>

                <div class="pt-4 pb-3 border-t border-gray-200">
                    @auth
                        <div class="px-4 mb-3">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-semibold bg-brand-primary">
                                    {{ strtoupper(substr(Auth::user()->first_name, 0, 1) . substr(Auth::user()->last_name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-medium text-base text-gray-800">{{ Auth::user()->full_name }}</div>
                                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <a href="{{ route('dashboard') }}" class="block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 transition duration-150 ease-in-out">Dashboard</a>
                            <a href="{{ route('account.edit') }}" class="block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 transition duration-150 ease-in-out">Account Settings</a>
                            <a href="{{ route('profile.edit') }}" class="block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 transition duration-150 ease-in-out">Profile</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 transition duration-150 ease-in-out">Log Out</button>
                            </form>
                        </div>
                    @else
                        <div class="space-y-1 px-4">
                            <a href="{{ route('login') }}" class="block w-full py-2 text-base font-medium text-gray-600 hover:text-gray-800 transition duration-150 ease-in-out">Log In</a>
                            <a href="{{ route('register') }}" class="block w-full py-2 text-base font-medium text-white text-center rounded-md bg-brand-primary hover:bg-brand-primary-hover">Sign Up</a>
                        </div>
                    @endauth
                </div>
            </div>
        </nav>
        <main>{{ $slot }}</main>
        <footer class="bg-white border-t border-gray-200 mt-16">
            <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 text-center text-gray-500 text-sm">
                &copy; {{ date('Y') }} National Acupuncture Detoxification Association. All rights reserved.
            </div>
        </footer>
        @stack('scripts')
        @include('partials.support-chat')
    </body>
</html>
