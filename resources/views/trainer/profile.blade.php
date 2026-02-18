<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Public Profile') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Preview Banner --}}
            <div class="border rounded-lg p-4 flex items-center justify-between" style="border-color: #d39c27; background-color: rgba(211, 156, 39, 0.05);">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" style="color: #d39c27;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <div>
                        <p class="text-sm font-medium" style="color: #374269;">This information is visible to the public on the trainer directory.</p>
                        <p class="text-xs text-gray-500 mt-0.5">Potential attendees will see this when they find you.</p>
                    </div>
                </div>
                <a href="{{ route('public.trainers.show', $trainer) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 border text-xs font-medium rounded-md whitespace-nowrap" style="border-color: #374269; color: #374269;">
                    View Public Profile
                    <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            </div>

            {{-- Profile Form --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-2" style="color: #374269;">Edit Public Profile</h3>
                    <p class="text-sm text-gray-500 mb-6">Update the information that appears on your public trainer listing and profile page.</p>

                    <form method="POST" action="{{ route('trainer.profile.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="space-y-5">
                            {{-- Profile Preview --}}
                            <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg">
                                @if ($trainer->profile_photo_url)
                                    <img src="{{ $trainer->profile_photo_url }}" alt="{{ $trainer->full_name }}" class="h-16 w-16 rounded-full object-cover">
                                @else
                                    <img src="{{ asset('images/nada-mark.png') }}" alt="NADA" class="h-16 w-16 rounded-full object-contain bg-white border border-gray-200 p-0.5">
                                @endif
                                <div>
                                    <p class="text-base font-semibold text-gray-900">{{ $trainer->full_name }}</p>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium mt-1" style="background-color: #d39c27; color: white;">
                                        NADA Registered Trainer
                                    </span>
                                </div>
                            </div>

                            {{-- Bio --}}
                            <div>
                                <label for="bio" class="block text-sm font-medium text-gray-700">Bio</label>
                                <textarea name="bio" id="bio" rows="5" maxlength="2000" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" placeholder="Tell potential attendees about your background, experience, and training approach...">{{ old('bio', $trainer->bio) }}</textarea>
                                <p class="mt-1 text-xs text-gray-400">Max 2,000 characters.</p>
                                @error('bio')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Contact Info --}}
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                                <input type="tel" name="phone" id="phone" value="{{ old('phone', $trainer->phone) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" placeholder="(555) 123-4567">
                                <p class="mt-1 text-xs text-gray-400">Displayed on your public profile. Leave blank to hide.</p>
                                @error('phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Location --}}
                            <div>
                                <h4 class="text-sm font-semibold text-gray-800 mb-3">Location</h4>
                                <p class="text-xs text-gray-500 mb-3">Your location is used to place you on the trainer directory map.</p>

                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                    <div class="col-span-2 sm:col-span-1">
                                        <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                                        <input type="text" name="city" id="city" value="{{ old('city', $trainer->city) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                                    </div>
                                    <div>
                                        <label for="state" class="block text-sm font-medium text-gray-700">State</label>
                                        <input type="text" name="state" id="state" value="{{ old('state', $trainer->state) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                                    </div>
                                    <div>
                                        <label for="zip" class="block text-sm font-medium text-gray-700">ZIP</label>
                                        <input type="text" name="zip" id="zip" value="{{ old('zip', $trainer->zip) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <label for="country" class="block text-sm font-medium text-gray-700">Country</label>
                                    <select name="country" id="country" class="mt-1 block w-full sm:w-1/2 rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                                        <option value="US" {{ old('country', $trainer->country) === 'US' ? 'selected' : '' }}>United States</option>
                                        <option value="CA" {{ old('country', $trainer->country) === 'CA' ? 'selected' : '' }}>Canada</option>
                                        <option value="GB" {{ old('country', $trainer->country) === 'GB' ? 'selected' : '' }}>United Kingdom</option>
                                        <option value="AU" {{ old('country', $trainer->country) === 'AU' ? 'selected' : '' }}>Australia</option>
                                    </select>
                                </div>

                                @if ($trainer->hasCoordinates())
                                    <p class="mt-2 text-xs text-green-600 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Your location is showing on the map.
                                    </p>
                                @else
                                    <p class="mt-2 text-xs text-amber-600 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"/></svg>
                                        Add your city and state to appear on the directory map.
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="mt-6 flex items-center gap-3">
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white" style="background-color: #374269;">
                                Save Public Profile
                            </button>
                            <a href="{{ route('trainer.dashboard') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
