@props(['active' => false])

@php
    // делаем так, чтобы фон активного пункта доходил до углов выпадающего контейнера
    $base =
        'block w-full px-4 py-2 text-start text-sm leading-5 transition duration-150 ease-in-out first:rounded-t-xl last:rounded-b-xl';
    $activeClasses = $active
        ? 'text-white bg-indigo-600'
        : 'text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100';
@endphp

<a {{ $attributes->merge(['class' => trim($base . ' ' . $activeClasses)]) }}>{{ $slot }}</a>
