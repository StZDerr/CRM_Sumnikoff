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
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label for="id" class="block text-sm font-medium text-gray-700 mb-1">Начальный ID</label>
                    <input id="id" name="id" type="number" min="1"
                        value="{{ old('id', $filters['id']) }}" class="w-full rounded border-gray-300" />
                </div>
                <div>
                    <label for="userId" class="block text-sm font-medium text-gray-700 mb-1">User ID</label>
                    <input id="userId" name="userId" type="text" value="{{ old('userId', $filters['userId']) }}"
                        class="w-full rounded border-gray-300" />
                </div>
                <div>
                    <label for="dateFrom" class="block text-sm font-medium text-gray-700 mb-1">Дата от</label>
                    <input id="dateFrom" name="dateFrom" type="datetime-local"
                        value="{{ old('dateFrom', $filters['dateFrom']) }}" class="w-full rounded border-gray-300" />
                </div>
                <div>
                    <label for="department" class="block text-sm font-medium text-gray-700 mb-1">Департамент</label>
                    <input id="department" name="department" type="text"
                        value="{{ old('department', $filters['department']) }}" class="w-full rounded border-gray-300" />
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

        <!-- Таблица звонков -->
        <div class="bg-white shadow rounded overflow-x-auto">
            <table class="w-full table-auto border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-left">

                        <th class="px-4 py-2 border-b">Дата/Время</th>
                        <th class="px-4 py-2 border-b">Направление</th>
                        <th class="px-4 py-2 border-b">Номер</th>

                        <th class="px-4 py-2 border-b">Длительность (мин)</th>

                        <th class="px-4 py-2 border-b">Файл записи</th>

                        <th class="px-4 py-2 border-b">abonent.phone</th>
                        <th class="px-4 py-2 border-b">abonent.firstName</th>
                        <th class="px-4 py-2 border-b">abonent.lastName</th>
                        <th class="px-4 py-2 border-b">abonent.email</th>
                        <th class="px-4 py-2 border-b">abonent.contactEmail</th>
                        <th class="px-4 py-2 border-b">Департамент</th>
                        <th class="px-4 py-2 border-b">Добавочный</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                        <tr class="hover:bg-gray-50 align-top">
                            <td class="px-4 py-2 border-b">{{ data_get($record, 'id', '—') }}</td>
                            <td class="px-4 py-2 border-b">{{ data_get($record, 'externalId', '—') }}</td>
                            <td class="px-4 py-2 border-b">{{ data_get($record, 'callId', '—') }}</td>
                            <td class="px-4 py-2 border-b">
                                {{ data_get($record, 'formattedDate') ?? data_get($record, 'date', '—') }}</td>
                            <td class="px-4 py-2 border-b">
                                {{ data_get($record, 'directionLabel', data_get($record, 'direction', '—')) }}</td>
                            <td class="px-4 py-2 border-b">{{ data_get($record, 'phone', '—') }}</td>
                            <td class="px-4 py-2 border-b">
                                {{ number_format((float) data_get($record, 'duration', 0), 0, ',', ' ') }}</td>
                            <td class="px-4 py-2 border-b">
                                {{ number_format((float) data_get($record, 'durationMinutes', 0), 2, ',', ' ') }}</td>
                            <td class="px-4 py-2 border-b">
                                {{ number_format((float) data_get($record, 'fileSize', 0), 0, ',', ' ') }}</td>
                            <td class="px-4 py-2 border-b">
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
                                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
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

                                        <audio id="audio-{{ data_get($record, 'dbId') }}" preload="none"
                                            src="{{ route('bilain.records.stream', data_get($record, 'dbId')) }}"></audio>
                                    </div>
                                @else
                                    <span class="text-gray-400">Нет</span>
                                    @if (data_get($record, 'recordFileError'))
                                        <div class="text-xs text-red-500">{{ data_get($record, 'recordFileError') }}</div>
                                    @endif
                                @endif
                            </td>
                            <td class="px-4 py-2 border-b">{{ data_get($record, 'comment', '—') }}</td>
                            <td class="px-4 py-2 border-b">{{ data_get($record, 'abonent.userId', '—') }}</td>
                            <td class="px-4 py-2 border-b">{{ data_get($record, 'abonent.phone', '—') }}</td>
                            <td class="px-4 py-2 border-b">{{ data_get($record, 'abonent.firstName', '—') }}</td>
                            <td class="px-4 py-2 border-b">{{ data_get($record, 'abonent.lastName', '—') }}</td>
                            <td class="px-4 py-2 border-b">{{ data_get($record, 'abonent.email', '—') }}</td>
                            <td class="px-4 py-2 border-b">{{ data_get($record, 'abonent.contactEmail', '—') }}</td>
                            <td class="px-4 py-2 border-b">{{ data_get($record, 'abonent.department', '—') }}</td>
                            <td class="px-4 py-2 border-b">{{ data_get($record, 'abonent.extension', '—') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="19" class="px-4 py-6 text-center text-gray-500">
                                Нет данных по выбранным параметрам.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $records->links() }}
        </div>
    </div>

    <script>
        (function() {
            const playBtnSelector = '.bilain-play-btn';
            let currentAudio = null;
            let currentBtn = null;

            function setPlaying(btn, playing) {
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

            document.querySelectorAll(playBtnSelector).forEach(btn => {
                const audioId = btn.getAttribute('data-audio-id');
                const audio = document.getElementById(audioId);
                if (!audio) return;

                btn.addEventListener('click', function() {
                    try {
                        if (currentAudio && currentAudio !== audio) {
                            currentAudio.pause();
                            if (currentBtn) setPlaying(currentBtn, false);
                        }

                        if (audio.paused) {
                            audio.play().catch(() => {});
                            setPlaying(btn, true);
                            currentAudio = audio;
                            currentBtn = btn;
                        } else {
                            audio.pause();
                            setPlaying(btn, false);
                            if (currentAudio === audio) {
                                currentAudio = null;
                                currentBtn = null;
                            }
                        }
                    } catch (e) {
                        console.error(e);
                    }
                });

                audio.addEventListener('ended', function() {
                    setPlaying(btn, false);
                    if (currentAudio === audio) {
                        currentAudio = null;
                        currentBtn = null;
                    }
                });

                audio.addEventListener('pause', function() {
                    setPlaying(btn, false);
                });

                audio.addEventListener('play', function() {
                    setPlaying(btn, true);
                });
            });
        })();
    </script>
@endsection
