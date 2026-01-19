@extends('layouts.app')

@section('content')
    <div class="mb-4 bg-white shadow-sm rounded-lg p-3 flex flex-wrap items-center justify-between gap-4">

        {{-- Легенда --}}
        <div class="flex flex-wrap gap-3 items-center">
            @foreach ($statuses as $status)
                <div class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <span class="w-3 h-3 rounded-sm" style="background-color: {{ $status->color }};"></span>
                    <span>{{ $status->title }}</span>
                </div>
            @endforeach

            <div class="inline-flex items-center gap-2 text-sm text-gray-700">
                <span class="w-3 h-3 rounded-sm bg-blue-500"></span>
                <span>Комментарий</span>
            </div>
        </div>

        {{-- Выбор года --}}
        <form method="GET"
            class="flex items-center gap-3 bg-gradient-to-r from-indigo-50 to-blue-50
             border border-indigo-100 rounded-lg px-4 py-2 shadow-sm">

            <div class="flex items-center gap-2 text-indigo-700">
                {{-- Иконка календаря --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10m-11 9h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>

                <span class="text-sm font-semibold">
                    Год
                </span>
            </div>

            <select name="year" onchange="this.form.submit()"
                class="bg-white border border-indigo-200 rounded-md px-8 py-1.5 text-sm
                   text-gray-800 font-medium
                   focus:outline-none focus:ring-2 focus:ring-indigo-400
                   hover:border-indigo-400 transition">
                @for ($y = now()->year + 1; $y >= now()->year - 5; $y--)
                    <option value="{{ $y }}" @selected($y == $year)>
                        {{ $y }}
                    </option>
                @endfor
            </select>

        </form>
    </div>

    <div id="attendance-table" class="bg-white shadow rounded-lg overflow-x-auto max-w-full">
        <table class="divide-y divide-gray-200 text-sm">
            <thead>
                <tr>
                    <th
                        class="sticky left-0 bg-gray-50 border-r p-3 z-20 text-left font-medium text-gray-700 min-w-[200px]">
                        Сотрудник</th>
                    @foreach ($days as $day)
                        @php
                            $isWeekend = $day->isWeekend();
                            $isToday = $day->isToday();
                        @endphp
                        <th @if ($isToday) id="today-column" @endif
                            class="px-2 py-3 text-center w-10 {{ $isWeekend ? 'bg-gray-100' : 'bg-white' }} {{ $isToday ? 'bg-yellow-100' : '' }}">
                            <div class="text-xs text-gray-600">{{ $day->format('d.m') }}</div>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr class="group hover:bg-gray-50 transition-colors">
                        <td
                            class="sticky left-0 bg-white group-hover:bg-gray-50 border-r px-3 py-2 font-medium text-gray-800 whitespace-nowrap z-10 min-w-[200px]">
                            {{ $user->name_without_middle }}</td>
                        @foreach ($days as $day)
                            @php
                                $key = $user->id . '_' . $day->toDateString();
                                $att = $attendance[$key] ?? null;
                                $color = $att?->status?->color ?? '';
                                $statusCode = $att?->status?->code ?? '';
                                $comment = $att?->comment ?? '';
                                $isWeekend = $day->isWeekend();
                                $isToday = $day->isToday();
                                $cellBg = $isToday ? '#fef3c7' : ($isWeekend ? '#e5e7eb' : $color);

                                // Tooltip content: status title + escaped comment (HTML line break)
                                $tippyContent = $att?->status?->title ?? '';
                                if ($comment) {
                                    $tippyContent .= '<br>' . e($comment);
                                }
                            @endphp
                            <td class="relative cursor-pointer text-center border w-8 h-6" data-user="{{ $user->id }}"
                                data-date="{{ $day->toDateString() }}" data-status="{{ $statusCode }}"
                                data-comment="{{ $comment }}" style="background-color: {{ $cellBg }};"
                                @if (!empty($tippyContent)) data-tippy data-tippy-content="{!! $tippyContent !!}" @endif
                                title="{{ $att?->status?->title ?? '' }}{{ $comment ? ' | ' . $comment : '' }}">

                                {{-- Первая буква статуса --}}
                                {{ $att?->status?->title ? mb_strtoupper(mb_substr($att->status->title, 0, 1)) : '' }}

                                {{-- Плашка комментария --}}
                                @if ($comment)
                                    <span class="absolute top-0 right-0 w-2 h-2 bg-blue-500 rounded-sm"></span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <script>
        // Флаг: может ли текущий пользователь менять табель (только admin)
        const ATTENDANCE_CAN_EDIT = @json(auth()->user()->isAdmin());

        // Автоскролл к сегодняшнему дню
        document.addEventListener('DOMContentLoaded', () => {
            const todayColumn = document.getElementById('today-column');
            const tableContainer = document.getElementById('attendance-table');

            if (todayColumn && tableContainer) {
                // Вычисляем позицию для центрирования
                const columnLeft = todayColumn.offsetLeft;
                const columnWidth = todayColumn.offsetWidth;
                const containerWidth = tableContainer.clientWidth;

                // Скроллим так, чтобы сегодняшний день был по центру
                const scrollPosition = columnLeft - (containerWidth / 2) + (columnWidth / 2);

                tableContainer.scrollLeft = Math.max(0, scrollPosition);
            }
        });

        document.querySelectorAll('td[data-user]').forEach(td => {
            // Если пользователь не админ — показываем только информацию, без возможности редактирования
            if (!ATTENDANCE_CAN_EDIT) {
                td.addEventListener('click', (e) => {
                    const status = td.dataset.status || '—';
                    const comment = td.dataset.comment || '';
                    alert('Редактирование табеля доступно только администраторам.\nСтатус: ' + status + (
                        comment ? '\nКомментарий: ' + comment : ''));
                });
                return;
            }

            td.addEventListener('click', async (e) => {
                const userId = td.dataset.user;
                const date = td.dataset.date;
                let currentStatus = td.dataset.status;
                let comment = td.dataset.comment || '';

                // Shift+клик — редактирование комментария
                if (e.shiftKey) {
                    const newComment = prompt('Комментарий к дню:', comment);
                    if (newComment !== null) {
                        const res = await fetch('{{ route('attendance.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                user_id: userId,
                                date: date,
                                status: currentStatus || null,
                                comment: newComment
                            })
                        });

                        if (!res.ok) {
                            const err = await res.json().catch(() => null);
                            alert(err?.error ?? 'Ошибка: доступ запрещён');
                            return;
                        }

                        td.dataset.comment = newComment;

                        // Плашка комментария
                        let indicator = td.querySelector('span');
                        if (newComment) {
                            if (!indicator) {
                                indicator = document.createElement('span');
                                indicator.className =
                                    'absolute top-0 right-0 w-2 h-2 bg-blue-500 rounded-sm';
                                td.appendChild(indicator);
                            }
                        } else if (indicator) {
                            indicator.remove();
                        }

                        td.title = (td.title.split('|')[0]) + (newComment ? ' | ' + newComment : '');
                    }
                    return; // не меняем статус
                }

                // Цикл статусов по клику
                const statuses = ['work', 'remote', 'short', 'absent', ''];
                let nextIndex = (statuses.indexOf(currentStatus) + 1) % statuses.length;
                const nextStatus = statuses[nextIndex];

                if (!nextStatus) {
                    // Удаляем запись
                    const res = await fetch('{{ route('attendance.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            user_id: userId,
                            date: date,
                            status: null
                        })
                    });

                    if (!res.ok) {
                        const err = await res.json().catch(() => null);
                        alert(err?.error ?? 'Ошибка: доступ запрещён');
                        return;
                    }

                    td.dataset.status = '';
                    td.dataset.comment = '';
                    td.style.backgroundColor = '';
                    td.textContent = '';
                    td.title = '';
                    const indicator = td.querySelector('span');
                    if (indicator) indicator.remove();
                    return;
                }

                // Сохраняем статус
                const res = await fetch('{{ route('attendance.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        user_id: userId,
                        date: date,
                        status: nextStatus,
                        comment: comment
                    })
                });

                if (!res.ok) {
                    const err = await res.json().catch(() => null);
                    alert(err?.error ?? 'Ошибка: доступ запрещён');
                    return;
                }

                const data = await res.json();
                td.dataset.status = nextStatus;
                td.dataset.comment = comment;
                td.style.backgroundColor = data.color;
                td.textContent = data.title ? data.title[0].toUpperCase() : '';
                td.title = data.title + (comment ? ' | ' + comment : '');

                // Плашка комментария остаётся
                let indicator = td.querySelector('span');
                if (comment && !indicator) {
                    indicator = document.createElement('span');
                    indicator.className = 'absolute top-0 right-0 w-2 h-2 bg-blue-500 rounded-sm';
                    td.appendChild(indicator);
                }
            });
        });
    </script>
@endsection
