@props(['title' => '', 'active' => false])

@php
    $classes = $active
        ? 'flex w-full items-center justify-between px-3 py-2 rounded bg-indigo-600 text-white text-sm font-medium shadow-inner transition duration-150 ease-in-out'
        : 'flex w-full items-center justify-between px-3 py-2 rounded text-gray-300 hover:bg-white/10 hover:text-white text-sm font-medium transition duration-150 ease-in-out';
@endphp

<div class="relative" x-data="{ open: false }">
    <button @click="open = !open" @click.outside="open = false" class="{{ $classes }}">
        <span>{{ $title }}</span>

        <svg class="h-4 w-4 transition-transform" :class="open && 'rotate-180'" xmlns="http://www.w3.org/2000/svg"
            fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <div x-show="open" x-transition
        class="absolute right-0 z-50 mt-2 w-56 rounded-xl border border-gray-200 bg-white shadow-lg">
        {{ $slot }}
    </div>
</div>
