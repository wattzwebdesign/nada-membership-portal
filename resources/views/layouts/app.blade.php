<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
        <link rel="manifest" href="{{ asset('site.webmanifest') }}">
        <meta name="theme-color" content="#1C3519">
        <meta name="apple-mobile-web-app-title" content="NADA Portal">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Image Optimization Config -->
        <script>
            window.imageOptimization = {
                enabled: @json(\App\Models\SiteSetting::imageOptimizationEnabled()),
                maxWidth: @json(\App\Models\SiteSetting::imageMaxWidth()),
                maxHeight: @json(\App\Models\SiteSetting::imageMaxHeight()),
            };
        </script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Stripe.js for payment method updates -->
        <script src="https://js.stripe.com/v3/" defer></script>

        <!-- Flatpickr Date Picker -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/airbnb.css">
        <script src="https://cdn.jsdelivr.net/npm/flatpickr" defer></script>

        @stack('styles')

        @include('partials.umami-tracking')
    </head>
    <body class="font-sans antialiased">
        @include('partials.impersonation-banner')
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <div class="lg:pl-64 print:pl-0">
                {{-- Mobile top bar spacer --}}
                <div class="h-14 lg:hidden print:hidden"></div>

                <!-- Page Heading -->
                @isset($header)
                    <header class="bg-white shadow">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <!-- Page Content -->
                <main>
                    {{ $slot }}
                </main>
            </div>
        </div>

        @stack('scripts')

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('[data-datepicker]').forEach(function(el) {
                    flatpickr(el, JSON.parse(el.dataset.datepicker || '{}'));
                });
            });
        </script>
        @include('partials.support-chat')
        @include('partials.umami-events')
    </body>
</html>
