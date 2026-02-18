<x-public-layout>
    <x-slot name="title">{{ $trainer->full_name }} - NADA Trainer</x-slot>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{ showContactModal: {{ $errors->any() || session('success') ? 'true' : 'false' }} }">
        {{-- Back Link --}}
        <a href="{{ route('public.trainers.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-6">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Trainer Directory
        </a>

        {{-- Success Flash (shown outside modal too) --}}
        @if (session('success'))
            <div class="rounded-md bg-green-50 border border-green-200 p-4 mb-6">
                <div class="flex">
                    <svg class="h-5 w-5 text-green-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <p class="ml-3 text-sm text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        {{-- Profile Card --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sm:p-8">
            <div class="flex flex-col sm:flex-row items-start gap-6">
                {{-- Photo / Initials --}}
                @if ($trainer->profile_photo_url)
                    <img src="{{ $trainer->profile_photo_url }}" alt="{{ $trainer->full_name }}" class="h-24 w-24 rounded-full object-cover flex-shrink-0">
                @else
                    <img src="{{ asset('images/nada-mark.png') }}" alt="NADA" class="h-24 w-24 rounded-full object-contain flex-shrink-0 bg-white border border-gray-200 p-1">
                @endif

                {{-- Name + Actions --}}
                <div class="flex-1 min-w-0">
                    <h1 class="text-2xl font-bold text-gray-900">{{ $trainer->full_name }}</h1>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium mt-1" style="background-color: #d39c27; color: white;">
                        NADA Registered Trainer
                    </span>

                    @if ($trainer->location_display)
                        <p class="flex items-center text-sm text-gray-500 mt-3">
                            <svg class="w-4 h-4 mr-1.5 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            {{ $trainer->location_display }}
                        </p>
                    @endif

                    <div class="flex items-center gap-2 mt-4">
                        {{-- Message Button --}}
                        <button @click="showContactModal = true" class="inline-flex items-center px-4 py-2 border-2 text-sm font-medium rounded-md transition-colors" style="border-color: #374269; color: #374269;" onmouseover="this.style.backgroundColor='#374269'; this.style.color='white'" onmouseout="this.style.backgroundColor='transparent'; this.style.color='#374269'">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            Message
                        </button>

                        {{-- Share Button --}}
                        <button onclick="navigator.clipboard.writeText(window.location.href); this.querySelector('.share-icon').classList.add('hidden'); this.querySelector('.check-icon').classList.remove('hidden'); setTimeout(() => { this.querySelector('.share-icon').classList.remove('hidden'); this.querySelector('.check-icon').classList.add('hidden'); }, 2000)" class="inline-flex items-center justify-center w-10 h-10 border-2 border-gray-300 rounded-md text-gray-500 hover:border-gray-400 hover:text-gray-700 transition-colors" title="Copy profile link">
                            <svg class="w-4 h-4 share-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                            <svg class="w-4 h-4 check-icon hidden text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Contact Info Box --}}
                @if ($trainer->email || $trainer->phone)
                    <div class="flex-shrink-0 border border-gray-200 rounded-lg p-4 sm:p-5">
                        <div class="flex items-start gap-6">
                            @if ($trainer->email)
                                <div class="text-center">
                                    <div class="flex justify-center mb-1.5">
                                        <svg class="w-5 h-5" style="color: #374269;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                                    </div>
                                    <a href="mailto:{{ $trainer->email }}" class="text-sm hover:underline" style="color: #374269;">{{ $trainer->email }}</a>
                                </div>
                            @endif
                            @if ($trainer->phone)
                                <div class="text-center">
                                    <div class="flex justify-center mb-1.5">
                                        <svg class="w-5 h-5" style="color: #374269;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    </div>
                                    <a href="tel:{{ $trainer->phone }}" class="text-sm hover:underline" style="color: #374269;">{{ $trainer->phone_formatted }}</a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Bio Section --}}
        @if ($trainer->bio)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sm:p-8 mt-6">
                <h2 class="text-lg font-semibold mb-3" style="color: #374269;">About</h2>
                <div class="text-sm text-gray-700 leading-relaxed">
                    {!! nl2br(e($trainer->bio)) !!}
                </div>
            </div>
        @endif

        {{-- Trainings I am Hosting --}}
        @if ($upcomingTrainings->isNotEmpty())
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sm:p-8 mt-6">
                <h2 class="text-lg font-semibold mb-4" style="color: #374269;">Trainings I am Hosting</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach ($upcomingTrainings as $training)
                        <a href="{{ route('trainings.show', $training) }}" class="block border border-gray-200 rounded-lg p-4 hover:border-gray-300 hover:shadow-sm transition-all">
                            <h3 class="text-sm font-semibold text-gray-900">{{ $training->title }}</h3>
                            <p class="flex items-center text-sm text-gray-500 mt-2">
                                <svg class="w-4 h-4 mr-1.5 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                {{ $training->start_date->format('F j, Y') }}
                                @if ($training->end_date && !$training->start_date->isSameDay($training->end_date))
                                    &ndash; {{ $training->end_date->format('F j, Y') }}
                                @endif
                            </p>
                            @if ($training->location_name)
                                <p class="flex items-center text-sm text-gray-500 mt-1">
                                    <svg class="w-4 h-4 mr-1.5 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    {{ $training->location_name }}
                                </p>
                            @endif
                            <p class="flex items-center text-sm text-gray-500 mt-1">
                                <svg class="w-4 h-4 mr-1.5 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Hosted by: {{ $trainer->full_name }}
                            </p>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Contact Modal --}}
        <div x-show="showContactModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                {{-- Backdrop --}}
                <div x-show="showContactModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="showContactModal = false" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                {{-- Modal Panel --}}
                <div x-show="showContactModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-6 pt-5 pb-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold" style="color: #374269;" id="modal-title">Message {{ $trainer->first_name }}</h3>
                            <button @click="showContactModal = false" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        @if (session('success'))
                            <div class="rounded-md bg-green-50 border border-green-200 p-4 mb-4">
                                <p class="text-sm text-green-700">{{ session('success') }}</p>
                            </div>
                        @else
                            <form method="POST" action="{{ route('public.trainers.contact', $trainer) }}">
                                @csrf
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-gray-700">Name <span class="text-red-500">*</span></label>
                                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label for="email" class="block text-sm font-medium text-gray-700">Email <span class="text-red-500">*</span></label>
                                        <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                        @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone <span class="text-sm text-gray-400 font-normal">(optional)</span></label>
                                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div class="mt-4">
                                    <label for="message" class="block text-sm font-medium text-gray-700">Message <span class="text-red-500">*</span></label>
                                    <textarea name="message" id="message" rows="4" required
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('message') }}</textarea>
                                    @error('message') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div class="mt-5 flex justify-end gap-3">
                                    <button type="button" @click="showContactModal = false" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                        Cancel
                                    </button>
                                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white" style="background-color: #374269;">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        Send Message
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-public-layout>
