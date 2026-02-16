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

                                // Tooltip content: только комментарий (HTML line break)
                                $tippyContent = $comment ? nl2br(e($comment)) : '';
                            @endphp
                            <td class="relative cursor-pointer text-center border w-8 h-6" data-user="{{ $user->id }}"
                                data-date="{{ $day->toDateString() }}" data-status="{{ $statusCode }}"
                                data-comment="{{ $comment }}" style="background-color: {{ $cellBg }};"
                                @if (!empty($tippyContent)) data-tippy data-tippy-content="{!! $tippyContent !!}" @endif
                                title="{{ $comment }}">

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

    {{-- Новый табель: График посещаемости (показывает отработанное время HH:MM) --}}
    <div class="mt-6 mb-4 text-sm font-semibold text-gray-700">График посещаемости</div>

    <div id="work-hours-table" class="bg-white shadow rounded-lg overflow-x-auto max-w-full">
        <table class="divide-y divide-gray-200 text-sm">
            <thead>
                <tr>
                    <th
                        class="sticky left-0 bg-gray-50 border-r p-3 z-20 text-left font-medium text-gray-700 min-w-[200px]">
                        Сотрудник</th>
                    @foreach ($days as $day)
                        @php $isToday = $day->isToday(); @endphp
                        <th @if ($isToday) id="today-column-hours" @endif
                            class="px-2 py-3 text-center w-20 {{ $isToday ? 'bg-yellow-100' : 'bg-white' }}">
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
                            <div class="flex items-center gap-2">
                                <span>{{ $user->name_without_middle }}</span>

                                @if (($workTodayStatuses[$user->id] ?? '') === 'working')
                                    <span
                                        class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">Работают</span>
                                @elseif (($workTodayStatuses[$user->id] ?? '') === 'finished')
                                    <span
                                        class="rounded-full bg-gray-200 px-2 py-0.5 text-[10px] font-semibold text-gray-700">Закончил
                                        работать</span>
                                @endif
                            </div>
                        </td>

                        @foreach ($days as $day)
                            @php
                                $key = $user->id . '_' . $day->toDateString();
                                $wd = $workDays[$key] ?? null;
                                $minutes = $wd?->total_work_minutes ?? 0;
                                $display =
                                    $minutes > 0 ? sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60) : '';
                                $isClickable = $minutes > 0;
                                $isToday = $day->isToday();
                            @endphp

                            <td class="text-center border w-20 h-6 text-xs {{ $isClickable ? 'cursor-pointer hover:bg-indigo-50' : 'cursor-default' }}"
                                @if ($isClickable) data-workday-cell="1"
                                    data-user-id="{{ $user->id }}"
                                    data-user-name="{{ $user->name_without_middle }}"
                                    data-date="{{ $day->toDateString() }}" @endif
                                style="background-color: {{ $isToday ? '#fef3c7' : 'transparent' }};">
                                {{ $display }}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div id="workday-detail-modal" class="fixed inset-0 z-[1000] hidden">
        <div class="absolute inset-0 bg-black/60" data-close-workday-modal="1"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="w-full max-w-2xl rounded-xl bg-white p-5 text-gray-900 shadow-xl max-h-[90vh] overflow-y-auto">
                <div class="mb-4 flex items-start justify-between gap-4">
                    <div>
                        <div class="text-lg font-semibold">Детали рабочего дня</div>
                        <div id="wd-modal-subtitle" class="mt-1 text-sm text-gray-600"></div>
                    </div>
                    <button type="button" data-close-workday-modal="1"
                        class="rounded border border-gray-300 px-3 py-1.5 text-sm hover:bg-gray-50">Закрыть</button>
                </div>

                <div id="wd-modal-empty" class="hidden rounded-lg border border-dashed p-4 text-sm text-gray-600">
                    Нет данных по рабочему времени за выбранный день.
                </div>

                <div id="wd-modal-content" class="space-y-4">
                    <div class="grid gap-3 md:grid-cols-3">
                        <div class="rounded-lg border p-3">
                            <div class="text-xs text-gray-500">Начал работать</div>
                            <div id="wd-modal-start" class="mt-1 text-sm font-semibold">—</div>
                        </div>
                        <div class="rounded-lg border p-3">
                            <div class="text-xs text-gray-500">Закончил работать</div>
                            <div id="wd-modal-end" class="mt-1 text-sm font-semibold">—</div>
                        </div>
                        <div class="rounded-lg border p-3">
                            <div class="text-xs text-gray-500">Отработано</div>
                            <div id="wd-modal-total" class="mt-1 text-sm font-semibold">—</div>
                        </div>
                    </div>

                    <div class="rounded-lg border p-4">
                        <div class="mb-2 text-sm font-semibold">Паузы</div>
                        <div id="wd-modal-breaks" class="space-y-2 text-sm"></div>
                    </div>

                    <div class="rounded-lg border p-4">
                        <div class="mb-2 text-sm font-semibold">Что сделал за день</div>
                        <div id="wd-modal-report" class="text-sm text-gray-700 whitespace-pre-wrap">—</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const WORK_DAY_DETAILS = @json($workDayDetails);

        function formatMinutesToHours(minutes) {
            const total = Number(minutes || 0);
            const hours = Math.floor(total / 60);
            const mins = total % 60;
            return `${String(hours).padStart(2, '0')}:${String(mins).padStart(2, '0')}`;
        }

        function openWorkDayModal(userId, userName, date) {
            const modal = document.getElementById('workday-detail-modal');
            const subtitle = document.getElementById('wd-modal-subtitle');
            const empty = document.getElementById('wd-modal-empty');
            const content = document.getElementById('wd-modal-content');
            const start = document.getElementById('wd-modal-start');
            const end = document.getElementById('wd-modal-end');
            const total = document.getElementById('wd-modal-total');
            const breaks = document.getElementById('wd-modal-breaks');
            const report = document.getElementById('wd-modal-report');

            subtitle.textContent = `${userName} · ${date}`;

            const key = `${userId}_${date}`;
            const details = WORK_DAY_DETAILS[key] || null;

            if (!details) {
                empty.classList.remove('hidden');
                content.classList.add('hidden');
            } else {
                empty.classList.add('hidden');
                content.classList.remove('hidden');

                start.textContent = details.started_at || '—';
                end.textContent = details.ended_at || '—';
                total.textContent = formatMinutesToHours(details.total_work_minutes || 0);
                report.textContent = details.report || '—';

                breaks.innerHTML = '';
                if (!Array.isArray(details.breaks) || !details.breaks.length) {
                    breaks.innerHTML = '<div class="text-gray-500">Паузы отсутствуют.</div>';
                } else {
                    details.breaks.forEach((item, index) => {
                        const row = document.createElement('div');
                        row.className = 'rounded border p-2';
                        row.innerHTML = `
                            <div><span class="font-medium">Пауза ${index + 1}:</span> ${item.started_at || '—'} — ${item.ended_at || '—'}</div>
                            <div class="text-gray-600">Комментарий: ${item.comment || '—'}</div>
                        `;
                        breaks.appendChild(row);
                    });
                }
            }

            modal.classList.remove('hidden');
        }

        function closeWorkDayModal() {
            const modal = document.getElementById('workday-detail-modal');
            modal.classList.add('hidden');
        }

        document.querySelectorAll('[data-workday-cell="1"]').forEach((cell) => {
            cell.addEventListener('click', () => {
                openWorkDayModal(cell.dataset.userId, cell.dataset.userName, cell.dataset.date);
            });
        });

        document.querySelectorAll('[data-close-workday-modal="1"]').forEach((btn) => {
            btn.addEventListener('click', closeWorkDayModal);
        });

        // Центрируем оба табеля по сегодняшней колонке
        document.addEventListener('DOMContentLoaded', () => {
            const todayColumn = document.getElementById('today-column');
            const tableContainer = document.getElementById('attendance-table');
            if (todayColumn && tableContainer) {
                const columnLeft = todayColumn.offsetLeft;
                const columnWidth = todayColumn.offsetWidth;
                const containerWidth = tableContainer.clientWidth;
                const scrollPosition = columnLeft - (containerWidth / 2) + (columnWidth / 2);
                tableContainer.scrollLeft = Math.max(0, scrollPosition);
            }

            const todayColumnHours = document.getElementById('today-column-hours');
            const hoursContainer = document.getElementById('work-hours-table');
            if (todayColumnHours && hoursContainer) {
                const columnLeft = todayColumnHours.offsetLeft;
                const columnWidth = todayColumnHours.offsetWidth;
                const containerWidth = hoursContainer.clientWidth;
                const scrollPosition = columnLeft - (containerWidth / 2) + (columnWidth / 2);
                hoursContainer.scrollLeft = Math.max(0, scrollPosition);
            }
        });
    </script>

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

                        td.title = newComment || '';
                    }
                    return; // не меняем статус
                }

                // Цикл статусов по клику
                const statuses = ['work', 'remote', 'short', 'absent', ''];
                let nextIndex = (statuses.indexOf(currentStatus) + 1) % statuses.length;
                const nextStatus = statuses[nextIndex];

                if (!nextStatus) {
                    // Переходим в пустой статус — сохраним/не потеряем комментарий
                    const res = await fetch('{{ route('attendance.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            user_id: userId,
                            date: date,
                            status: null,
                            comment: comment || null
                        })
                    });

                    if (!res.ok) {
                        const err = await res.json().catch(() => null);
                        alert(err?.error ?? 'Ошибка: доступ запрещён');
                        return;
                    }

                    const data = await res.json().catch(() => ({}));

                    td.dataset.status = '';
                    td.dataset.comment = data.comment ?? '';
                    td.style.backgroundColor = '';
                    td.textContent = '';

                    // Показываем в подсказке только комментарий, если он есть
                    td.title = data.comment ? data.comment : '';

                    // Плашка комментария
                    let indicator = td.querySelector('span');
                    if (data.comment) {
                        if (!indicator) {
                            indicator = document.createElement('span');
                            indicator.className =
                                'absolute top-0 right-0 w-2 h-2 bg-blue-500 rounded-sm';
                            td.appendChild(indicator);
                        }
                    } else if (indicator) {
                        indicator.remove();
                    }

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
                td.title = comment || '';

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
