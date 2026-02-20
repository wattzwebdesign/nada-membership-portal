@if(session()->has('impersonator_id'))
    <div class="bg-amber-50 border-b border-amber-200 text-amber-800 text-center text-sm py-1.5 px-4 print:hidden">
        Viewing as <strong>{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</strong> ({{ Auth::user()->email }})
        <form action="{{ url('/impersonate/stop') }}" method="POST" class="inline ml-2">
            @csrf
            <button type="submit" class="inline-flex items-center px-2.5 py-0.5 bg-amber-700 text-white text-xs font-medium rounded hover:bg-amber-800 transition">
                Switch Back to Admin
            </button>
        </form>
    </div>
@endif
