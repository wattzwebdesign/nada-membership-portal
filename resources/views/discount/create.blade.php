<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Request Discount') }}
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

            {{-- Already approved --}}
            @if (auth()->user()->discount_approved)
                <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
                    <svg class="mx-auto h-12 w-12 text-green-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <h3 class="text-lg font-semibold text-green-800">Discount Active</h3>
                    <p class="text-sm text-green-600 mt-1">Your {{ ucfirst(auth()->user()->discount_type) }} discount is already approved and active.</p>
                    <a href="{{ route('membership.plans') }}" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white" style="background-color: #374269;">
                        View Discounted Plans
                    </a>
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-2" style="color: #374269;">Discount Request Form</h3>
                        <p class="text-sm text-gray-500 mb-6">NADA offers discounted membership rates for students and seniors. Submit your request with supporting documentation and we will review it promptly.</p>

                        <form method="POST" action="{{ route('discount.request.store') }}" enctype="multipart/form-data">
                            @csrf

                            <div class="space-y-6">
                                {{-- Discount Type --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-3">Discount Type *</label>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <label class="relative flex items-start p-4 border-2 rounded-lg cursor-pointer transition {{ old('discount_type') === 'student' ? 'border-amber-400 bg-amber-50' : 'border-gray-200 hover:border-gray-300' }}">
                                            <input type="radio" name="discount_type" value="student" class="mt-0.5 mr-3" style="color: #d39c27;" {{ old('discount_type') === 'student' ? 'checked' : '' }} required>
                                            <div>
                                                <span class="text-sm font-medium text-gray-900">Student Discount</span>
                                                <p class="text-xs text-gray-500 mt-1">For currently enrolled students. Student ID or enrollment verification required.</p>
                                            </div>
                                        </label>
                                        <label class="relative flex items-start p-4 border-2 rounded-lg cursor-pointer transition {{ old('discount_type') === 'senior' ? 'border-amber-400 bg-amber-50' : 'border-gray-200 hover:border-gray-300' }}">
                                            <input type="radio" name="discount_type" value="senior" class="mt-0.5 mr-3" style="color: #d39c27;" {{ old('discount_type') === 'senior' ? 'checked' : '' }}>
                                            <div>
                                                <span class="text-sm font-medium text-gray-900">Senior Discount</span>
                                                <p class="text-xs text-gray-500 mt-1">For seniors. Government-issued ID or other age verification required.</p>
                                            </div>
                                        </label>
                                    </div>
                                    @error('discount_type')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Proof Description --}}
                                <div>
                                    <label for="proof_description" class="block text-sm font-medium text-gray-700">Proof / Documentation Description *</label>
                                    <p class="text-xs text-gray-500 mb-2">Describe the documentation you are providing as proof of eligibility.</p>
                                    <textarea name="proof_description" id="proof_description" rows="4" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" placeholder="e.g., I am a full-time student at XYZ University, enrolled in the Acupuncture program. I have attached my current student ID.">{{ old('proof_description') }}</textarea>
                                    @error('proof_description')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- File Upload --}}
                                <div>
                                    <label for="proof_documents" class="block text-sm font-medium text-gray-700">Upload Supporting Documents *</label>
                                    <p class="text-xs text-gray-500 mb-2">Upload a photo or scan of your student ID, enrollment letter, or senior identification.</p>
                                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                        <div class="space-y-1 text-center">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            <div class="flex text-sm text-gray-600">
                                                <label for="proof_documents" class="relative cursor-pointer rounded-md font-medium hover:underline" style="color: #374269;">
                                                    <span>Upload files</span>
                                                    <input id="proof_documents" name="proof_documents[]" type="file" class="sr-only" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                                </label>
                                                <p class="pl-1">or drag and drop</p>
                                            </div>
                                            <p class="text-xs text-gray-500">PDF, JPG, PNG, DOC, DOCX up to 10MB each</p>
                                        </div>
                                    </div>
                                    @error('proof_documents')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    @error('proof_documents.*')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Submit --}}
                            <div class="mt-6 flex items-center justify-between">
                                <a href="{{ route('discount.request.status') }}" class="text-sm text-gray-500 hover:text-gray-700">Check Request Status</a>
                                <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-md text-white" style="background-color: #374269;">
                                    Submit Request
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
