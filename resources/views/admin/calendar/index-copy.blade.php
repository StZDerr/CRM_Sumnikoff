@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto py-6">
        {{-- Кнопка Назад --}}
        <div class="mb-4">
            <a href="{{ url()->previous() }}"
                class="inline-flex items-center px-4 py-2 rounded-md border border-indigo-500 text-indigo-600 text-sm font-medium
          hover:bg-indigo-50 transition">
                ← Назад
            </a>
        </div>
        <h1 class="text-2xl font-semibold mb-4">Проекты по месяцам</h1>



        <div class="overflow-x-auto">
            @if (empty($project->contract_date) || empty($months))
                <div class="bg-yellow-50 border border-yellow-200 rounded p-4 text-sm text-gray-700">
                    У проекта не задана дата заключения договора — нет данных для календаря.
                </div>
            @else
                <table class="min-w-full border-collapse border border-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border border-gray-200 p-2 text-left">Проект</th>
                            @foreach ($months as $m)
                                <th class="border border-gray-200 p-2 text-center">{{ $m['label'] }}</th>
                            @endforeach
                            <th class="border border-gray-200 p-2 text-center"
                                style="width:80px; position:sticky; right:170px; background:#fff; z-index:10;">К оплате
                            </th>
                            <th class="border border-gray-200 p-2 text-center"
                                style="width:80px; position:sticky; right:80px; background:#fff; z-index:20;">Оплачено
                            </th>
                            <th class="border border-gray-200 p-2 text-center"
                                style="width:80px; position:sticky; right:0; background:#fff; z-index:30;">Разница</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr class="hover:bg-gray-50">
                            <td class="border border-gray-200 p-2 font-medium">{{ $project->title }}</td>

                            @foreach ($months as $m)
                                @php
                                    $key = $m['ym'];
                                    $paid = (float) ($paymentsByMonth[$key] ?? 0);
                                    $expected = (float) ($project->contract_amount ?? 0);
                                    $diff = $paid - $expected;
                                @endphp

                                <td class="relative border border-gray-200 p-2 text-center cursor-pointer"
                                    data-month="{{ $key }}" data-month-label="{{ $m['label'] }}" data-tippy
                                    data-tippy-content="Оплачено: {{ number_format($paid, 0, '.', ' ') }} ₽<br>Ожидалось: {{ number_format($expected, 0, '.', ' ') }} ₽">
                                    @if ($expected <= 0)
                                        —
                                    @else
                                        {{-- уголок, если есть комментарии за месяц --}}
                                        @if (!empty($commentsByMonth[$key] ?? 0))
                                            <span title="Комментарии: {{ $commentsByMonth[$key] }}"
                                                class="absolute top-0 right-0 w-3 h-3 bg-blue-500"
                                                style="clip-path: polygon(100% 0, 0 0, 100% 100%);"
                                                aria-hidden="true"></span>
                                        @endif

                                        @if ($diff > 0)
                                            <span
                                                class="text-green-600 font-semibold">+{{ number_format($diff, 0, '.', ' ') }}
                                                ₽</span>
                                        @elseif ($diff < 0)
                                            <span
                                                class="text-red-600 font-semibold">-{{ number_format(abs($diff), 0, '.', ' ') }}
                                                ₽</span>
                                        @else
                                            <span class="text-gray-900 font-semibold">{{ number_format(0, 0, '.', ' ') }}
                                                ₽</span>
                                        @endif
                                    @endif
                                </td>
                            @endforeach

                            {{-- Итоги --}}
                            <td class="border border-gray-200 p-2 text-center font-medium"
                                style="width:80px; position:sticky; right:170px; background:#fff; z-index:10;">
                                @if (!is_null($project->debt))
                                    {{ number_format($project->debt, 0, '.', ' ') }} ₽
                                    @if ($project->debt_calculated_at)
                                        <div class="text-xs text-gray-400">as of
                                            {{ \Illuminate\Support\Carbon::make($project->debt_calculated_at)->format('Y-m-d H:i') }}
                                        </div>
                                    @endif
                                @else
                                    —
                                @endif
                            </td>

                            <td class="border border-gray-200 p-2 text-center font-medium"
                                style="width:80px; position:sticky; right:80px; background:#fff; z-index:20;">
                                @if (!is_null($project->received_total))
                                    {{ number_format($project->received_total, 0, '.', ' ') }} ₽
                                    @if ($project->received_calculated_at)
                                        <div class="text-xs text-gray-400">as of
                                            {{ \Illuminate\Support\Carbon::make($project->received_calculated_at)->format('Y-m-d H:i') }}
                                        </div>
                                    @endif
                                @else
                                    —
                                @endif
                            </td>

                            @php
                                $bal =
                                    $project->balance ??
                                    (isset($project->debt, $project->received_total)
                                        ? $project->debt - $project->received_total
                                        : null);
                                $diffClass =
                                    $bal < 0 ? 'text-red-600' : ($bal > 0 ? 'text-green-600' : 'text-gray-900');
                            @endphp
                            <td class="border border-gray-200 p-2 text-center font-semibold {{ $diffClass }}"
                                style="width:160px; position:sticky; right:0; background:#fff; z-index:30;">
                                @if (!is_null($bal))
                                    {{ number_format($bal, 0, '.', ' ') }} ₽
                                    @if ($project->balance_calculated_at)
                                        <div class="text-xs text-gray-400">as of
                                            {{ \Illuminate\Support\Carbon::make($project->balance_calculated_at)->format('Y-m-d H:i') }}
                                        </div>
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            @endif
        </div>
    </div>
    <!-- Offcanvas -->
    <div id="month-offcanvas" class="fixed inset-0 z-50 hidden">
        <div id="offcanvas-overlay" class="absolute inset-0 bg-black/50"></div>

        <div id="offcanvas-panel"
            class="absolute right-0 top-0 h-full w-full sm:w-96 bg-white shadow-lg transform translate-x-full transition-transform">
            <div class="p-4 flex items-center justify-between border-b">
                <h3 class="text-lg font-medium">Добавить комментарий — <span id="offcanvas-month-label"></span></h3>
                <button id="offcanvas-close" class="text-gray-600">✕</button>
            </div>

            <form action="{{ route('projects.comments.store', $project) }}" method="POST" enctype="multipart/form-data"
                class="p-4">
                @csrf
                <input type="hidden" name="month" id="offcanvas-month-input" value="">
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
                    <button type="button" id="offcanvas-cancel" class="px-3 py-2 border rounded">Отмена</button>
                    <button type="submit" class="px-3 py-2 bg-indigo-600 text-white rounded">Добавить</button>
                </div>
            </form>
            <!-- Comments area (загружается по AJAX) -->
            <div id="offcanvas-comments" class="p-4 border-t">
                <div class="text-sm text-gray-500">Загрузка комментариев...</div>
            </div>
        </div>

    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const offcanvas = document.getElementById('month-offcanvas');
                const panel = document.getElementById('offcanvas-panel');
                const overlay = document.getElementById('offcanvas-overlay');
                const monthInput = document.getElementById('offcanvas-month-input');
                const monthLabel = document.getElementById('offcanvas-month-label');
                const cancelBtn = document.getElementById('offcanvas-cancel');
                const closeBtn = document.getElementById('offcanvas-close');

                const commentsUrlTemplate =
                    "{{ route('projects.comments.index', ['project' => $project->id, 'month' => 'MONTH_PLACEHOLDER']) }}";
                const commentsContainer = document.getElementById('offcanvas-comments');
                const form = offcanvas.querySelector('form');

                function openOffcanvas(month, label) {
                    monthInput.value = month;
                    monthLabel.textContent = label || month;
                    offcanvas.classList.remove('hidden');
                    requestAnimationFrame(() => panel.classList.remove('translate-x-full'));
                    const ta = offcanvas.querySelector('textarea[name="body"]');
                    if (ta) setTimeout(() => ta.focus(), 200);
                    loadComments(month);
                }

                function closeOffcanvas() {
                    panel.classList.add('translate-x-full');
                    setTimeout(() => offcanvas.classList.add('hidden'), 240);
                }

                // Attach click handlers to month cells
                document.querySelectorAll('td[data-month]').forEach(td => {
                    td.addEventListener('click', function() {
                        openOffcanvas(td.getAttribute('data-month'), td.getAttribute(
                            'data-month-label'));
                    });
                });

                // Close handlers
                overlay.addEventListener('click', closeOffcanvas);
                if (cancelBtn) cancelBtn.addEventListener('click', closeOffcanvas);
                if (closeBtn) closeBtn.addEventListener('click', closeOffcanvas);
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') closeOffcanvas();
                });

                // Load comments (AJAX JSON expected { html: '...' })
                async function loadComments(month) {
                    if (!commentsContainer) return;
                    commentsContainer.innerHTML = '<div class="text-sm text-gray-500">Загрузка комментариев…</div>';
                    const url = commentsUrlTemplate.replace('MONTH_PLACEHOLDER', encodeURIComponent(month));
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
                        // init features for loaded content
                        if (window.GLightbox) window.GLightbox({
                            selector: '.glightbox'
                        });
                        initAjaxDeletes();
                        initAjaxEdits();
                    } catch (err) {
                        console.error('Не удалось загрузить комментарии:', err);
                        commentsContainer.innerHTML =
                            '<div class="text-sm text-red-600">Ошибка загрузки комментариев.</div>';
                    }
                }

                // Delete (delegated)
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

                // Inline edit (delegated)
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
                                        // заменим весь элемент
                                        const wrapper = document.createElement('div');
                                        wrapper.innerHTML = json.html;
                                        item.replaceWith(wrapper.firstElementChild);
                                        // реинициализация
                                        initAjaxDeletes();
                                        initAjaxEdits();
                                        if (window.GLightbox) window.GLightbox({
                                            selector: '.glightbox'
                                        });
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
                                if (window.GLightbox) window.GLightbox({
                                    selector: '.glightbox'
                                });
                            } else {
                                window.location.reload();
                            }
                        } catch (err) {
                            console.error('Ошибка отправки комментария:', err);
                            alert('Ошибка отправки комментария.');
                        }
                    });
                }

                // Escape helper to avoid double-encoding in textarea (keeps plain text safe)
                function escapeHtml(text) {
                    const div = document.createElement('div');
                    div.textContent = text;
                    return div.innerHTML;
                }
            });
        </script>
    @endpush
@endsection
