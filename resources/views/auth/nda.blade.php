<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $agreement->title }} - {{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div>
                <a href="/">
                    <img src="{{ asset('NADAWebsiteLogo.svg') }}" alt="NADA" class="h-16 mx-auto" />
                </a>
            </div>

            <div class="w-full sm:max-w-2xl mt-6 px-6 py-6 bg-white shadow-md overflow-hidden sm:rounded-lg">
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-center" style="color: #374269;">
                        {{ $agreement->title }}
                    </h2>
                </div>

                <div id="nda-content" class="prose prose-sm max-w-none mb-6 max-h-[28rem] overflow-y-auto border border-gray-200 rounded-md p-4 bg-gray-50">
                    {!! $agreement->content !!}
                </div>

                <p id="scroll-hint" class="text-xs text-amber-600 mb-4 text-center">
                    Please scroll to the bottom of the agreement to continue.
                </p>

                <form method="POST" action="{{ route('nda.accept') }}">
                    @csrf

                    <div class="mb-4">
                        <label class="flex items-start gap-2">
                            <input type="checkbox" id="nda-agree" name="agree" value="1" disabled class="rounded border-gray-300 shadow-sm focus:ring-indigo-500 mt-0.5 disabled:opacity-50 disabled:cursor-not-allowed" style="color: #374269;">
                            <span id="agree-label" class="text-sm text-gray-400">I have read and agree to the above agreement</span>
                        </label>
                        @error('agree')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between">
                        <button type="submit" id="nda-submit" disabled class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed" style="background-color: #374269;">
                            Accept & Continue
                        </button>
                    </div>
                </form>

                <div class="mt-4 text-center">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Log Out
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const content = document.getElementById('nda-content');
                const checkbox = document.getElementById('nda-agree');
                const submit = document.getElementById('nda-submit');
                const hint = document.getElementById('scroll-hint');
                const label = document.getElementById('agree-label');

                function checkScrolledToBottom() {
                    // Allow a small threshold for rounding
                    return content.scrollHeight - content.scrollTop - content.clientHeight < 10;
                }

                function enableForm() {
                    checkbox.disabled = false;
                    checkbox.classList.remove('disabled:opacity-50', 'disabled:cursor-not-allowed');
                    label.classList.remove('text-gray-400');
                    label.classList.add('text-gray-700');
                    hint.style.display = 'none';
                }

                // If content doesn't overflow (short agreement), enable immediately
                if (content.scrollHeight <= content.clientHeight) {
                    enableForm();
                }

                content.addEventListener('scroll', function () {
                    if (checkScrolledToBottom()) {
                        enableForm();
                    }
                });

                checkbox.addEventListener('change', function () {
                    submit.disabled = !this.checked;
                });
            });
        </script>
    </body>
</html>
