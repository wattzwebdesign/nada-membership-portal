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

            @if (session('info'))
                <div class="mb-6 bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-md">
                    {{ session('info') }}
                </div>
            @endif

            {{-- Already a trainer --}}
            @if (auth()->user()->isTrainer())
                <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
                    <svg class="mx-auto h-12 w-12 text-green-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <h3 class="text-lg font-semibold text-green-800">You Are a Registered Trainer</h3>
                    <p class="text-sm text-green-600 mt-1">Your trainer account is active. Access the Trainer Portal to manage trainings and payouts.</p>
                    <a href="{{ route('trainer.dashboard') }}" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-brand-primary">
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
                            <h3 class="text-lg font-semibold text-brand-primary">Trainer Application</h3>
                            <p class="text-sm text-gray-500 mt-2">Registered Trainers can host NADA trainings, manage attendees, issue certificates, and earn payouts through Stripe Connect. Complete the application below, upload the required documents, and pay the $75 application fee. An administrator will review your submission.</p>
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

                        <form method="POST" action="{{ route('trainer-application.store') }}" enctype="multipart/form-data">
                            @csrf

                            <div class="space-y-6">
                                {{-- Name Fields --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label for="first_name" class="block text-sm font-medium text-gray-700">First Name *</label>
                                        <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $user->first_name) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                                        @error('first_name')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name *</label>
                                        <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $user->last_name) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                                        @error('last_name')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Email --}}
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                                    @error('email')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Phone --}}
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone / Mobile *</label>
                                    <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                                    @error('phone')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- File Uploads --}}
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Required Documents</h4>
                                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                            <div>
                                                <label for="letter_of_nomination" class="block text-sm font-medium text-gray-700 mb-1">Letter of Nomination *</label>
                                                <p class="text-xs text-gray-500 mb-2">PDF, JPG, PNG, DOC, or DOCX (max 10MB)</p>
                                                <input type="file" name="letter_of_nomination" id="letter_of_nomination" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                                                @error('letter_of_nomination')
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            <div>
                                                <label for="application_submission" class="block text-sm font-medium text-gray-700 mb-1">Application Submission *</label>
                                                <p class="text-xs text-gray-500 mb-2">PDF, JPG, PNG, DOC, or DOCX (max 10MB)</p>
                                                <input type="file" name="application_submission" id="application_submission" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                                                @error('application_submission')
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Application Fee --}}
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h4 class="text-sm font-semibold text-gray-700">Application Fee</h4>
                                            <p class="text-xs text-gray-500 mt-0.5">One-time, non-refundable fee</p>
                                        </div>
                                        <span class="text-lg font-bold text-brand-primary">$75.00</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Submit --}}
                            <div class="mt-6 flex items-center justify-between">
                                <a href="{{ route('account.edit') }}" class="text-sm text-gray-500 hover:text-gray-700">Back to Account</a>
                                <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-md text-white bg-brand-secondary">
                                    Submit Application & Pay $75
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
