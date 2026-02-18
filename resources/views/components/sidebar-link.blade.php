@props(['href', 'active' => false])

@php
$base = 'flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150';
$activeClass = $base . ' text-white';
$inactiveClass = $base . ' hover:bg-black/5';
@endphp

<a href="{{ $href }}"
   style="{{ $active ? 'background-color: #a5741e;' : 'color: #242424;' }}"
   {{ $attributes->merge(['class' => $active ? $activeClass : $inactiveClass]) }}>
    @if(isset($icon))
        <span class="w-5 h-5 shrink-0">{{ $icon }}</span>
    @endif
    {{ $slot }}
</a>
