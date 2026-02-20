@if(session()->has('impersonator_id'))
    <div class="sticky top-0 z-[9999] bg-yellow-400 text-yellow-900 text-center text-sm font-semibold py-2 px-4 print:hidden">
        Viewing as <strong>{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</strong> ({{ Auth::user()->email }})
        <form action="{{ url('/impersonate/stop') }}" method="POST" class="inline ml-3">
            @csrf
            <button type="submit" class="inline-flex items-center px-3 py-1 bg-yellow-800 text-white text-xs font-bold rounded hover:bg-yellow-900 transition">
                Switch Back to Admin
            </button>
        </form>
    </div>
@endif
