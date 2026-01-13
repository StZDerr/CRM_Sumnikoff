@extends('layouts.app')

@section('content')
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
        <div class="mb-4">
            <a href="{{ url()->previous() }}"
                class="inline-flex items-center px-4 py-2 rounded-md border border-indigo-500 text-indigo-600 text-sm font-medium hover:bg-indigo-50 transition">
                ← Назад
            </a>
        </div>

        <h1 class="text-2xl font-semibold mb-4">Календарь — все проекты</h1>

        @if (empty($months))
            <div class="bg-yellow-50 border border-yellow-200 rounded p-4 text-sm text-gray-700">
                Нет проектов с датой договора — нечего отображать.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse border border-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border border-gray-200 p-2 text-left sticky left-0 bg-gray-100 z-10">Объект</th>
                            @foreach ($months as $m)
                                <th class="border border-gray-200 p-2 text-center min-w-[120px]">{{ $m['label'] }}</th>
                            @endforeach
                            <th
                                class="border border-gray-200 p-2 text-center sticky right-[180px] bg-gray-100 z-10 min-w-[100px]">
                                Ожидаемо</th>
                            <th
                                class="border border-gray-200 p-2 text-center sticky right-[90px] bg-gray-100 z-10 min-w-[100px]">
                                Оплачено</th>
                            <th class="border border-gray-200 p-2 text-center sticky right-0 bg-gray-100 z-10 min-w-[90px]">
                                Баланс</th>
                        </tr>
                    </thead>

                    {{-- Сводная строка по всем проектам --}}
                    <tbody>
                        <tr class="bg-gray-50 font-medium">
                            <td class="border border-gray-200 p-2 font-semibold sticky left-0 bg-gray-50 z-10">Все проекты
                            </td>

                            @foreach ($months as $m)
                                @php
                                    $key = $m['ym'];
                                    $paid = (float) ($paymentsByMonth[$key] ?? 0);
                                    $expected = (float) ($expectedByMonth[$key] ?? 0);
                                    $diff = $paid - $expected;
                                @endphp
                                <td class="border border-gray-200 p-2 text-center" data-tippy
                                    data-tippy-content="Ожидаемо: {{ number_format($expected, 0, '.', ' ') }} ₽<br>Оплачено: {{ number_format($paid, 0, '.', ' ') }} ₽">
                                    @if ($expected <= 0 && $paid == 0)
                                        <span class="text-gray-400">—</span>
                                    @else
                                        @if ($expected > 0)
                                            <div class="text-xs text-gray-500">Ожидаемо:</div>
                                            <div class="text-gray-800">{{ number_format($expected, 0, '.', ' ') }} ₽</div>
                                        @endif
                                        @if ($paid > 0)
                                            <div class="text-xs text-gray-500 mt-1">Оплачено:</div>
                                            <div class="text-gray-800">{{ number_format($paid, 0, '.', ' ') }} ₽</div>
                                        @endif
                                        <div class="mt-1 pt-1 border-t border-gray-200">
                                            @if ($diff > 0)
                                                <span
                                                    class="text-green-600 font-semibold">+{{ number_format($diff, 0, '.', ' ') }}
                                                    ₽</span>
                                            @elseif ($diff < 0)
                                                <span
                                                    class="text-red-600 font-semibold">{{ number_format($diff, 0, '.', ' ') }}
                                                    ₽</span>
                                            @else
                                                <span class="text-gray-600 font-semibold">0 ₽</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            @endforeach

                            <td
                                class="border border-gray-200 p-2 text-center font-medium sticky right-[180px] bg-gray-50 z-10">
                                <div class="text-xs text-gray-500">Ожидаемо:</div>
                                <div class="font-semibold">{{ number_format($owedTotal, 0, '.', ' ') }} ₽</div>
                            </td>
                            <td
                                class="border border-gray-200 p-2 text-center font-medium sticky right-[90px] bg-gray-50 z-10">
                                <div class="text-xs text-gray-500">Оплачено:</div>
                                <div class="font-semibold">{{ number_format($periodTotal, 0, '.', ' ') }} ₽</div>
                            </td>
                            @php $diffClass = $difference < 0 ? 'text-red-600' : ($difference > 0 ? 'text-green-600' : 'text-gray-600'); @endphp
                            <td
                                class="border border-gray-200 p-2 text-center font-semibold sticky right-0 bg-gray-50 z-10 {{ $diffClass }}">
                                {{ ($difference > 0 ? '+' : '') . number_format($difference, 0, '.', ' ') }} ₽
                            </td>
                        </tr>
                    </tbody>

                    {{-- Строки по каждому проекту --}}
                    <tbody>
                        @foreach ($projectRows as $row)
                            @php $proj = $row['project']; @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="border border-gray-200 p-2 font-medium sticky left-0 bg-white z-10">
                                    <a href="{{ route('projects.show', $proj) }}" class="text-indigo-600 hover:underline">
                                        {{ $proj->title }}
                                    </a>
                                </td>

                                @php
                                    $lastDisplayMonth = $row['lastDisplayMonth'] ?? now()->format('Y-m');
                                    $currentYm = now()->format('Y-m');
                                @endphp

                                @foreach ($months as $m)
                                    @php
                                        $ym = $m['ym'];
                                        $cell = $row['months'][$ym] ?? [
                                            'invoiced' => 0,
                                            'paid' => 0,
                                            'expected' => 0,
                                            'balance' => 0,
                                            'isActive' => false,
                                        ];
                                        $invoiced = $cell['invoiced'];
                                        $paid = $cell['paid'];
                                        $expected = $cell['expected'];
                                        $balance = $cell['balance'];
                                        $isActive = $cell['isActive'];
                                        $commentsCount = $commentsMap[$proj->id][$ym] ?? 0;

                                        // Проверяем, нужно ли показывать данные для этого месяца
                                        // Если месяц в будущем (> текущего) и > lastDisplayMonth — не показываем ожидаемые
                                        $isFutureMonth = $ym > $currentYm;
                                        $isWithinDisplayLimit = $ym <= $lastDisplayMonth;
                                        $shouldShowExpected = !$isFutureMonth || $isWithinDisplayLimit;

                                        // Если не нужно показывать — обнуляем ожидаемое (но счета и платежи показываем всегда)
                                        $displayExpected = $shouldShowExpected ? $expected : 0;
                                        $displayInvoiced = $invoiced; // Реальные счета показываем всегда
                                        $displayPaid = $paid; // Реальные платежи показываем всегда

                                        // Для будущих месяцев за пределами лимита — не показываем баланс если нет реальных данных
                                        $showCell = $shouldShowExpected || $invoiced > 0 || $paid > 0;

                                        // Цвет накопительного баланса
                                        if (
                                            !$showCell ||
                                            ($displayInvoiced == 0 && $displayExpected == 0 && $displayPaid == 0)
                                        ) {
                                            $balanceClass = 'text-gray-400';
                                            $balanceText = '—';
                                            $showBalance = false;
                                        } elseif ($balance > 0) {
                                            $balanceClass = 'text-green-600';
                                            $balanceText = '+' . number_format($balance, 0, '.', ' ') . ' ₽';
                                            $showBalance = true;
                                        } elseif ($balance == 0) {
                                            $balanceClass = 'text-gray-600';
                                            $balanceText = '0 ₽';
                                            $showBalance = true;
                                        } else {
                                            $balanceClass = 'text-red-600';
                                            $balanceText = number_format($balance, 0, '.', ' ') . ' ₽';
                                            $showBalance = true;
                                        }

                                        // Для tooltip
                                        $tooltipInvoice =
                                            $displayInvoiced > 0
                                                ? 'Счета: ' . number_format($displayInvoiced, 0, '.', ' ') . ' ₽'
                                                : ($displayExpected > 0
                                                    ? 'Ожидаемо: ' . number_format($displayExpected, 0, '.', ' ') . ' ₽'
                                                    : '');
                                    @endphp

                                    <td class="relative border border-gray-200 p-2 text-center cursor-pointer min-w-[120px]"
                                        data-project-id="{{ $proj->id }}" data-project-title="{{ e($proj->title) }}"
                                        data-month="{{ $ym }}" data-month-label="{{ $m['label'] }}" data-tippy
                                        data-tippy-content="{{ $tooltipInvoice }}<br>Оплачено: {{ number_format($displayPaid, 0, '.', ' ') }} ₽<br>Накоп. баланс: {{ $balanceText }}">

                                        {{-- Уголок, если есть комментарии --}}
                                        @if ($commentsCount > 0)
                                            <span title="Комментарии: {{ $commentsCount }}" aria-hidden="true"
                                                class="absolute top-0 right-0 w-3 h-3 bg-blue-500"
                                                style="clip-path: polygon(100% 0, 0 0, 100% 100%);"></span>
                                        @endif

                                        @if (!$showCell || ($displayInvoiced == 0 && $displayExpected == 0 && $displayPaid == 0))
                                            <span class="text-gray-400">—</span>
                                        @else
                                            {{-- Счета (реальные) --}}
                                            @if ($displayInvoiced > 0)
                                                <div class="text-xs text-gray-500">Счета:</div>
                                                <div class="font-medium text-gray-800">
                                                    {{ number_format($displayInvoiced, 0, '.', ' ') }} ₽
                                                </div>
                                            @elseif ($displayExpected > 0)
                                                {{-- Ожидаемо (из контракта) --}}
                                                <div class="text-xs text-orange-500">Ожидаемо:</div>
                                                <div class="font-medium text-orange-600">
                                                    {{ number_format($displayExpected, 0, '.', ' ') }} ₽
                                                </div>
                                            @endif

                                            {{-- Оплачено --}}
                                            @if ($displayPaid > 0)
                                                <div class="text-xs text-gray-500 mt-1">Оплачено:</div>
                                                <div class="font-medium text-gray-800">
                                                    {{ number_format($displayPaid, 0, '.', ' ') }} ₽</div>
                                            @endif

                                            {{-- Накопительный баланс --}}
                                            @if ($invoiced > 0 || $expected > 0 || $paid > 0 || $balance != 0)
                                                <div class="mt-1 pt-1 border-t border-gray-200">
                                                    <div class="text-xs text-gray-400">Баланс:</div>
                                                    <span
                                                        class="font-semibold {{ $balanceClass }}">{{ $balanceText }}</span>
                                                </div>
                                            @endif
                                        @endif
                                    </td>
                                @endforeach

                                {{-- Итого: Ожидаемо --}}
                                <td class="border border-gray-200 p-2 text-center sticky right-[180px] bg-white z-10">
                                    <div class="text-xs text-gray-500">Ожидаемо:</div>
                                    <div class="font-medium">{{ number_format($row['owed'], 0, '.', ' ') }} ₽</div>
                                </td>

                                {{-- Итого: Оплачено --}}
                                <td class="border border-gray-200 p-2 text-center sticky right-[90px] bg-white z-10">
                                    <div class="text-xs text-gray-500">Оплачено:</div>
                                    <div class="font-medium">{{ number_format($row['paid'], 0, '.', ' ') }} ₽</div>
                                </td>

                                {{-- Итого: Баланс --}}
                                @php
                                    $rowDiffClass =
                                        $row['diff'] < 0
                                            ? 'text-red-600'
                                            : ($row['diff'] > 0
                                                ? 'text-green-600'
                                                : 'text-gray-600');
                                @endphp
                                <td
                                    class="border border-gray-200 p-2 text-center font-semibold sticky right-0 bg-white z-10 {{ $rowDiffClass }}">
                                    {{ ($row['diff'] > 0 ? '+' : '') . number_format($row['diff'], 0, '.', ' ') }} ₽
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Offcanvas for all-projects -->
    <div id="ap-month-offcanvas" class="fixed inset-0 z-50 hidden">
        <div id="ap-offcanvas-overlay" class="absolute inset-0 bg-black/50"></div>

        <div id="ap-offcanvas-panel"
            class="absolute right-0 top-0 h-full w-full sm:w-96 bg-white shadow-lg transform translate-x-full transition-transform">
            <div class="p-4 flex items-center justify-between border-b">
                <h3 class="text-lg font-medium">Комментарии — <span id="ap-offcanvas-title"></span></h3>
                <button id="ap-offcanvas-close" class="text-gray-600">✕</button>
            </div>

            <form id="ap-offcanvas-form" action="" method="POST" enctype="multipart/form-data" class="p-4">
                @csrf
                <input type="hidden" name="month" id="ap-offcanvas-month-input" value="">
                <input type="hidden" name="redirect" value="{{ url()->full() }}" />

                <div class="mb-3">
                    <label class="block text-sm text-gray-600">Комментарий</label>
                    <textarea name="body" required rows="4" class="w-full border rounded p-2"></textarea>
                </div>

                <div class="mb-3">
                    <label class="block text-sm text-gray-600">Фото (опционально)</label>
                    <input type="file" name="photos[]" multiple accept="image/*" class="w-full" />
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" id="ap-offcanvas-cancel" class="px-3 py-2 border rounded">Отмена</button>
                    <button type="submit" class="px-3 py-2 bg-indigo-600 text-white rounded">Добавить</button>
                </div>
            </form>

            <!-- Comments area (загружается по AJAX) -->
            <div id="ap-offcanvas-comments" class="p-4 border-t">
                <div class="text-sm text-gray-500">Загрузка комментариев...</div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const offcanvas = document.getElementById('ap-month-offcanvas');
            const panel = document.getElementById('ap-offcanvas-panel');
            const overlay = document.getElementById('ap-offcanvas-overlay');
            const monthInput = document.getElementById('ap-offcanvas-month-input');
            const titleEl = document.getElementById('ap-offcanvas-title');
            const cancelBtn = document.getElementById('ap-offcanvas-cancel');
            const closeBtn = document.getElementById('ap-offcanvas-close');
            const form = document.getElementById('ap-offcanvas-form');
            const commentsContainer = document.getElementById('ap-offcanvas-comments');

            const commentsUrlTemplate =
                "{{ route('projects.comments.index', ['project' => 'PROJECT_ID', 'month' => 'MONTH_PLACEHOLDER']) }}";
            const storeUrlTemplate = "{{ route('projects.comments.store', 'PROJECT_ID') }}";

            function openOffcanvas(projectId, projectTitle, month) {
                monthInput.value = month;
                titleEl.textContent = projectTitle + ' — ' + month;
                offcanvas.classList.remove('hidden');
                requestAnimationFrame(() => panel.classList.remove('translate-x-full'));
                // set form action
                form.action = storeUrlTemplate.replace('PROJECT_ID', projectId);
                loadComments(projectId, month);
                const ta = offcanvas.querySelector('textarea[name="body"]');
                if (ta) setTimeout(() => ta.focus(), 200);
            }

            function closeOffcanvas() {
                panel.classList.add('translate-x-full');
                setTimeout(() => offcanvas.classList.add('hidden'), 240);
            }

            // attach click handlers to project-month cells (entire cell clickable)
            document.querySelectorAll('td[data-project-id]').forEach(td => {
                td.addEventListener('click', function(e) {
                    // avoid triggering when clicking links inside cell
                    if (e.target.closest('a')) return;
                    const pid = td.getAttribute('data-project-id');
                    const ptitle = td.getAttribute('data-project-title') || td.querySelector('a')
                        ?.textContent?.trim() || '';
                    const month = td.getAttribute('data-month');
                    openOffcanvas(pid, ptitle, month);
                });
            });

            overlay.addEventListener('click', closeOffcanvas);
            if (cancelBtn) cancelBtn.addEventListener('click', closeOffcanvas);
            if (closeBtn) closeBtn.addEventListener('click', closeOffcanvas);
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') closeOffcanvas();
            });

            async function loadComments(projectId, month) {
                if (!commentsContainer) return;
                commentsContainer.innerHTML = '<div class="text-sm text-gray-500">Загрузка комментариев…</div>';
                const url = commentsUrlTemplate.replace('PROJECT_ID', projectId).replace('MONTH_PLACEHOLDER',
                    encodeURIComponent(month));
                try {
                    const res = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    if (!res.ok) throw new Error('Network response not OK');
                    const json = await res.json();
                    commentsContainer.innerHTML = json.html ||
                        '<div class="text-sm text-gray-500">Нет комментариев.</div>';
                    initAjaxDeletes();
                    initAjaxEdits();
                } catch (err) {
                    console.error('Не удалось загрузить комментарии:', err);
                    commentsContainer.innerHTML =
                        '<div class="text-sm text-red-600">Ошибка загрузки комментариев.</div>';
                }
            }

            function initAjaxDeletes() {
                const list = document.getElementById('month-comments-list');
                if (!list) return;
                if (list.__ajaxDeletesInit) return;
                list.__ajaxDeletesInit = true;

                list.addEventListener('click', async (e) => {
                    const btn = e.target.closest('.delete-comment-form button');
                    if (!btn) return;
                    e.preventDefault();
                    const formEl = btn.closest('form');
                    const action = formEl?.getAttribute('action');
                    if (!action) return;
                    if (!confirm('Удалить комментарий?')) return;

                    try {
                        const res = await fetch(action, {
                            method: 'DELETE',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                        });
                        if (res.ok) {
                            formEl.closest('.comment-item')?.remove();
                        } else {
                            await loadComments(monthInput.value);
                        }
                    } catch (err) {
                        console.error('Ошибка удаления комментария:', err);
                    }
                });
            }

            function initAjaxEdits() {
                const list = document.getElementById('month-comments-list');
                if (!list) return;
                if (list.__ajaxEditsInit) return;
                list.__ajaxEditsInit = true;

                list.addEventListener('click', function(e) {
                    const btn = e.target.closest('.edit-comment-btn');
                    if (!btn) return;

                    const item = btn.closest('.comment-item');
                    const bodyEl = item.querySelector('.comment-body');
                    const updateUrl = btn.dataset.updateUrl;
                    if (!bodyEl || !updateUrl) return;
                    if (item.querySelector('.edit-comment-form')) return;

                    const originalText = bodyEl.innerText.trim();
                    bodyEl.style.display = 'none';

                    const formEl = document.createElement('form');
                    formEl.className = 'edit-comment-form mt-3';
                    formEl.innerHTML = `
                            <textarea name="body" rows="3" class="w-full border rounded p-2">${escapeHtml(originalText)}</textarea>
                            <div class="mt-2 flex gap-2 justify-end">
                                <button type="button" class="px-3 py-1 border rounded cancel-edit-btn">Отмена</button>
                                <button type="submit" class="px-3 py-1 bg-indigo-600 text-white rounded save-edit-btn">Сохранить</button>
                            </div>
                        `;
                    bodyEl.parentNode.insertBefore(formEl, bodyEl.nextSibling);

                    formEl.querySelector('.cancel-edit-btn').addEventListener('click', function() {
                        formEl.remove();
                        bodyEl.style.display = '';
                    });

                    formEl.addEventListener('submit', async function(ev) {
                        ev.preventDefault();
                        const ta = formEl.querySelector('textarea[name="body"]');
                        const body = ta.value.trim();
                        if (!body) {
                            alert('Комментарий не может быть пустым.');
                            return;
                        }

                        try {
                            const res = await fetch(updateUrl, {
                                method: 'PATCH',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').content,
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                                },
                                body: new URLSearchParams({
                                    body
                                })
                            });

                            if (res.ok) {
                                const json = await res.json();
                                if (json.html) {
                                    const wrapper = document.createElement('div');
                                    wrapper.innerHTML = json.html;
                                    item.replaceWith(wrapper.firstElementChild);
                                    initAjaxDeletes();
                                    initAjaxEdits();
                                } else {
                                    bodyEl.innerText = body;
                                    formEl.remove();
                                    bodyEl.style.display = '';
                                }
                            } else if (res.status === 422) {
                                const data = await res.json();
                                alert(Object.values(data.errors).flat().join('\n'));
                            } else {
                                alert('Ошибка при обновлении комментария.');
                            }
                        } catch (err) {
                            console.error('Ошибка при обновлении комментария:', err);
                            alert('Ошибка при обновлении комментария.');
                        }
                    });
                });
            }

            // Handle create form submit (AJAX)
            if (form) {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    const fd = new FormData(form);
                    const action = form.action;
                    try {
                        const res = await fetch(action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: fd
                        });
                        if (!res.ok) {
                            if (res.status === 422) {
                                const err = await res.json().catch(() => null);
                                alert(Object.values(err?.errors || {}).flat().join('\n') ||
                                    'Ошибка при отправке.');
                                return;
                            }
                            alert('Ошибка при отправке комментария.');
                            return;
                        }
                        const json = await res.json();
                        if (json.html) {
                            const list = document.getElementById('month-comments-list');
                            if (list) {
                                list.insertAdjacentHTML('afterbegin', json.html);
                            } else {
                                commentsContainer.innerHTML = json.html;
                            }
                            form.reset();
                            initAjaxDeletes();
                            initAjaxEdits();
                        } else {
                            window.location.reload();
                        }
                    } catch (err) {
                        console.error('Ошибка отправки комментария:', err);
                        alert('Ошибка отправки комментария.');
                    }
                });
            }

            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
        });
    </script>
@endpush
