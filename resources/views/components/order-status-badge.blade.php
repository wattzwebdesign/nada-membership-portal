@props(['status'])

@php
    $colors = match(is_string($status) ? $status : $status->value) {
        'pending' => 'bg-gray-100 text-gray-700',
        'paid' => 'bg-green-100 text-green-700',
        'processing' => 'bg-blue-100 text-blue-700',
        'shipped' => 'bg-blue-100 text-blue-700',
        'delivered' => 'bg-green-100 text-green-700',
        'canceled' => 'bg-red-100 text-red-700',
        'refunded' => 'bg-yellow-100 text-yellow-700',
        'transferred' => 'bg-green-100 text-green-700',
        'failed' => 'bg-red-100 text-red-700',
        default => 'bg-gray-100 text-gray-700',
    };

    $label = is_string($status) ? ucfirst($status) : $status->getLabel();
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {$colors}"]) }}>
    {{ $label }}
</span>
