@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-gray-100 p-6">
        <div class="flex items-center justify-between mb-6 gap-4 flex-wrap">
            <h1 class="text-2xl font-bold">Канбан доска</h1>
            <div class="flex items-center gap-2">
                <button id="open-quick-lead-modal"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    Быстрый лид
                </button>
                <input id="new-board-name" type="text" placeholder="Название доски" class="px-3 py-2 border rounded" />
                <button id="add-board" style="background-color:#10B981; color:#fff"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-500 text-white rounded shadow hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Добавить
                </button>
            </div>
        </div>

        <div id="boards" class="flex gap-6 overflow-x-auto pb-4">
            @forelse($columns as $column)
                <div class="board bg-white rounded-2xl shadow p-4 flex flex-col w-80" data-column-id="{{ $column->id }}">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-semibold text-gray-700">{{ $column->name }}</h2>
                        <span
                            class="text-xs bg-blue-100 text-blue-600 px-2 py-1 rounded-full board-count">{{ $column->phoneLeads->count() }}</span>
                    </div>

                    <div class="cards flex flex-col gap-4 min-h-[110px]" data-column-id="{{ $column->id }}">
                        @foreach ($column->phoneLeads as $lead)
                            @php
                                $lastCall = $lead->latestCall;
                                $d = strtoupper(trim((string) optional($lastCall)->direction));
                                $dirLabel = $d === 'INBOUND' ? 'Вх' : ($d === 'OUTBOUND' ? 'Исх' : '—');
                            @endphp
                            <div class="card bg-gray-50 border rounded-xl p-4 hover:shadow transition cursor-move"
                                draggable="true" data-id="{{ $lead->id }}">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-xs font-semibold text-gray-600">{{ $dirLabel }}</span>
                                    <span
                                        class="text-xs text-gray-400">{{ optional($lead->last_call_at)->format('d.m.Y H:i') ?? '—' }}</span>
                                </div>

                                <p class="text-sm text-gray-700 mt-2">Телефон: <span
                                        class="font-semibold">{{ $lead->phone }}</span></p>

                                <div class="mt-2 flex items-center justify-between text-xs text-gray-500">
                                    <span>Звонков: {{ (int) $lead->calls_count }}</span>
                                    <span>{{ $lead->name ?: 'Без имени' }}</span>
                                </div>
                                @if ($lead->topic || $lead->region)
                                    <div class="mt-2 text-xs text-gray-500">
                                        {{ $lead->topic?->name ?? 'Без запроса' }}{{ $lead->region ? ' • ' . $lead->region : '' }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="text-gray-500">Колонки не созданы</div>
            @endforelse
        </div>
    </div>

    <div id="quick-lead-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
        <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold">Быстрое создание лида</h2>
                <button type="button" data-close-modal="quick-lead-modal"
                    class="text-gray-500 hover:text-gray-800">✕</button>
            </div>

            <form id="quick-lead-form" class="space-y-4">
                <div>
                    <label class="text-sm text-gray-700">Имя</label>
                    <input name="name" type="text" class="mt-1 w-full rounded border px-3 py-2" />
                </div>

                <div>
                    <label class="text-sm text-gray-700">Телефон</label>
                    <input id="quick-lead-phone" name="phone" type="text" required inputmode="tel"
                        placeholder="+7 (___) ___-__-__" class="mt-1 w-full rounded border px-3 py-2" />
                </div>

                <div>
                    <label class="text-sm text-gray-700">Регион (+часов от МСК)</label>
                    <select name="region" class="mt-1 w-full rounded border px-3 py-2">
                        <option value="Europe/Kaliningrad">Калининград (UTC+2)</option>
                        <option value="Europe/Moscow" selected>Москва (UTC+3)</option>
                        <option value="Europe/Samara">Самара (UTC+4)</option>
                        <option value="Asia/Yekaterinburg">Екатеринбург (UTC+5)</option>
                        <option value="Asia/Omsk">Омск (UTC+6)</option>
                        <option value="Asia/Krasnoyarsk">Красноярск (UTC+7)</option>
                        <option value="Asia/Irkutsk">Иркутск (UTC+8)</option>
                        <option value="Asia/Yakutsk">Якутск (UTC+9)</option>
                        <option value="Asia/Vladivostok">Владивосток (UTC+10)</option>
                        <option value="Asia/Srednekolymsk">Среднеколымск (UTC+11)</option>
                        <option value="Asia/Kamchatka">Петропавловск-Камчатский (UTC+12)</option>
                    </select>
                </div>

                <div>
                    <div class="mb-1 flex items-center justify-between gap-2">
                        <label class="text-sm text-gray-700">Запрос (тема обращения)</label>
                        <button type="button" id="open-topic-modal" class="text-xs text-indigo-600 hover:text-indigo-800">+
                            Добавить тему</button>
                    </div>
                    <select name="lead_topic_id" id="quick-lead-topic" class="w-full rounded border px-3 py-2">
                        <option value="">— Не выбрано —</option>
                    </select>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" data-close-modal="quick-lead-modal"
                        class="rounded border px-4 py-2">Отмена</button>
                    <button type="submit"
                        class="rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">Создать</button>
                </div>
            </form>
        </div>
    </div>

    <div id="lead-details-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
        <div class="w-full max-w-3xl rounded-xl bg-white p-6 shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold">Карточка лида</h2>
                <button type="button" data-close-modal="lead-details-modal"
                    class="text-gray-500 hover:text-gray-800">✕</button>
            </div>

            <form id="lead-details-form" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="hidden" name="id" />

                <div>
                    <label class="text-sm text-gray-700">Имя</label>
                    <input name="name" type="text" class="mt-1 w-full rounded border px-3 py-2" />
                </div>

                <div>
                    <label class="text-sm text-gray-700">Телефон</label>
                    <input name="phone" type="text" required class="mt-1 w-full rounded border px-3 py-2"
                        id="lead-details-phone" />
                </div>

                <div>
                    <label class="text-sm text-gray-700">Регион (+часов от МСК)</label>
                    <select name="region" class="mt-1 w-full rounded border px-3 py-2">
                        <option value="Europe/Kaliningrad">Калининград (UTC+2)</option>
                        <option value="Europe/Moscow" selected>Москва (UTC+3)</option>
                        <option value="Europe/Samara">Самара (UTC+4)</option>
                        <option value="Asia/Yekaterinburg">Екатеринбург (UTC+5)</option>
                        <option value="Asia/Omsk">Омск (UTC+6)</option>
                        <option value="Asia/Krasnoyarsk">Красноярск (UTC+7)</option>
                        <option value="Asia/Irkutsk">Иркутск (UTC+8)</option>
                        <option value="Asia/Yakutsk">Якутск (UTC+9)</option>
                        <option value="Asia/Vladivostok">Владивосток (UTC+10)</option>
                        <option value="Asia/Srednekolymsk">Среднеколымск (UTC+11)</option>
                        <option value="Asia/Kamchatka">Петропавловск-Камчатский (UTC+12)</option>
                    </select>
                </div>

                <div>
                    <div class="mb-1 flex items-center justify-between gap-2">
                        <label class="text-sm text-gray-700">Запрос</label>
                        <button type="button" id="open-topic-modal-details"
                            class="text-xs text-indigo-600 hover:text-indigo-800">+ Добавить тему</button>
                    </div>
                    <select name="lead_topic_id" id="details-topic" class="w-full rounded border px-3 py-2">
                        <option value="">— Не выбрано —</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="text-sm text-gray-700">Комментарий</label>
                    <textarea name="comment" rows="3" class="mt-1 w-full rounded border px-3 py-2"></textarea>
                </div>

                <div>
                    <label class="text-sm text-gray-700">Дедлайн</label>
                    <input name="deadline_at" type="datetime-local" class="mt-1 w-full rounded border px-3 py-2" />
                </div>

                <div>
                    <label class="text-sm text-gray-700">Сумма</label>
                    <input name="amount" type="number" step="0.01" min="0"
                        class="mt-1 w-full rounded border px-3 py-2" />
                </div>

                <div>
                    <label class="text-sm text-gray-700">Дата начала сделки</label>
                    <input name="deal_start_date" type="date" class="mt-1 w-full rounded border px-3 py-2" />
                </div>

                <div>
                    <div class="mb-1 flex items-center justify-between gap-2">
                        <label class="text-sm text-gray-700">Источник сделки</label>
                        <button type="button" id="open-source-modal"
                            class="text-xs text-indigo-600 hover:text-indigo-800">+ Добавить источник</button>
                    </div>
                    <select name="campaign_source_id" id="details-source" class="w-full rounded border px-3 py-2">
                        <option value="">— Не выбрано —</option>
                    </select>
                </div>

                <div>
                    <label class="text-sm text-gray-700">Ответственный сотрудник</label>
                    <select name="responsible_user_id" id="details-user" class="w-full rounded border px-3 py-2">
                        <option value="">— Не выбрано —</option>
                    </select>
                </div>

                <div class="md:col-span-2 flex items-center justify-end gap-2 pt-2">
                    <button type="button" data-close-modal="lead-details-modal"
                        class="rounded border px-4 py-2">Отмена</button>
                    <button type="submit"
                        class="rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">Сохранить</button>
                </div>
            </form>
        </div>
    </div>

    <div id="topic-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
        <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold">Новая тема обращения</h2>
                <button type="button" data-close-modal="topic-modal"
                    class="text-gray-500 hover:text-gray-800">✕</button>
            </div>
            <form id="topic-form" class="space-y-4">
                <div>
                    <label class="text-sm text-gray-700">Название темы</label>
                    <input name="name" type="text" required class="mt-1 w-full rounded border px-3 py-2" />
                </div>
                <div class="flex items-center justify-end gap-2">
                    <button type="button" data-close-modal="topic-modal"
                        class="rounded border px-4 py-2">Отмена</button>
                    <button type="submit"
                        class="rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">Добавить</button>
                </div>
            </form>
        </div>
    </div>

    <div id="source-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
        <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold">Новый источник сделки</h2>
                <button type="button" data-close-modal="source-modal"
                    class="text-gray-500 hover:text-gray-800">✕</button>
            </div>
            <form id="source-form" class="space-y-4">
                <div>
                    <label class="text-sm text-gray-700">Название источника</label>
                    <input name="name" type="text" required class="mt-1 w-full rounded border px-3 py-2" />
                </div>
                <div class="text-xs text-gray-500">Созданный источник автоматически получит флаг «для лидов».</div>
                <div class="flex items-center justify-end gap-2">
                    <button type="button" data-close-modal="source-modal"
                        class="rounded border px-4 py-2">Отмена</button>
                    <button type="submit"
                        class="rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">Добавить</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const boardsContainer = document.getElementById('boards');
            const addBtn = document.getElementById('add-board');
            const newNameInput = document.getElementById('new-board-name');
            const quickLeadModal = document.getElementById('quick-lead-modal');
            const leadDetailsModal = document.getElementById('lead-details-modal');
            const topicModal = document.getElementById('topic-modal');
            const sourceModal = document.getElementById('source-modal');
            const quickLeadForm = document.getElementById('quick-lead-form');
            const leadDetailsForm = document.getElementById('lead-details-form');
            const topicForm = document.getElementById('topic-form');
            const sourceForm = document.getElementById('source-form');
            const quickLeadPhoneInput = document.getElementById('quick-lead-phone');
            const leadDetailsPhoneInput = document.getElementById('lead-details-phone');

            let draggedCard = null;
            let dragInProgress = false;

            const topics = @json($topics->map(fn($item) => ['id' => $item->id, 'name' => $item->name])->values());
            const campaignSources = @json($campaignSources->map(fn($item) => ['id' => $item->id, 'name' => $item->name])->values());
            const responsibleUsers = @json($responsibleUsers->map(fn($item) => ['id' => $item->id, 'name' => $item->name])->values());

            function escapeHtml(str) {
                return String(str ?? '').replace(/[&<>'"]/g, (char) => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    "'": '&#39;',
                    '"': '&quot;'
                } [char]));
            }

            function openModal(modal) {
                modal?.classList.remove('hidden');
                modal?.classList.add('flex');
            }

            function closeModal(modal) {
                modal?.classList.add('hidden');
                modal?.classList.remove('flex');
            }

            function fillSelect(selectEl, items, placeholder = '— Не выбрано —', selected = '') {
                if (!selectEl) return;
                selectEl.innerHTML = `<option value="">${placeholder}</option>`;
                items.forEach(item => {
                    const option = document.createElement('option');
                    option.value = String(item.id);
                    option.textContent = item.name;
                    if (selected && String(selected) === String(item.id)) {
                        option.selected = true;
                    }
                    selectEl.appendChild(option);
                });
            }

            function renderLeadCard(lead) {
                const name = lead.name || 'Без имени';
                const region = lead.region || '';
                const topic = lead.topic || '';

                return `
                    <div class="card bg-gray-50 border rounded-xl p-4 hover:shadow transition cursor-move" draggable="true" data-id="${lead.id}">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-semibold text-gray-600">—</span>
                            <span class="text-xs text-gray-400">—</span>
                        </div>
                        <p class="text-sm text-gray-700 mt-2">Телефон: <span class="font-semibold">${escapeHtml(lead.phone)}</span></p>
                        <div class="mt-2 flex items-center justify-between text-xs text-gray-500">
                            <span>Звонков: 0</span>
                            <span>${escapeHtml(name)}</span>
                        </div>
                        ${(topic || region) ? `<div class="mt-2 text-xs text-gray-500">${escapeHtml(topic || 'Без запроса')}${region ? ' • ' + escapeHtml(region) : ''}</div>` : ''}
                    </div>
                `;
            }

            function normalizePhone(rawValue) {
                const source = String(rawValue || '');
                let digits = source.replace(/\D/g, '');

                if (source.trim().startsWith('+7') && digits.startsWith('7')) {
                    digits = digits.slice(1);
                }

                if (digits.length === 11 && (digits.startsWith('7') || digits.startsWith('8'))) {
                    digits = digits.slice(1);
                }

                return digits.slice(0, 10);
            }

            function formatPhoneMask(rawValue) {
                const digits = normalizePhone(rawValue);
                return formatPhoneMaskFromDigits(digits);
            }

            function formatPhoneMaskFromDigits(rawDigits) {
                const digits = String(rawDigits || '').replace(/\D/g, '').slice(0, 10);
                if (!digits.length) return '';

                let result = '+7';
                if (digits.length > 0) {
                    result += ` (${digits.slice(0, 3)}`;
                }
                if (digits.length >= 3) {
                    result += ')';
                }
                if (digits.length > 3) {
                    result += ` ${digits.slice(3, 6)}`;
                }
                if (digits.length > 6) {
                    result += `-${digits.slice(6, 8)}`;
                }
                if (digits.length > 8) {
                    result += `-${digits.slice(8, 10)}`;
                }

                return result;
            }

            function updateCounts() {
                document.querySelectorAll('.board').forEach(board => {
                    const cnt = board.querySelectorAll('.card').length;
                    const badge = board.querySelector('.board-count');
                    if (badge) badge.textContent = cnt;
                });
            }

            function collectColumnsPayload() {
                return Array.from(document.querySelectorAll('.board')).map(board => {
                    const columnId = Number(board.dataset.columnId);
                    const leadIds = Array.from(board.querySelectorAll('.card')).map(card => Number(card
                        .dataset.id));
                    return {
                        id: columnId,
                        lead_ids: leadIds
                    };
                });
            }

            async function persistOrder() {
                const payload = {
                    columns: collectColumnsPayload()
                };
                try {
                    const resp = await fetch('{{ route('lead.reorder') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(payload),
                    });
                    if (!resp.ok) {
                        const txt = await resp.text();
                        console.error('reorder failed', resp.status, txt);
                    }
                } catch (e) {
                    console.error(e);
                }
            }

            boardsContainer.addEventListener('dragstart', e => {
                const card = e.target.closest('.card');
                if (!card) return;
                draggedCard = card;
                dragInProgress = true;
                e.dataTransfer.effectAllowed = 'move';
            });

            boardsContainer.addEventListener('dragend', () => {
                setTimeout(() => {
                    dragInProgress = false;
                }, 50);
            });

            // register listeners on both cards container and entire board so drops on empty space still work
            function setupBoardListeners(board) {
                const cardsEl = board.querySelector('.cards');
                cardsEl.addEventListener('dragover', e => {
                    e.preventDefault();
                });

                cardsEl.addEventListener('drop', handleDrop);

                board.addEventListener('dragover', e => {
                    // allow drop anywhere on board
                    e.preventDefault();
                });

                board.addEventListener('drop', handleDrop);
            }

            function handleDrop(e) {
                e.preventDefault();
                if (!draggedCard) return;

                // find closest cards container inside this board
                const board = e.target.closest('.board');
                const cardsEl = board?.querySelector('.cards');
                if (!cardsEl) return;

                const targetCard = e.target.closest('.card');
                if (targetCard && targetCard.parentElement === cardsEl) {
                    cardsEl.insertBefore(draggedCard, targetCard);
                } else {
                    cardsEl.appendChild(draggedCard);
                }

                draggedCard = null;
                updateCounts();
                persistOrder();
            }

            // initial setup
            boardsContainer.querySelectorAll('.board').forEach(setupBoardListeners);

            addBtn?.addEventListener('click', async () => {
                const name = (newNameInput?.value || '').trim();
                if (!name) return;

                try {
                    const response = await fetch('{{ route('lead.columns.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            name
                        }),
                    });

                    const data = await response.json();
                    if (!data?.ok || !data?.column) return;

                    const board = document.createElement('div');
                    board.className = 'board bg-white rounded-2xl shadow p-4 flex flex-col w-80';
                    board.dataset.columnId = data.column.id;
                    board.innerHTML = `
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="font-semibold text-gray-700"></h2>
                            <span class="text-xs bg-blue-100 text-blue-600 px-2 py-1 rounded-full board-count">0</span>
                        </div>
                        <div class="cards flex flex-col gap-4 min-h-[110px]" data-column-id="${data.column.id}"></div>
                    `;
                    board.querySelector('h2').textContent = data.column.name;

                    const cardsEl = board.querySelector('.cards');
                    cardsEl.addEventListener('dragover', e => e.preventDefault());
                    cardsEl.addEventListener('drop', async e => {
                        e.preventDefault();
                        if (!draggedCard) return;
                        cardsEl.appendChild(draggedCard);
                        draggedCard = null;
                        updateCounts();
                        await persistOrder();
                    });

                    boardsContainer.appendChild(board);
                    setupBoardListeners(board);
                    newNameInput.value = '';
                } catch (e) {
                    console.error(e);
                }
            });

            fillSelect(document.getElementById('quick-lead-topic'), topics);
            fillSelect(document.getElementById('details-topic'), topics);
            fillSelect(document.getElementById('details-source'), campaignSources);
            fillSelect(document.getElementById('details-user'), responsibleUsers);

            document.getElementById('open-quick-lead-modal')?.addEventListener('click', () => {
                quickLeadForm.reset();
                fillSelect(document.getElementById('quick-lead-topic'), topics);
                openModal(quickLeadModal);
            });

            quickLeadPhoneInput?.addEventListener('input', () => {
                quickLeadPhoneInput.value = formatPhoneMask(quickLeadPhoneInput.value);
            });

            quickLeadPhoneInput?.addEventListener('keydown', (e) => {
                if (e.key !== 'Backspace' && e.key !== 'Delete') return;

                const input = quickLeadPhoneInput;
                const value = input.value;
                const start = input.selectionStart ?? 0;
                const end = input.selectionEnd ?? 0;

                if (start !== end) return;

                const isBackspace = e.key === 'Backspace';
                const nearChar = isBackspace ? value[start - 1] : value[start];
                if (!nearChar || /\d/.test(nearChar)) return;

                const digits = normalizePhone(value);
                const digitsBeforeCursor = normalizePhone(value.slice(0, start)).length;
                const removeIndex = isBackspace ? digitsBeforeCursor - 1 : digitsBeforeCursor;

                if (removeIndex < 0 || removeIndex >= digits.length) {
                    e.preventDefault();
                    return;
                }

                const nextDigits = digits.slice(0, removeIndex) + digits.slice(removeIndex + 1);
                input.value = formatPhoneMaskFromDigits(nextDigits);
                e.preventDefault();
            });

            leadDetailsPhoneInput?.addEventListener('input', () => {
                leadDetailsPhoneInput.value = formatPhoneMask(leadDetailsPhoneInput.value);
            });

            leadDetailsPhoneInput?.addEventListener('keydown', (e) => {
                if (e.key !== 'Backspace' && e.key !== 'Delete') return;

                const input = leadDetailsPhoneInput;
                const value = input.value;
                const start = input.selectionStart ?? 0;
                const end = input.selectionEnd ?? 0;

                if (start !== end) return;

                const isBackspace = e.key === 'Backspace';
                const nearChar = isBackspace ? value[start - 1] : value[start];
                if (!nearChar || /\d/.test(nearChar)) return;

                const digits = normalizePhone(value);
                const digitsBeforeCursor = normalizePhone(value.slice(0, start)).length;
                const removeIndex = isBackspace ? digitsBeforeCursor - 1 : digitsBeforeCursor;

                if (removeIndex < 0 || removeIndex >= digits.length) {
                    e.preventDefault();
                    return;
                }

                const nextDigits = digits.slice(0, removeIndex) + digits.slice(removeIndex + 1);
                input.value = formatPhoneMaskFromDigits(nextDigits);
                e.preventDefault();
            });

            document.querySelectorAll('[data-close-modal]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const modalId = btn.getAttribute('data-close-modal');
                    if (!modalId) return;
                    closeModal(document.getElementById(modalId));
                });
            });

            [quickLeadModal, leadDetailsModal, topicModal, sourceModal].forEach(modal => {
                modal?.addEventListener('click', (e) => {
                    if (e.target === modal) closeModal(modal);
                });
            });

            quickLeadForm?.addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(quickLeadForm);
                const payload = {
                    name: (formData.get('name') || '').toString().trim() || null,
                    phone: normalizePhone((formData.get('phone') || '').toString().trim()),
                    region: (formData.get('region') || '').toString().trim() || null,
                    lead_topic_id: formData.get('lead_topic_id') || null,
                };

                try {
                    const response = await fetch('{{ route('lead.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(payload),
                    });

                    if (!response.ok) {
                        const errorPayload = await response.json().catch(() => null);
                        const msg = errorPayload?.errors?.phone?.[0] || errorPayload?.message ||
                            'Не удалось создать лид. Проверьте номер телефона.';
                        alert(msg);
                        console.error(errorPayload);
                        return;
                    }

                    const data = await response.json();
                    if (!data?.ok || !data?.lead) return;

                    const columnId = String(data.lead.column_id);
                    const targetCards = document.querySelector(`.cards[data-column-id="${columnId}"]`);
                    if (targetCards) {
                        targetCards.insertAdjacentHTML('beforeend', renderLeadCard(data.lead));
                        updateCounts();
                    }

                    closeModal(quickLeadModal);
                    quickLeadForm.reset();
                } catch (err) {
                    console.error(err);
                }
            });

            boardsContainer.addEventListener('click', async (e) => {
                const card = e.target.closest('.card');
                if (!card || dragInProgress) return;

                const leadId = card.dataset.id;
                if (!leadId) return;

                try {
                    const response = await fetch(`{{ url('lead') }}/${leadId}`, {
                        headers: {
                            'Accept': 'application/json',
                        }
                    });
                    if (!response.ok) return;

                    const data = await response.json();
                    if (!data?.ok || !data?.lead) return;

                    const lead = data.lead;
                    leadDetailsForm.elements.id.value = lead.id || '';
                    leadDetailsForm.elements.name.value = lead.name || '';
                    leadDetailsForm.elements.phone.value = formatPhoneMaskFromDigits(lead.phone) || '';
                    leadDetailsForm.elements.region.value = lead.region || '';
                    leadDetailsForm.elements.comment.value = lead.comment || '';
                    leadDetailsForm.elements.deadline_at.value = lead.deadline_at || '';
                    leadDetailsForm.elements.amount.value = lead.amount || '';
                    leadDetailsForm.elements.deal_start_date.value = lead.deal_start_date || '';

                    fillSelect(document.getElementById('details-topic'), topics, '— Не выбрано —', lead
                        .lead_topic_id || '');
                    fillSelect(document.getElementById('details-source'), campaignSources,
                        '— Не выбрано —', lead.campaign_source_id || '');
                    fillSelect(document.getElementById('details-user'), responsibleUsers,
                        '— Не выбрано —', lead.responsible_user_id || '');

                    openModal(leadDetailsModal);
                } catch (err) {
                    console.error(err);
                }
            });

            leadDetailsForm?.addEventListener('submit', async (e) => {
                e.preventDefault();

                const leadId = leadDetailsForm.elements.id.value;
                if (!leadId) return;

                const payload = {
                    name: leadDetailsForm.elements.name.value || null,
                    phone: normalizePhone(leadDetailsForm.elements.phone.value),
                    region: leadDetailsForm.elements.region.value || null,
                    lead_topic_id: leadDetailsForm.elements.lead_topic_id.value || null,
                    comment: leadDetailsForm.elements.comment.value || null,
                    deadline_at: leadDetailsForm.elements.deadline_at.value || null,
                    amount: leadDetailsForm.elements.amount.value || null,
                    deal_start_date: leadDetailsForm.elements.deal_start_date.value || null,
                    campaign_source_id: leadDetailsForm.elements.campaign_source_id.value || null,
                    responsible_user_id: leadDetailsForm.elements.responsible_user_id.value || null,
                };

                try {
                    const response = await fetch(`{{ url('lead') }}/${leadId}`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(payload),
                    });

                    if (!response.ok) {
                        const txt = await response.text();
                        alert('Не удалось сохранить лид. Проверьте корректность данных.');
                        console.error(txt);
                        return;
                    }

                    closeModal(leadDetailsModal);
                } catch (err) {
                    console.error(err);
                }
            });

            function openTopicModal() {
                topicForm.reset();
                openModal(topicModal);
            }

            document.getElementById('open-topic-modal')?.addEventListener('click', openTopicModal);
            document.getElementById('open-topic-modal-details')?.addEventListener('click', openTopicModal);
            document.getElementById('open-source-modal')?.addEventListener('click', () => {
                sourceForm.reset();
                openModal(sourceModal);
            });

            topicForm?.addEventListener('submit', async (e) => {
                e.preventDefault();
                const name = (topicForm.elements.name.value || '').trim();
                if (!name) return;

                try {
                    const response = await fetch('{{ route('lead.topics.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            name
                        }),
                    });

                    if (!response.ok) {
                        const txt = await response.text();
                        alert('Не удалось добавить тему. Возможно, такая уже есть.');
                        console.error(txt);
                        return;
                    }

                    const data = await response.json();
                    if (!data?.ok || !data?.topic) return;

                    topics.push(data.topic);
                    fillSelect(document.getElementById('quick-lead-topic'), topics, '— Не выбрано —',
                        data.topic.id);
                    fillSelect(document.getElementById('details-topic'), topics, '— Не выбрано —', data
                        .topic.id);
                    closeModal(topicModal);
                } catch (err) {
                    console.error(err);
                }
            });

            sourceForm?.addEventListener('submit', async (e) => {
                e.preventDefault();
                const name = (sourceForm.elements.name.value || '').trim();
                if (!name) return;

                try {
                    const response = await fetch('{{ route('lead.campaign-sources.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            name
                        }),
                    });

                    if (!response.ok) {
                        const txt = await response.text();
                        alert('Не удалось добавить источник.');
                        console.error(txt);
                        return;
                    }

                    const data = await response.json();
                    if (!data?.ok || !data?.source) return;

                    campaignSources.push(data.source);
                    fillSelect(document.getElementById('details-source'), campaignSources,
                        '— Не выбрано —', data.source.id);
                    closeModal(sourceModal);
                } catch (err) {
                    console.error(err);
                }
            });
        });
    </script>
@endsection
