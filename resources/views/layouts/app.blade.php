<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" href="{{ asset('images/favicon/favicon-32x32-1.png') }}" sizes="32x32" type="image/png" />
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}" type="image/x-icon" />

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        <div class="flex min-h-screen">
            <aside
                class="hidden sm:flex sm:w-64 sm:flex-col sm:fixed sm:inset-y-0 bg-gray-900 text-white sm:h-screen sm:overflow-auto">
                @include('layouts.navigation')
            </aside>

            <!-- Mobile top bar -->
            <div class="sm:hidden bg-gray-900 text-white flex items-center justify-between px-4 py-3 w-full">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                    <x-application-logo class="h-6 w-6" />
                    <span class="font-semibold">{{ config('app.name', 'Laravel') }}</span>
                </a>
                <div class="flex items-center gap-2">
                    <a href="{{ route('profile.edit') }}" class="p-2 rounded hover:bg-white/10">
                        <!-- user icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.5 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="p-2 rounded hover:bg-white/10">
                            <!-- logout icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>

            <div class="flex-1 sm:ml-64">
                <!-- Toast notifications -->
                <div class="fixed top-4 right-4 z-50 space-y-2" aria-live="polite" aria-atomic="true">
                    @if (session('success'))
                        <div x-data="{ show: false }" x-init="$nextTick(() => {
                            show = true;
                            setTimeout(() => show = false, 5000)
                        })" x-show="show" x-cloak
                            x-transition:enter="transform ease-out duration-300"
                            x-transition:enter-start="-translate-y-4 opacity-0"
                            x-transition:enter-end="translate-y-0 opacity-100"
                            x-transition:leave="transform ease-in duration-300"
                            x-transition:leave-start="translate-y-0 opacity-100"
                            x-transition:leave-end="-translate-y-4 opacity-0"
                            class="max-w-sm w-full bg-green-600 text-white rounded shadow p-3 flex items-start gap-3">
                            <div class="flex-1 text-sm">{{ session('success') }}</div>
                            <button @click="show = false"
                                class="text-white opacity-80 hover:opacity-100 ms-2">&times;</button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div x-data="{ show: false }" x-init="$nextTick(() => {
                            show = true;
                            setTimeout(() => show = false, 5000)
                        })" x-show="show" x-cloak
                            x-transition:enter="transform ease-out duration-300"
                            x-transition:enter-start="-translate-y-4 opacity-0"
                            x-transition:enter-end="translate-y-0 opacity-100"
                            x-transition:leave="transform ease-in duration-300"
                            x-transition:leave-start="translate-y-0 opacity-100"
                            x-transition:leave-end="-translate-y-4 opacity-0"
                            class="max-w-sm w-full bg-red-600 text-white rounded shadow p-3 flex items-start gap-3">
                            <div class="flex-1 text-sm">{{ session('error') }}</div>
                            <button @click="show = false"
                                class="text-white opacity-80 hover:opacity-100 ms-2">&times;</button>
                        </div>
                    @endif

                    @if (session('status'))
                        <div x-data="{ show: false }" x-init="$nextTick(() => {
                            show = true;
                            setTimeout(() => show = false, 5000)
                        })" x-show="show" x-cloak
                            x-transition:enter="transform ease-out duration-300"
                            x-transition:enter-start="-translate-y-4 opacity-0"
                            x-transition:enter-end="translate-y-0 opacity-100"
                            x-transition:leave="transform ease-in duration-300"
                            x-transition:leave-start="translate-y-0 opacity-100"
                            x-transition:leave-end="-translate-y-4 opacity-0"
                            class="max-w-sm w-full bg-blue-600 text-white rounded shadow p-3 flex items-start gap-3">
                            <div class="flex-1 text-sm">{{ session('status') }}</div>
                            <button @click="show = false"
                                class="text-white opacity-80 hover:opacity-100 ms-2">&times;</button>
                        </div>
                    @endif
                </div>


                @if (isset($header))
                    <header class="bg-white shadow">
                        <div class="px-6 py-4">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                <!-- Page Content -->
                <main class="p-6">
                    @php $slot = $slot ?? null; @endphp
                    @isset($slot)
                        {{ $slot }}
                    @else
                        @yield('content')
                    @endisset
                </main>
            </div>
        </div>
    </div>
    @stack('scripts')
</body>


</html>
