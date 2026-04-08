@props(['active'])

@php
    $classes = ($active ?? false)
                ? 'flex items-center px-4 py-3 text-sm font-medium text-white bg-indigo-600 rounded-lg'
                : 'flex items-center px-4 py-3 text-sm font-medium text-zinc-400 hover:text-white hover:bg-zinc-800 rounded-lg transition-all duration-200';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
