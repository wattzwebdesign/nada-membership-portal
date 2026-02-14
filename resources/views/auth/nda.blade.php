<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-lg font-semibold text-center" style="color: #374269;">
            {{ $agreement->title }}
        </h2>
    </div>

    <div class="prose prose-sm max-w-none mb-6 max-h-96 overflow-y-auto border border-gray-200 rounded-md p-4 bg-gray-50">
        {!! $agreement->content !!}
    </div>

    <form method="POST" action="{{ route('nda.accept') }}">
        @csrf

        <div class="mb-4">
            <label class="flex items-start gap-2">
                <input type="checkbox" name="agree" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 mt-0.5" style="color: #374269;">
                <span class="text-sm text-gray-700">I have read and agree to the above agreement</span>
            </label>
            @error('agree')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white focus:outline-none focus:ring-2 focus:ring-offset-2" style="background-color: #374269;">
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
</x-guest-layout>
