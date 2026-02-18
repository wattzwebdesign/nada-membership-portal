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
                    <a href="{{ route('membership.plans') }}" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-brand-primary">
                        View Discounted Plans
                    </a>
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6" x-data="{
                        discountType: '{{ old('discount_type', '') }}',
                        dragging: false,
                        files: [],
                        dobRaw: '{{ old('date_of_birth', '') }}',
                        handleFiles(fileList) {
                            const input = this.$refs.fileInput;
                            const dt = new DataTransfer();
                            for (const f of this.files) { dt.items.add(f); }
                            for (const f of fileList) { dt.items.add(f); }
                            input.files = dt.files;
                            this.files = Array.from(dt.files);
                        },
                        removeFile(index) {
                            this.files.splice(index, 1);
                            const dt = new DataTransfer();
                            for (const f of this.files) { dt.items.add(f); }
                            this.$refs.fileInput.files = dt.files;
                        },
                        formatSize(bytes) {
                            if (bytes < 1024) return bytes + ' B';
                            if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
                            return (bytes / 1048576).toFixed(1) + ' MB';
                        },
                        formatDob(e) {
                            let val = e.target.value.replace(/\D/g, '');
                            if (val.length > 8) val = val.slice(0, 8);
                            let formatted = '';
                            if (val.length >= 2) {
                                formatted = val.slice(0, 2) + '/';
                                if (val.length >= 4) {
                                    formatted += val.slice(2, 4) + '/';
                                    formatted += val.slice(4);
                                } else {
                                    formatted += val.slice(2);
                                }
                            } else {
                                formatted = val;
                            }
                            e.target.value = formatted;
                            this.dobRaw = formatted;
                        },
                        get dobIso() {
                            const parts = this.dobRaw.split('/');
                            if (parts.length === 3 && parts[2].length === 4) {
                                return parts[2] + '-' + parts[0].padStart(2, '0') + '-' + parts[1].padStart(2, '0');
                            }
                            return '';
                        }
                    }">
                        <h3 class="text-lg font-semibold mb-2 text-brand-primary">Discount Request Form</h3>
                        <p class="text-sm text-gray-500 mb-6">NADA offers discounted membership rates for students and seniors. Submit your request with supporting documentation and we will review it promptly.</p>

                        <form method="POST" action="{{ route('discount.request.store') }}" enctype="multipart/form-data">
                            @csrf

                            <div class="space-y-6">
                                {{-- Discount Type --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-3">Discount Type *</label>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <label class="relative flex items-start p-4 border-2 rounded-lg cursor-pointer transition"
                                            :class="discountType === 'student' ? 'border-amber-400 bg-amber-50' : 'border-gray-200 hover:border-gray-300'">
                                            <input type="radio" name="discount_type" value="student" class="mt-0.5 mr-3 text-brand-secondary" x-model="discountType" required>
                                            <div>
                                                <span class="text-sm font-medium text-gray-900">Student Discount</span>
                                                <p class="text-xs text-gray-500 mt-1">For currently enrolled students. Student ID or enrollment verification required.</p>
                                            </div>
                                        </label>
                                        <label class="relative flex items-start p-4 border-2 rounded-lg cursor-pointer transition"
                                            :class="discountType === 'senior' ? 'border-amber-400 bg-amber-50' : 'border-gray-200 hover:border-gray-300'">
                                            <input type="radio" name="discount_type" value="senior" class="mt-0.5 mr-3 text-brand-secondary" x-model="discountType">
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

                                {{-- Student-specific fields --}}
                                <template x-if="discountType === 'student'">
                                    <div class="space-y-6">
                                        <div>
                                            <label for="school_name" class="block text-sm font-medium text-gray-700">What school do you attend? *</label>
                                            <input type="text" name="school_name" id="school_name" value="{{ old('school_name') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" placeholder="e.g., University of California, Los Angeles">
                                            @error('school_name')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div>
                                            <label for="years_remaining" class="block text-sm font-medium text-gray-700">How many more years do you plan on being a student? *</label>
                                            <input type="number" name="years_remaining" id="years_remaining" value="{{ old('years_remaining') }}" min="1" max="10" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" placeholder="e.g., 2">
                                            @error('years_remaining')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </template>

                                {{-- Senior-specific fields --}}
                                <template x-if="discountType === 'senior'">
                                    <div class="space-y-6">
                                        <div>
                                            <label for="dob_display" class="block text-sm font-medium text-gray-700">Date of Birth *</label>
                                            <input type="text" id="dob_display" x-on:input="formatDob($event)" :value="dobRaw" placeholder="MM/DD/YYYY" maxlength="10" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" inputmode="numeric">
                                            <input type="hidden" name="date_of_birth" :value="dobIso">
                                            @error('date_of_birth')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </template>

                                {{-- Proof Description --}}
                                <div>
                                    <label for="proof_description" class="block text-sm font-medium text-gray-700">Additional Notes</label>
                                    <p class="text-xs text-gray-500 mb-2">Any additional information you'd like to provide about your eligibility.</p>
                                    <textarea name="proof_description" id="proof_description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" placeholder="Optional: any additional details about your eligibility.">{{ old('proof_description') }}</textarea>
                                    @error('proof_description')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- File Upload --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">
                                        <template x-if="discountType === 'student'">
                                            <span>Proof of Studentship *</span>
                                        </template>
                                        <template x-if="discountType === 'senior'">
                                            <span>Photo ID / Document Verifying Date of Birth *</span>
                                        </template>
                                        <template x-if="discountType !== 'student' && discountType !== 'senior'">
                                            <span>Upload Supporting Documents *</span>
                                        </template>
                                    </label>
                                    <p class="text-xs text-gray-500 mb-2">
                                        <template x-if="discountType === 'student'">
                                            <span>Upload your student ID, enrollment letter, or other proof of current enrollment.</span>
                                        </template>
                                        <template x-if="discountType === 'senior'">
                                            <span>Upload a photo of your government-issued ID or other document showing your date of birth.</span>
                                        </template>
                                        <template x-if="discountType !== 'student' && discountType !== 'senior'">
                                            <span>Select a discount type above, then upload your supporting documents.</span>
                                        </template>
                                    </p>

                                    <div
                                        x-on:click="$refs.fileInput.click()"
                                        x-on:dragover.prevent="dragging = true"
                                        x-on:dragleave.prevent="dragging = false"
                                        x-on:drop.prevent="dragging = false; handleFiles($event.dataTransfer.files)"
                                        :class="dragging ? 'border-brand-primary bg-blue-50' : 'border-gray-300'"
                                        class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed rounded-md cursor-pointer hover:border-gray-400 transition-colors"
                                    >
                                        <div class="space-y-1 text-center">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            <div class="flex text-sm text-gray-600">
                                                <span class="font-medium hover:underline text-brand-primary">Upload files</span>
                                                <p class="pl-1">or drag and drop</p>
                                            </div>
                                            <p class="text-xs text-gray-500">PDF, JPG, PNG, DOC, DOCX up to 10MB each</p>
                                        </div>
                                    </div>

                                    <input
                                        x-ref="fileInput"
                                        name="proof_documents[]"
                                        type="file"
                                        class="hidden"
                                        multiple
                                        accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                        x-on:change="handleFiles($event.target.files)"
                                    >

                                    {{-- Selected files list --}}
                                    <template x-if="files.length > 0">
                                        <ul class="mt-3 space-y-2">
                                            <template x-for="(file, index) in files" :key="index">
                                                <li class="flex items-center justify-between text-sm bg-gray-50 rounded-md px-3 py-2">
                                                    <div class="flex items-center min-w-0">
                                                        <svg class="w-4 h-4 mr-2 text-green-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                        </svg>
                                                        <span class="text-gray-700 truncate" x-text="file.name"></span>
                                                        <span class="ml-2 text-gray-400 text-xs shrink-0" x-text="formatSize(file.size)"></span>
                                                    </div>
                                                    <button type="button" x-on:click.stop="removeFile(index)" class="ml-3 text-red-400 hover:text-red-600 shrink-0" title="Remove">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </li>
                                            </template>
                                        </ul>
                                    </template>

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
                                <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-md text-white bg-brand-primary">
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
