<!-- resources/views/calls.blade.php -->

@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Статистика звонков</h1>

        @if ($error)
            <div class="mb-4 rounded border border-red-200 bg-red-50 px-4 py-3 text-red-700">
                {{ $error }}
            </div>
        @endif

        <form method="GET" action="{{ route('bilain.index') }}" class="bg-white shadow rounded p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="dateFrom" class="block text-sm font-medium text-gray-700 mb-1">Дата от</label>
                    <input id="dateFrom" name="dateFrom" type="datetime-local"
                        value="{{ old('dateFrom', $filters['dateFrom']) }}" class="w-full rounded border-gray-300" />
                </div>
                <div>
                    <label for="dateTo" class="block text-sm font-medium text-gray-700 mb-1">Дата до</label>
                    <input id="dateTo" name="dateTo" type="datetime-local"
                        value="{{ old('dateTo', $filters['dateTo']) }}" class="w-full rounded border-gray-300" />
                </div>
            </div>

            <div class="mt-4 flex gap-3">
                <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Применить</button>
                <a href="{{ route('bilain.index') }}"
                    class="px-4 py-2 rounded bg-gray-200 text-gray-700 hover:bg-gray-300">Сбросить</a>
            </div>
        </form>

        <!-- Основная статистика -->
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="p-4 bg-white shadow rounded">
                <h2 class="font-semibold text-gray-700">Всего звонков</h2>
                <p class="text-3xl font-bold text-emerald-600">{{ $stats['callsCount'] }}</p>
            </div>
            <div class="p-4 bg-white shadow rounded">
                <h2 class="font-semibold text-gray-700">Общая длительность</h2>
                <p class="text-3xl font-bold text-blue-600">{{ $stats['totalDurationHuman'] }}</p>
            </div>
            <div class="p-4 bg-white shadow rounded">
                <h2 class="font-semibold text-gray-700">Средняя длительность</h2>
                <p class="text-3xl font-bold text-purple-600">{{ $stats['avgDurationHuman'] }}</p>
            </div>
        </div>

        <!-- Таблица звонков (в стиле операций, сгруппирована по дате) -->
        <div class="bg-white shadow rounded p-4">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-100 text-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left">Время</th>
                        <th class="px-4 py-3 text-left">Направление</th>
                        <th class="px-4 py-3 text-left">На какой</th>
                        <th class="px-4 py-3 text-left">Файл записи</th>
                        <th class="px-4 py-3 text-left">С какого</th>
                        <th class="px-4 py-3 text-left">Имя</th>
                        <th class="px-4 py-3 text-left">Фамилия</th>
                        <th class="px-4 py-3 text-left">Email</th>
                        <th class="px-4 py-3 text-left">Департамент</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php
                        $items =
                            is_object($records) && method_exists($records, 'items')
                                ? collect($records->items())
                                : collect($records);

                        $groups = $items->groupBy(
                            fn($r) => $r['date'] ?? null
                                ? \Illuminate\Support\Carbon::parse($r['date'])->format('Y-m-d')
                                : 'Без даты',
                        );
                    @endphp

                    @forelse($groups as $date => $rows)
                        @php
                            if ($date === 'Без даты') {
                                $labelText = 'Без даты';
                            } else {
                                $carbonDate = \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $date);
                                $prefix = $carbonDate->isToday()
                                    ? 'Сегодня, '
                                    : ($carbonDate->isYesterday()
                                        ? 'Вчера, '
                                        : '');
                                $labelText = $prefix . $carbonDate->format('d.m.Y');
                            }
                        @endphp

                        <tr>
                            <td colspan="9" class="bg-indigo-50 py-2">
                                <div class="mx-auto max-w-prose text-center">
                                    <span
                                        class="inline-flex items-center gap-2 px-4 py-1 rounded-full bg-indigo-100 text-indigo-700 text-sm font-semibold shadow-sm">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z" />
                                        </svg>
                                        {{ $labelText }}
                                    </span>
                                </div>
                            </td>
                        </tr>

                        @foreach ($rows as $record)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-2 font-medium text-gray-700">
                                    {{ isset($record['date']) ? \Illuminate\Support\Carbon::parse($record['date'])->format('H:i') : '-' }}
                                </td>

                                <td class="px-4 py-2">
                                    {{ data_get($record, 'directionLabel', data_get($record, 'direction', '—')) }}</td>

                                <td class="px-4 py-2">{{ data_get($record, 'phone', '—') }}</td>

                                <td class="px-4 py-2">
                                    @if (data_get($record, 'recordFilePath'))
                                        <div class="flex items-center gap-3">
                                            <button type="button" data-audio-id="audio-{{ data_get($record, 'dbId') }}"
                                                class="bilain-play-btn group relative inline-flex items-center justify-center w-11 h-11 rounded-full
                                                    bg-gradient-to-br from-emerald-500 to-emerald-600
                                                    hover:from-emerald-600 hover:to-emerald-700
                                                    active:scale-95
                                                    transition-all duration-200 ease-out
                                                    shadow-md hover:shadow-lg
                                                    focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2"
                                                aria-label="Воспроизвести запись">

                                                <!-- Play -->
                                                <svg class="play-icon w-5 h-5 text-white transition-transform duration-200 group-hover:scale-110"
                                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                    fill="currentColor">
                                                    <path d="M8 5v14l11-7z" />
                                                </svg>

                                                <!-- Pause -->
                                                <svg class="pause-icon hidden w-5 h-5 text-white transition-transform duration-200 group-hover:scale-110"
                                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M10 9v6M14 9v6" />
                                                </svg>
                                            </button>

                                            <span class="text-sm text-gray-600 tabular-nums bilain-audio-duration"
                                                data-duration-for="audio-{{ data_get($record, 'dbId') }}">--:--</span>

                                            <audio id="audio-{{ data_get($record, 'dbId') }}" preload="metadata"
                                                src="{{ route('bilain.records.stream', data_get($record, 'dbId')) }}"></audio>
                                        </div>
                                    @else
                                        <span class="text-gray-400">Нет</span>
                                        @if (data_get($record, 'recordFileError'))
                                            <div class="text-xs text-red-500">{{ data_get($record, 'recordFileError') }}
                                            </div>
                                        @endif
                                    @endif
                                </td>

                                <td class="px-4 py-2">{{ data_get($record, 'abonent.phone', '—') }}</td>
                                <td class="px-4 py-2">{{ data_get($record, 'abonent.firstName', '—') }}</td>
                                <td class="px-4 py-2">{{ data_get($record, 'abonent.lastName', '—') }}</td>
                                <td class="px-4 py-2">{{ data_get($record, 'abonent.email', '—') }}</td>
                                <td class="px-4 py-2">{{ data_get($record, 'abonent.department', '—') }}</td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-6 text-center text-gray-500">Нет данных по выбранным
                                параметрам.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $records->links() }}
        </div>

        <div id="bilain-bottom-player"
            class="hidden fixed bottom-4 left-4 right-4 sm:left-[calc(16rem+1rem)] z-50 rounded-2xl border border-gray-200 bg-white shadow-2xl px-4 py-3">
            <div class="flex items-center justify-between gap-3 mb-2">
                <div id="bilain-bottom-player-title" class="text-sm font-medium text-gray-700 truncate">
                    Воспроизведение записи
                </div>
                <button id="bilain-bottom-player-close" type="button"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-full text-gray-500 hover:bg-gray-100 hover:text-gray-700"
                    aria-label="Закрыть плеер">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <audio id="bilain-bottom-audio" class="w-full" controls preload="none"></audio>
        </div>
    </div>

    <script>
        (function() {
            const playBtnSelector = '.bilain-play-btn';
            const playerWrap = document.getElementById('bilain-bottom-player');
            const playerAudio = document.getElementById('bilain-bottom-audio');
            const playerTitle = document.getElementById('bilain-bottom-player-title');
            const playerCloseBtn = document.getElementById('bilain-bottom-player-close');

            let currentAudio = null;
            let currentBtn = null;
            let currentTrackId = null;

            function formatDuration(secondsTotal) {
                const total = Math.max(0, Math.floor(secondsTotal));
                const hours = Math.floor(total / 3600);
                const minutes = Math.floor((total % 3600) / 60);
                const seconds = total % 60;

                if (hours > 0) {
                    return `${hours}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                }

                return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            }

            function updateDurationLabel(audio, durationEl) {
                if (!durationEl) return;

                if (!Number.isFinite(audio.duration) || audio.duration <= 0) {
                    durationEl.textContent = '--:--';
                    return;
                }

                durationEl.textContent = formatDuration(audio.duration);
            }

            function setPlaying(btn, playing) {
                if (!btn) return;
                const playIcon = btn.querySelector('.play-icon');
                const pauseIcon = btn.querySelector('.pause-icon');
                if (playing) {
                    btn.classList.add('bg-red-600');
                    btn.classList.remove('bg-emerald-600');
                    if (playIcon) playIcon.classList.add('hidden');
                    if (pauseIcon) pauseIcon.classList.remove('hidden');
                } else {
                    btn.classList.remove('bg-red-600');
                    btn.classList.add('bg-emerald-600');
                    if (playIcon) playIcon.classList.remove('hidden');
                    if (pauseIcon) pauseIcon.classList.add('hidden');
                }
            }

            function showPlayer() {
                if (!playerWrap) return;
                playerWrap.classList.remove('hidden');
            }

            function hidePlayer() {
                if (!playerWrap || !playerAudio) return;
                playerAudio.pause();
                playerWrap.classList.add('hidden');

                if (currentBtn) {
                    setPlaying(currentBtn, false);
                }

                currentBtn = null;
                currentTrackId = null;
            }

            if (playerCloseBtn) {
                playerCloseBtn.addEventListener('click', hidePlayer);
            }

            if (playerAudio) {
                playerAudio.addEventListener('play', function() {
                    if (currentBtn) setPlaying(currentBtn, true);
                });

                playerAudio.addEventListener('pause', function() {
                    if (currentBtn) setPlaying(currentBtn, false);
                });

                playerAudio.addEventListener('ended', function() {
                    if (currentBtn) setPlaying(currentBtn, false);
                });
            }

            document.querySelectorAll(playBtnSelector).forEach(btn => {
                const audioId = btn.getAttribute('data-audio-id');
                const audio = document.getElementById(audioId);
                const durationEl = document.querySelector(
                    `.bilain-audio-duration[data-duration-for="${audioId}"]`);
                if (!audio) return;

                audio.preload = 'metadata';
                audio.addEventListener('loadedmetadata', function() {
                    updateDurationLabel(audio, durationEl);
                });

                audio.addEventListener('durationchange', function() {
                    updateDurationLabel(audio, durationEl);
                });

                audio.addEventListener('error', function() {
                    if (durationEl) durationEl.textContent = '—';
                });

                if (audio.readyState >= 1) {
                    updateDurationLabel(audio, durationEl);
                } else {
                    audio.load();
                }

                btn.addEventListener('click', function() {
                    try {
                        if (!playerAudio) return;

                        if (currentBtn && currentBtn !== btn) {
                            setPlaying(currentBtn, false);
                        }

                        const sameTrack = currentTrackId === audioId;

                        if (!sameTrack) {
                            playerAudio.src = audio.currentSrc || audio.src;
                            currentTrackId = audioId;
                            currentAudio = audio;

                            const phone = btn.closest('tr')?.querySelector('td:nth-child(3)')
                                ?.textContent?.trim() || '';
                            const title = phone ? `Запись: ${phone}` : 'Воспроизведение записи';
                            if (playerTitle) playerTitle.textContent = title;
                        }

                        if (playerAudio.paused || !sameTrack) {
                            showPlayer();
                            playerAudio.play().catch(() => {});
                            currentBtn = btn;
                            setPlaying(btn, true);
                        } else {
                            playerAudio.pause();
                            setPlaying(btn, false);
                            if (currentBtn === btn) {
                                currentBtn = null;
                            }
                        }
                    } catch (e) {
                        console.error(e);
                    }
                });
            });
        })();
    </script>
@endsection
