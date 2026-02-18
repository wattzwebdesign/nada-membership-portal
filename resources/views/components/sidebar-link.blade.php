@props(['href', 'active' => false])

@php
$classes = $active
    ? 'flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium bg-white/15 text-white'
    : 'flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition-colors duration-150';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
    @if(isset($icon))
        <span class="w-5 h-5 shrink-0">{{ $icon }}</span>
    @endif
    {{ $slot }}
</a>
