@props(['active'])

@php
    $classes =
        $active ?? false
            ? 'block w-full text-left px-3 py-2 rounded bg-indigo-600 text-white text-sm font-medium shadow-inner transition duration-150 ease-in-out'
            : 'block w-full text-left px-3 py-2 rounded text-gray-300 hover:bg-white/10 hover:text-white text-sm font-medium transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
