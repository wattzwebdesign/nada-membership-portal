<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Upgrade to Registered Trainer') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Already a trainer --}}
            @if (auth()->user()->isTrainer())
                <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
                    <svg class="mx-auto h-12 w-12 text-green-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <h3 class="text-lg font-semibold text-green-800">You Are a Registered Trainer</h3>
                    <p class="text-sm text-green-600 mt-1">Your trainer account is active. Access the Trainer Portal to manage trainings and payouts.</p>
                    <a href="{{ route('trainer.dashboard') }}" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white" style="background-color: #374269;">
                        Go to Trainer Portal
                    </a>
                </div>

            {{-- Pending application --}}
            @elseif (auth()->user()->trainer_application_status === 'pending')
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                    <svg class="mx-auto h-12 w-12 text-yellow-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <h3 class="text-lg font-semibold text-yellow-800">Application Under Review</h3>
                    <p class="text-sm text-yellow-600 mt-1">Your trainer application has been submitted and is being reviewed by the NADA team. You will be notified by email once a decision is made.</p>
                    <a href="{{ route('dashboard') }}" class="mt-4 inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-50">
                        Back to Dashboard
                    </a>
                </div>

            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        {{-- Intro --}}
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold" style="color: #374269;">Trainer Application</h3>
                            <p class="text-sm text-gray-500 mt-2">Registered Trainers can host NADA trainings, manage attendees, issue certificates, and earn payouts through Stripe Connect. Complete the application below and an administrator will review your qualifications.</p>
                        </div>

                        {{-- Benefits --}}
                        <div class="bg-gray-50 rounded-lg p-4 mb-6">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3">Registered Trainer Benefits</h4>
                            <ul class="space-y-2">
                                <li class="flex items-start text-sm text-gray-600">
                                    <svg class="w-4 h-4 mr-2 mt-0.5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Create and manage your own trainings (in-person, virtual, hybrid)
                                </li>
                                <li class="flex items-start text-sm text-gray-600">
                                    <svg class="w-4 h-4 mr-2 mt-0.5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Mark attendees as complete and trigger certificate generation
                                </li>
                                <li class="flex items-start text-sm text-gray-600">
                                    <svg class="w-4 h-4 mr-2 mt-0.5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Host paid trainings and receive payouts via Stripe Connect
                                </li>
                                <li class="flex items-start text-sm text-gray-600">
                                    <svg class="w-4 h-4 mr-2 mt-0.5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Access detailed earnings reports and per-training breakdowns
                                </li>
                            </ul>
                        </div>

                        <form method="POST" action="{{ route('trainer-application.store') }}">
                            @csrf

                            <div class="space-y-6">
                                {{-- Credentials --}}
                                <div>
                                    <label for="credentials" class="block text-sm font-medium text-gray-700">Professional Credentials *</label>
                                    <p class="text-xs text-gray-500 mb-2">Describe your professional credentials, certifications, and relevant qualifications.</p>
                                    <textarea name="credentials" id="credentials" rows="4" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" placeholder="e.g., Licensed Acupuncturist (LAc), NADA certified since 2015, trained under Dr. Smith...">{{ old('credentials') }}</textarea>
                                    @error('credentials')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Experience --}}
                                <div>
                                    <label for="experience_description" class="block text-sm font-medium text-gray-700">Training Experience *</label>
                                    <p class="text-xs text-gray-500 mb-2">Describe your experience leading or assisting with NADA or acupuncture detox trainings.</p>
                                    <textarea name="experience_description" id="experience_description" rows="4" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" placeholder="e.g., I have led 12 NADA training sessions over the past 3 years, training over 100 new ADS practitioners...">{{ old('experience_description') }}</textarea>
                                    @error('experience_description')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- License Number --}}
                                <div>
                                    <label for="license_number" class="block text-sm font-medium text-gray-700">License Number</label>
                                    <p class="text-xs text-gray-500 mb-2">If applicable, provide your professional license number.</p>
                                    <input type="text" name="license_number" id="license_number" value="{{ old('license_number') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" placeholder="e.g., LAC-12345">
                                    @error('license_number')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Submit --}}
                            <div class="mt-6 flex items-center justify-between">
                                <a href="{{ route('account.edit') }}" class="text-sm text-gray-500 hover:text-gray-700">Back to Account</a>
                                <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-md text-white" style="background-color: #d39c27;">
                                    Submit Application
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
