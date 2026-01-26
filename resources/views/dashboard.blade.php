<x-app-layout>
    {{-- ================= HEADER ================= --}}
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

            <h2 class="font-semibold text-xl text-gray-800">
                📈 Динамика доходов
            </h2>

            <form method="GET" class="flex items-center gap-2">

                <input type="month" name="month" value="{{ $monthParam ?? now()->format('Y-m') }}"
                    class="border rounded-lg px-3 py-1.5 text-sm
                           focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />

                <button
                    class="px-4 py-1.5 bg-indigo-600 text-white text-sm rounded-lg
                           hover:bg-indigo-700 transition">
                    Показать
                </button>

                @if (request('period') === 'all')
                    <a href="{{ url()->current() }}" class="text-sm text-gray-500 hover:text-gray-700">Текущий месяц</a>
                @else
                    <a href="{{ url()->current() }}?period=all" class="text-sm text-gray-500 hover:text-gray-700">За всё
                        время</a>
                @endif

                <a href="{{ route('dashboard') }}" class="text-sm text-gray-500 hover:text-gray-700">Сброс</a>
            </form>
        </div>
    </x-slot>

    {{-- ================= CONTENT ================= --}}
    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- ===== QUICK SEARCH ===== --}}
            <div class="bg-white rounded-xl shadow p-5">
                <label for="site-search" class="block text-sm font-medium text-gray-700 mb-2">
                    🔍 Быстрый поиск
                </label>

                <div class="relative">
                    <input id="site-search" type="search" autocomplete="off"
                        placeholder="Поиск проектов и организаций…"
                        class="w-full rounded-lg border-gray-300
                   focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500
                   text-sm px-4 py-2 pr-10" />

                    {{-- иконка --}}
                    <div class="absolute inset-y-0 right-3 flex items-center text-gray-400 pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1010.5 18a7.5 7.5 0 006.15-3.35z" />
                        </svg>
                    </div>

                    {{-- результаты --}}
                    <div id="site-search-results"
                        class="hidden absolute left-0 right-0 mt-2 bg-white
                   border border-gray-200 rounded-lg shadow-lg z-50
                   max-h-72 overflow-y-auto">
                        <div id="site-search-list" class="divide-y"></div>

                        <div id="site-search-empty" class="p-3 text-sm text-gray-500 text-center hidden">
                            Ничего не найдено
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== LINK CARDS ===== --}}
            <div class="bg-white rounded-xl shadow p-6">
                <div class="mb-4">
                    <div class="text-sm font-medium text-gray-800">Карточки ссылок</div>
                    <div class="text-xs text-gray-500">Перетаскивайте карточки для изменения порядка</div>
                </div>

                <div class="text-xs text-gray-500 mb-4">Добавьте ссылку через карточку «+».</div>

                <div id="link-cards-grid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                    <button type="button" id="link-card-add"
                        class="link-card-add bg-white rounded-xl p-4 border border-dashed border-gray-300 shadow-sm hover:shadow-lg transition duration-200 transform hover:-translate-y-0.5 flex flex-col items-center justify-center text-center">
                        <div
                            class="w-12 h-12 rounded-full bg-indigo-50 border border-indigo-200 flex items-center justify-center">
                            <span class="text-2xl text-indigo-600">+</span>
                        </div>
                        <div class="text-sm font-semibold text-gray-700 mt-2">Добавить</div>
                    </button>

                    @foreach ($linkCards as $card)
                        <div class="link-card relative bg-gray-50 rounded-xl p-4 border border-gray-100 shadow-sm hover:shadow-lg transition duration-200 transform hover:-translate-y-0.5"
                            draggable="true" data-id="{{ $card->id }}">

                            <!-- Кнопка редактирования (карандаш) -->
                            <button type="button" data-id="{{ $card->id }}" data-title="{{ e($card->title) }}"
                                data-url="{{ e($card->url) }}" data-icon="{{ e($card->icon) }}"
                                class="edit-card-btn absolute top-2 left-2 z-10 text-gray-400 hover:text-indigo-600 bg-white rounded-full w-6 h-6 flex items-center justify-center text-xs shadow">
                                ✎
                            </button>

                            <!-- Крестик удаления -->
                            <form method="POST" action="{{ route('link-cards.destroy', $card->id) }}"
                                class="absolute top-2 right-2 z-10">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="text-gray-400 hover:text-red-500 bg-white rounded-full w-6 h-6 flex items-center justify-center text-xs shadow">
                                    ✕
                                </button>
                            </form>

                            <a href="{{ $card->url }}" target="_blank" rel="noopener noreferrer" draggable="false"
                                class="flex flex-col items-center text-center gap-2">
                                <div
                                    class="w-12 h-12 rounded-full bg-white border border-gray-200 flex items-center justify-center overflow-hidden">
                                    @if (!empty($card->icon))
                                        <img src="{{ $card->icon }}" alt="" class="w-8 h-8 object-contain"
                                            draggable="false" />
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M12 4.5a7.5 7.5 0 00-7.5 7.5v4.125A2.625 2.625 0 007.125 18h9.75A2.625 2.625 0 0019.5 16.125V12A7.5 7.5 0 0012 4.5z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M9.75 18v1.5a2.25 2.25 0 004.5 0V18" />
                                        </svg>
                                    @endif
                                </div>

                                <div class="text-sm font-semibold text-gray-900">
                                    {{ $card->title }}
                                </div>
                            </a>

                            <div class="text-xs text-gray-500 break-all mt-1">
                                {{ $card->url }}
                            </div>
                        </div>
                    @endforeach

                </div>

                @if (($linkCards ?? collect())->isEmpty())
                    <div class="text-sm text-gray-500 mt-4">Пока нет карточек. Добавьте первую ссылку.</div>
                @endif
            </div>

            {{-- ===== KPI CARDS ===== --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl shadow p-4">
                    <div class="text-xs text-gray-500">Выбранный месяц</div>
                    <div class="text-lg font-semibold mt-1">
                        {{ $monthLabel }}
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow p-4">
                    <div class="text-xs text-gray-500">Доход за месяц</div>
                    <div class="text-2xl font-bold mt-1 text-indigo-600">
                        {{ number_format($monthTotal, 2, '.', ' ') }} ₽
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow p-4">
                    <div class="text-xs text-gray-500">Расход за месяц</div>
                    <div class="text-2xl font-bold mt-1 text-red-600">
                        {{ number_format($monthTotalExpense ?? 0, 2, '.', ' ') }} ₽
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow p-4">
                    <div class="text-xs text-gray-500">Баланс (Доход − Расход)</div>
                    @php $net = $monthTotal - ($monthTotalExpense ?? 0); @endphp
                    <div class="text-2xl font-bold mt-1 {{ $net >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($net, 2, '.', ' ') }} ₽
                    </div>
                </div>
            </div>

            @php
                $currentMonth = \Carbon\Carbon::now()->format('Y-m');
            @endphp
            @if (request('period') !== 'all' && ($monthParam ?? $currentMonth) === $currentMonth)
                {{-- ===== BARTER AND OWN PROJECTS (count) ===== --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                    <button type="button" id="barter-open"
                        class="bg-white rounded-xl shadow p-4 text-left w-full hover:shadow-md transition">
                        <div class="text-xs text-gray-500">Бартерные проекты (на
                            {{ now()->locale('ru')->isoFormat('MMMM YYYY') }})</div>
                        <div class="text-2xl font-bold mt-1 text-yellow-600">{{ $barterCount ?? 0 }}</div>
                    </button>

                    <button type="button" id="own-open"
                        class="bg-white rounded-xl shadow p-4 text-left w-full hover:shadow-md transition">
                        <div class="text-xs text-gray-500">Свои проекты (на
                            {{ now()->locale('ru')->isoFormat('MMMM YYYY') }})</div>
                        <div class="text-2xl font-bold mt-1 text-indigo-600">{{ $ownCount ?? 0 }}</div>
                    </button>

                    <button type="button" id="commercial-open"
                        class="bg-white rounded-xl shadow p-4 text-left w-full hover:shadow-md transition">
                        <div class="text-xs text-gray-500">Коммерческие проекты (на
                            {{ now()->locale('ru')->isoFormat('MMMM YYYY') }})</div>
                        <div class="text-2xl font-bold mt-1 text-indigo-600">{{ $commercialCount }}</div>
                    </button>

                    <button type="button" id="expected-profit-open"
                        class="bg-white rounded-xl shadow p-4 text-left w-full hover:shadow-md transition">
                        <div class="text-xs text-gray-500">Ожидаемая прибыль (сумма по контрактам на
                            {{ now()->locale('ru')->isoFormat('MMMM YYYY') }})</div>
                        <div class="text-2xl font-bold mt-1 text-indigo-600">
                            {{ number_format($expectedProfit ?? 0, 2, '.', ' ') }} ₽
                        </div>
                        <div class="text-xs text-gray-500 mt-1">Не учитываются бартерные и свои проекты</div>
                    </button>
                </div>
            @endif
            {{-- ===== TAXES SUMMARY (VAT + USN) ===== --}}
            <div class="bg-white rounded-xl shadow p-4 mt-4">
                <div class="text-sm text-gray-500 mb-2">Налоги за выбранный месяц</div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <div class="text-xs text-gray-500">НДС (5%)</div>
                        <div class="text-lg font-semibold mt-1 text-indigo-700">
                            {{ number_format($monthVatTotal ?? 0, 2, '.', ' ') }} ₽
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <div class="text-xs text-gray-500">УСН (7%)</div>
                        <div class="text-lg font-semibold mt-1 text-indigo-700">
                            {{ number_format($monthUsnTotal ?? 0, 2, '.', ' ') }} ₽
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <div class="text-xs text-gray-500">Итого налогов</div>
                        <div class="text-2xl font-bold mt-1 text-indigo-600">
                            {{ number_format(($monthVatTotal ?? 0) + ($monthUsnTotal ?? 0), 2, '.', ' ') }} ₽
                        </div>
                    </div>
                </div>
            </div>

            @if (request('period') !== 'all')
                {{-- ===== MONTHLY EXPENSES ===== --}}
                <div class="bg-white rounded-xl shadow p-6 mt-6">
                    <div class="mb-4">
                        <div class="text-sm font-medium text-gray-800">Ежемесячные расходы</div>
                        <div class="text-xs text-gray-500">
                            @if (!empty($showWeeklyExpenses))
                                За текущую неделю
                            @else
                                Статусы за
                                {{ \Carbon\Carbon::createFromFormat('Y-m', $monthlyExpensesMonth)->locale('ru')->isoFormat('MMMM YYYY') }}
                            @endif
                        </div>
                    </div>

                    @if ($monthlyExpenses->isEmpty())
                        <div class="text-sm text-gray-500">Нет активных ежемесячных расходов.</div>
                    @else
                        <div>
                            <div class="flex items-center gap-6 py-2 text-xs text-gray-500 font-medium border-b">
                                <div class="w-4 shrink-0">Дата</div>
                                <div class="min-w-0 flex-1">Название / примечание</div>
                                <div class="flex items-center gap-3 shrink-0 justify-end">Сумма / Статус</div>
                            </div>
                        </div>

                        <div class="divide-y">
                            @foreach ($monthlyExpenses as $me)
                                <div class="flex items-center gap-6 py-3">
                                    {{-- Дата оплаты --}}
                                    <div class="w-4 shrink-0 text-sm text-gray-600">
                                        {{ $me->due_date->format('d') }}
                                    </div>

                                    {{-- Название + примечание --}}
                                    <div class="min-w-0 flex-1">
                                        <div class="font-medium text-gray-900 truncate">
                                            {{ $me->title }}
                                        </div>

                                        @if (!empty($me->note))
                                            <div class="text-xs text-gray-500">
                                                {{ $me->note }}
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Правая часть --}}
                                    <div class="flex items-center gap-3 shrink-0">
                                        <div class="text-sm font-semibold text-gray-900 whitespace-nowrap">
                                            {{ number_format($me->amount, 2, '.', ' ') }} ₽
                                        </div>

                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded text-xs {{ $me->status_class }}">
                                            {{ $me->status_label }}
                                        </span>

                                        @if ($me->status_state !== 'paid')
                                            <button type="button"
                                                class="monthly-expense-pay-btn px-3 py-1 bg-indigo-600 text-white text-xs rounded hover:bg-indigo-700 whitespace-nowrap"
                                                data-id="{{ $me->id }}"
                                                data-month="{{ $monthlyExpensesMonth }}"
                                                data-amount="{{ $me->amount }}"
                                                data-due="{{ $me->due_date->format('Y-m-d') }}"
                                                data-title="{{ e($me->title) }}" data-note="{{ e($me->note) }}">
                                                Оплатить
                                            </button>
                                        @elseif (!empty($me->status_expense_id))
                                            <span class="text-xs text-gray-400 whitespace-nowrap">
                                                Расход #{{ $me->status_expense_id }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach

                        </div>

                        <div class="mt-4 pt-4 border-t flex items-center justify-between text-sm text-gray-700">
                            <div>Всего расходов: <span class="font-semibold">{{ $monthlyExpenses->count() }}</span>
                            </div>
                            <div>Итоговая сумма:
                                <span class="font-semibold">
                                    {{ number_format($monthlyExpenses->sum('amount'), 2, '.', ' ') }}
                                    ₽</span>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- ===== MONTHLY EXPENSE PAY MODAL ===== --}}
            <div id="monthly-expense-pay-modal" class="fixed inset-0 z-50 hidden">
                <div id="monthly-expense-pay-overlay" class="absolute inset-0 bg-black/50"></div>
                <div class="absolute inset-0 flex items-center justify-center p-4">
                    <div class="w-full max-w-lg bg-white rounded-xl shadow-lg overflow-hidden">
                        <div class="flex items-center justify-between px-5 py-4 border-b">
                            <div class="text-lg font-semibold text-gray-800">Оплата ежемесячного расхода</div>
                            <button type="button" id="monthly-expense-pay-close"
                                class="text-gray-500 hover:text-gray-700">✕</button>
                        </div>

                        <form id="monthly-expense-pay-form" method="POST" action="" class="p-5 space-y-4">
                            @csrf
                            <input type="hidden" name="month" id="mepf-month" value="" />
                            <input type="hidden" name="_monthly_expense_id" id="mepf-id" value="" />

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Сумма</label>
                                <input type="text" name="amount" id="mepf-amount" required
                                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 text-sm px-3 py-2" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Дата расхода</label>
                                <input type="date" name="expense_date" id="mepf-date"
                                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 text-sm px-3 py-2" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Категория расхода</label>
                                <select name="expense_category_id" id="mepf-category"
                                    class="w-full rounded-lg border-gray-300 text-sm px-3 py-2">
                                    @foreach ($officeExpenseCategories ?? collect() as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->title }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Комментарий</label>
                                <textarea name="description" id="mepf-description" rows="3"
                                    class="w-full rounded-lg border-gray-300 text-sm px-3 py-2"></textarea>
                            </div>

                            <div id="mepf-errors" class="text-sm text-red-500 hidden"></div>

                            <div class="flex items-center justify-end gap-3 pt-2">
                                <button type="button" id="mepf-cancel"
                                    class="px-4 py-2 text-sm rounded-lg border border-gray-300 hover:bg-gray-50">Отмена</button>
                                <button type="button" id="mepf-mark-paid-only"
                                    class="px-4 py-2 bg-gray-100 text-sm rounded-lg hover:bg-gray-200">Пометить как
                                    оплаченный без создания расхода</button>
                                <button type="submit" id="mepf-submit"
                                    class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">Сохранить
                                    и отметить как оплачено</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>


            {{-- ===== CHART ===== --}}
            <div class="bg-white rounded-xl shadow p-6">
                <div class="mb-4">
                    <div class="text-sm font-medium text-gray-800">
                        Поступления по дням
                    </div>
                    <div class="text-xs text-gray-500">
                        Финансовая динамика за выбранный месяц
                    </div>
                </div>

                <div class="h-100">
                    <canvas id="incomeChart"></canvas>
                </div>
            </div>

            {{-- ===== TOP PROJECTS ===== --}}
            <div class="bg-white rounded-xl shadow p-6 mt-6">
                <div class="mb-4">
                    <div class="text-sm font-medium text-gray-800">Самые доходные проекты (топ 5)</div>
                    <div class="text-xs text-gray-500">
                        {{ request('period') === 'all' ? 'Сумма поступлений за весь период' : 'Сумма поступлений за выбранный месяц' }}
                    </div>
                </div>

                <div class="h-72">
                    <canvas id="topProjectsChart"></canvas>
                </div>
            </div>

            {{-- ===== ACTIVE PROJECTS ===== --}}
            <div class="bg-white rounded-xl shadow p-6 mt-6">
                <div class="mb-4">
                    <div class="text-sm font-medium text-gray-800">Активные проекты</div>
                    <div class="text-xs text-gray-500">Количество проектов в работе (закрытые не считаются)</div>
                </div>

                <div class="h-60">
                    <canvas id="activeProjectsChart"></canvas>
                </div>
            </div>

            {{-- ===== DEBTORS ===== --}}
            <div class="bg-white rounded-xl shadow p-6 mt-6">
                <div class="mb-4">
                    <div class="text-sm font-medium text-gray-800">Должники</div>
                    <div class="text-xs text-gray-500">Топ-10 должников (balance &lt; 0) — показываем абсолютные
                        значения задолженности</div>
                </div>

                <div class="h-60">
                    <canvas id="debtorsChart"></canvas>
                </div>
            </div>

        </div>
    </div>

    {{-- ===== LINK CARD MODAL (Create & Edit) ===== --}}
    <div id="link-card-modal" class="fixed inset-0 z-50 hidden">
        <div id="link-card-overlay" class="absolute inset-0 bg-black/50"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="w-full max-w-xl bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b">
                    <div id="link-card-modal-title" class="text-lg font-semibold text-gray-800">Новая карточка</div>
                    <button type="button" id="link-card-close" class="text-gray-500 hover:text-gray-700">✕</button>
                </div>

                <form id="link-card-form" method="POST" action="{{ route('link-cards.store') }}"
                    class="p-5 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Название</label>
                        <input type="text" name="title" required placeholder="Название"
                            class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 text-sm px-3 py-2" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ссылка</label>
                        <input type="url" name="url" required placeholder="https://..."
                            class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 text-sm px-3 py-2" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Иконка</label>
                        <input type="text" name="icon" placeholder="Ссылка на иконку (опционально)"
                            class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 text-sm px-3 py-2" />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" id="link-card-cancel"
                            class="px-4 py-2 text-sm rounded-lg border border-gray-300 hover:bg-gray-50">
                            Отмена
                        </button>
                        <button type="submit" id="link-card-submit"
                            class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">
                            Добавить
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    {{-- ===== EXPECTED PROFIT MODAL ===== --}}
    <div id="expected-profit-modal" class="fixed inset-0 z-50 hidden">
        <div id="expected-profit-overlay" class="absolute inset-0 bg-black/50"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="w-full max-w-3xl bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b">
                    <div>
                        <div class="text-lg font-semibold text-gray-800">Ожидаемая прибыль — детали</div>
                        <div class="text-xs text-gray-500">
                            {{ now()->locale('ru')->isoFormat('MMMM YYYY') }}
                        </div>
                    </div>
                    <button type="button" id="expected-profit-close"
                        class="text-gray-500 hover:text-gray-700">✕</button>
                </div>

                <div class="p-5 max-h-[70vh] overflow-y-auto">
                    @if (($expectedProjects ?? collect())->isEmpty())
                        <div class="text-sm text-gray-500">Нет проектов для расчёта.</div>
                    @else
                        <div class="overflow-auto">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-500">
                                            Проект</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-500">
                                            Сумма контракта</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-500">
                                            Дата закрытия</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    @foreach ($expectedProjects as $proj)
                                        <tr>
                                            <td class="px-3 py-2">
                                                <a href="{{ route('projects.show', $proj) }}"
                                                    class="text-indigo-600 hover:underline">
                                                    {{ $proj->title }}
                                                </a>
                                            </td>
                                            <td class="px-3 py-2">
                                                {{ number_format($proj->contract_amount ?? 0, 2, '.', ' ') }} ₽
                                            </td>
                                            <td class="px-3 py-2">
                                                {{ $proj->closed_at?->format('d.m.Y') ?? '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ===== BARTER PROJECTS MODAL ===== --}}
    <div id="barter-projects-modal" class="fixed inset-0 z-50 hidden">
        <div id="barter-projects-overlay" class="absolute inset-0 bg-black/50"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="w-full max-w-3xl bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b">
                    <div>
                        <div class="text-lg font-semibold text-gray-800">Бартерные проекты</div>
                        <div class="text-xs text-gray-500">{{ now()->locale('ru')->isoFormat('MMMM YYYY') }}</div>
                    </div>
                    <button type="button" id="barter-projects-close"
                        class="text-gray-500 hover:text-gray-700">✕</button>
                </div>

                <div class="p-5 max-h-[70vh] overflow-y-auto">
                    @if (($barterProjects ?? collect())->isEmpty())
                        <div class="text-sm text-gray-500">Нет бартерных проектов.</div>
                    @else
                        <div class="overflow-auto">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-500">
                                            Проект</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-500">
                                            Сумма контракта</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-500">
                                            Дата создания</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    @foreach ($barterProjects as $proj)
                                        <tr>
                                            <td class="px-3 py-2"><a href="{{ route('projects.show', $proj) }}"
                                                    class="text-indigo-600 hover:underline">{{ $proj->title }}</a>
                                            </td>
                                            <td class="px-3 py-2">
                                                {{ number_format($proj->contract_amount ?? 0, 2, '.', ' ') }} ₽</td>
                                            <td class="px-3 py-2">{{ $proj->created_at?->format('d.m.Y') ?? '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ===== OWN PROJECTS MODAL ===== --}}
    <div id="own-projects-modal" class="fixed inset-0 z-50 hidden">
        <div id="own-projects-overlay" class="absolute inset-0 bg-black/50"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="w-full max-w-3xl bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b">
                    <div>
                        <div class="text-lg font-semibold text-gray-800">Свои проекты</div>
                        <div class="text-xs text-gray-500">{{ now()->locale('ru')->isoFormat('MMMM YYYY') }}</div>
                    </div>
                    <button type="button" id="own-projects-close"
                        class="text-gray-500 hover:text-gray-700">✕</button>
                </div>

                <div class="p-5 max-h-[70vh] overflow-y-auto">
                    @if (($ownProjects ?? collect())->isEmpty())
                        <div class="text-sm text-gray-500">Нет своих проектов.</div>
                    @else
                        <div class="overflow-auto">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-500">
                                            Проект</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-500">
                                            Сумма контракта</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-500">
                                            Дата создания</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    @foreach ($ownProjects as $proj)
                                        <tr>
                                            <td class="px-3 py-2"><a href="{{ route('projects.show', $proj) }}"
                                                    class="text-indigo-600 hover:underline">{{ $proj->title }}</a>
                                            </td>
                                            <td class="px-3 py-2">
                                                {{ number_format($proj->contract_amount ?? 0, 2, '.', ' ') }} ₽</td>
                                            <td class="px-3 py-2">{{ $proj->created_at?->format('d.m.Y') ?? '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ===== COMMERCIAL PROJECTS MODAL ===== --}}
    <div id="commercial-projects-modal" class="fixed inset-0 z-50 hidden">
        <div id="commercial-projects-overlay" class="absolute inset-0 bg-black/50"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="w-full max-w-3xl bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b">
                    <div>
                        <div class="text-lg font-semibold text-gray-800">Коммерческие проекты</div>
                        <div class="text-xs text-gray-500">{{ now()->locale('ru')->isoFormat('MMMM YYYY') }}</div>
                    </div>
                    <button type="button" id="commercial-projects-close"
                        class="text-gray-500 hover:text-gray-700">✕</button>
                </div>

                <div class="p-5 max-h-[70vh] overflow-y-auto">
                    @if (($commercialProjects ?? collect())->isEmpty())
                        <div class="text-sm text-gray-500">Нет коммерческих проектов.</div>
                    @else
                        <div class="overflow-auto">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-500">
                                            Проект</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-500">
                                            Сумма контракта</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-500">
                                            Дата создания</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    @foreach ($commercialProjects as $proj)
                                        <tr>
                                            <td class="px-3 py-2"><a href="{{ route('projects.show', $proj) }}"
                                                    class="text-indigo-600 hover:underline">{{ $proj->title }}</a>
                                            </td>
                                            <td class="px-3 py-2">
                                                {{ number_format($proj->contract_amount ?? 0, 2, '.', ' ') }} ₽</td>
                                            <td class="px-3 py-2">{{ $proj->created_at?->format('d.m.Y') ?? '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ================= SCRIPTS ================= --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        (function() {

            const labels = {!! json_encode($labels) !!};
            const income = {!! json_encode($incomeData) !!};
            const expense = {!! json_encode($expenseData) !!};
            const yMax = {{ (int) $yMax }};
            const yMin = {{ (int) $yMin }};
            const yStep = {{ (int) $step }};

            const canvas = document.getElementById('incomeChart');
            if (!canvas) {
                console.error('Chart canvas #incomeChart not found');
                return;
            }
            const ctx = canvas.getContext('2d');
            if (!ctx) {
                console.error('Could not get 2D context for #incomeChart');
                return;
            }

            // --- Top projects data ---
            const topLabels = {!! json_encode($topProjectsLabels ?? []) !!};
            const topData = {!! json_encode($topProjectsData ?? []) !!};
            const topMax = {{ (int) ($topMaxChart ?? 0) }};
            const topStep = {{ (int) ($topStep ?? 1000) }};

            // If all data arrays are empty, show console and do nothing
            const hasData = (Array.isArray(income) && income.some(v => Number(v) !== 0)) || (Array.isArray(expense) &&
                expense.some(v => Number(v) !== 0));
            if (!hasData) {
                // draw empty axes with zero line
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: []
                    },
                    options: {
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
                console.info('No income/expense data for selected month — continuing to render other charts');
            } else {
                // Рисуем график доходов/расходов только если есть данные
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                                label: 'Доходы',
                                data: income,
                                borderColor: '#10B981',
                                backgroundColor: 'rgba(16,185,129,0.12)',
                                tension: 0.3,
                                fill: true,
                            },
                            {
                                label: 'Расходы',
                                data: expense,
                                borderColor: '#EF4444',
                                backgroundColor: 'rgba(239,68,68,0.08)',
                                tension: 0.3,
                                fill: true,
                            },

                        ]
                    },
                    options: {
                        scales: {
                            y: {
                                min: yMin,
                                max: yMax,
                                ticks: {
                                    stepSize: yStep,
                                    callback: function(val) {
                                        if (val === 0) return '0k';
                                        return (val / 1000) + 'k';
                                    }
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(ctx) {
                                        const v = ctx.parsed.y;
                                        return ctx.dataset.label + ': ' + Number(v).toLocaleString(
                                            'ru-RU', {
                                                minimumFractionDigits: 2
                                            }) + ' ₽';
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // --- Top projects chart (horizontal bars) ---
            const topCanvas = document.getElementById('topProjectsChart');
            if (topCanvas) {
                const topCtx = topCanvas.getContext('2d');
                try {
                    new Chart(topCtx, {
                        type: 'bar',
                        data: {
                            labels: topLabels,
                            datasets: [{
                                label: 'Доходы',
                                data: topData,
                                backgroundColor: '#3B82F6'
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: {
                                    min: 0,
                                    max: topMax,
                                    ticks: {
                                        stepSize: topStep,
                                        callback: function(v) {
                                            if (v === 0) return '0';
                                            return (v / 1000) + 'k';
                                        }
                                    }
                                },
                                y: {
                                    ticks: {
                                        autoSkip: false
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(ctx) {
                                            const v = ctx.parsed.x;
                                            return Number(v).toLocaleString('ru-RU', {
                                                minimumFractionDigits: 2
                                            }) + ' ₽';
                                        }
                                    }
                                }
                            }
                        }
                    });
                } catch (e) {
                    console.error('Top projects chart error', e);
                }
            }

            // --- Active projects chart (line) ---
            const activeCanvas = document.getElementById('activeProjectsChart');
            if (activeCanvas) {
                const activeCtx = activeCanvas.getContext('2d');
                try {
                    const activeData = {!! json_encode($activeData ?? []) !!};
                    const activeStep = {{ (int) ($activeStep ?? 1) }};

                    new Chart(activeCtx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Активные проекты',
                                data: activeData,
                                borderColor: '#F59E0B',
                                backgroundColor: 'rgba(245,158,11,0.08)',
                                tension: 0.2,
                                fill: true,
                                pointRadius: 3
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: activeStep,
                                        precision: 0
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(ctx) {
                                            return ctx.dataset.label + ': ' + Number(ctx.parsed.y)
                                                .toLocaleString('ru-RU') + ' шт';
                                        }
                                    }
                                }
                            }
                        }
                    });
                } catch (e) {
                    console.error('Active projects chart error', e);
                }
            }

            // --- Debtors (vertical bar) ---
            const debtorsCanvas = document.getElementById('debtorsChart');
            if (debtorsCanvas) {
                const dCtx = debtorsCanvas.getContext('2d');
                try {
                    const dLabels = {!! json_encode($debtorLabels ?? []) !!};
                    const dData = {!! json_encode($debtorData ?? []) !!};
                    const dMax = {{ (int) ($debtorMaxChart ?? 0) }};
                    const dStep = {{ (int) ($debtorStep ?? 10000) }};

                    const hasDebtors = Array.isArray(dData) && dData.some(v => Number(v) > 0);
                    if (!hasDebtors) {
                        // draw blank axes
                        new Chart(dCtx, {
                            type: 'bar',
                            data: {
                                labels: dLabels,
                                datasets: []
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false
                            }
                        });
                    } else {
                        new Chart(dCtx, {
                            type: 'bar',
                            data: {
                                labels: dLabels,
                                datasets: [{
                                    label: 'Задолженность',
                                    data: dData,
                                    backgroundColor: '#EF4444'
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        min: 0,
                                        max: dMax,
                                        ticks: {
                                            stepSize: dStep,
                                            callback: function(v) {
                                                if (v === 0) return '0';
                                                return (v / 1000) + 'k';
                                            }
                                        }
                                    },
                                    x: {
                                        ticks: {
                                            autoSkip: false
                                        }
                                    }
                                },
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(ctx) {
                                                // Show as negative (debtor)
                                                return '-' + Number(ctx.parsed.y).toLocaleString('ru-RU', {
                                                    minimumFractionDigits: 2
                                                }) + ' ₽';
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }
                } catch (e) {
                    console.error('Debtors chart error', e);
                }
            }

        })();
        document.addEventListener('DOMContentLoaded', function() {
            const expectedModal = document.getElementById('expected-profit-modal');
            const expectedOpen = document.getElementById('expected-profit-open');
            const expectedClose = document.getElementById('expected-profit-close');
            const expectedOverlay = document.getElementById('expected-profit-overlay');

            function openExpectedModal() {
                if (!expectedModal) return;
                expectedModal.classList.remove('hidden');
            }

            function closeExpectedModal() {
                if (!expectedModal) return;
                expectedModal.classList.add('hidden');
            }

            if (expectedOpen) expectedOpen.addEventListener('click', openExpectedModal);
            if (expectedClose) expectedClose.addEventListener('click', closeExpectedModal);
            if (expectedOverlay) expectedOverlay.addEventListener('click', closeExpectedModal);

            // Barter / Own / Commercial modals
            const barterModal = document.getElementById('barter-projects-modal');
            const barterOpen = document.getElementById('barter-open');
            const barterClose = document.getElementById('barter-projects-close');
            const barterOverlay = document.getElementById('barter-projects-overlay');

            function openBarterModal() {
                if (!barterModal) return;
                barterModal.classList.remove('hidden');
            }

            function closeBarterModal() {
                if (!barterModal) return;
                barterModal.classList.add('hidden');
            }

            if (barterOpen) barterOpen.addEventListener('click', openBarterModal);
            if (barterClose) barterClose.addEventListener('click', closeBarterModal);
            if (barterOverlay) barterOverlay.addEventListener('click', closeBarterModal);

            const ownModal = document.getElementById('own-projects-modal');
            const ownOpen = document.getElementById('own-open');
            const ownClose = document.getElementById('own-projects-close');
            const ownOverlay = document.getElementById('own-projects-overlay');

            function openOwnModal() {
                if (!ownModal) return;
                ownModal.classList.remove('hidden');
            }

            function closeOwnModal() {
                if (!ownModal) return;
                ownModal.classList.add('hidden');
            }

            if (ownOpen) ownOpen.addEventListener('click', openOwnModal);
            if (ownClose) ownClose.addEventListener('click', closeOwnModal);
            if (ownOverlay) ownOverlay.addEventListener('click', closeOwnModal);

            const commercialModal = document.getElementById('commercial-projects-modal');
            const commercialOpen = document.getElementById('commercial-open');
            const commercialClose = document.getElementById('commercial-projects-close');
            const commercialOverlay = document.getElementById('commercial-projects-overlay');

            function openCommercialModal() {
                if (!commercialModal) return;
                commercialModal.classList.remove('hidden');
            }

            function closeCommercialModal() {
                if (!commercialModal) return;
                commercialModal.classList.add('hidden');
            }

            if (commercialOpen) commercialOpen.addEventListener('click', openCommercialModal);
            if (commercialClose) commercialClose.addEventListener('click', closeCommercialModal);
            if (commercialOverlay) commercialOverlay.addEventListener('click', closeCommercialModal);

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeExpectedModal();
                    closeBarterModal();
                    closeOwnModal();
                    closeCommercialModal();
                }
            });

            const input = document.getElementById('site-search');
            const resultsWrap = document.getElementById('site-search-results');
            const resultsList = document.getElementById('site-search-list');
            const emptyEl = document.getElementById('site-search-empty');
            let timer = null;
            const delay = 300;

            function hideResults() {
                resultsWrap.classList.add('hidden');
            }

            function showResults() {
                resultsWrap.classList.remove('hidden');
            }

            function renderResults(items) {
                resultsList.innerHTML = '';
                if (!items || items.length === 0) {
                    emptyEl.classList.remove('hidden');
                    return;
                }
                emptyEl.classList.add('hidden');

                for (const it of items) {
                    const div = document.createElement('div');
                    div.className = 'px-3 py-2 hover:bg-gray-50';
                    const a = document.createElement('a');
                    a.href = it.url;
                    a.className = 'block text-sm text-gray-900';
                    a.textContent = (it.type === 'project' ? 'Проект: ' : 'Организация: ') + it.label;
                    div.appendChild(a);
                    resultsList.appendChild(div);
                }
            }

            function doSearch(q) {
                if (!q || q.trim().length === 0) {
                    hideResults();
                    return;
                }
                const url = '{{ route('search') }}' + '?q=' + encodeURIComponent(q);
                fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }).then(res => res.json())
                    .then(json => {
                        if (json.results && json.results.length) {
                            renderResults(json.results);
                            showResults();
                        } else {
                            renderResults([]);
                            showResults();
                        }
                    }).catch(err => {
                        console.error('Search error', err);
                        hideResults();
                    });
            }

            input.addEventListener('input', function(e) {
                clearTimeout(timer);
                const q = e.target.value;
                timer = setTimeout(() => doSearch(q), delay);
            });

            // Close on outside click or ESC
            document.addEventListener('click', function(e) {
                if (!resultsWrap.contains(e.target) && e.target !== input) {
                    hideResults();
                }
            });
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') hideResults();
            });

            // Show on focus if there's existing results
            input.addEventListener('focus', function() {
                if (resultsList.children.length || !emptyEl.classList.contains('hidden')) {
                    showResults();
                }
            });
            const linkModal = document.getElementById('link-card-modal');
            const linkOpen = document.getElementById('link-card-add');
            const linkClose = document.getElementById('link-card-close');
            const linkCancel = document.getElementById('link-card-cancel');
            const linkOverlay = document.getElementById('link-card-overlay');
            const linkForm = document.getElementById('link-card-form');
            const linkModalTitle = document.getElementById('link-card-modal-title');
            const linkModalSubmit = document.getElementById('link-card-submit');

            function openLinkModal() {
                if (!linkModal) return;
                // default to create
                linkForm.action = "{{ route('link-cards.store') }}";
                linkForm.querySelector('input[name="title"]').value = '';
                linkForm.querySelector('input[name="url"]').value = '';
                linkForm.querySelector('input[name="icon"]').value = '';
                const methodInput = linkForm.querySelector('input[name="_method"]');
                if (methodInput) methodInput.remove();

                linkModalTitle.textContent = 'Новая карточка';
                linkModalSubmit.textContent = 'Добавить';

                linkModal.classList.remove('hidden');
            }

            function closeLinkModal() {
                if (!linkModal) return;
                linkModal.classList.add('hidden');
            }

            if (linkOpen) linkOpen.addEventListener('click', openLinkModal);
            if (linkClose) linkClose.addEventListener('click', closeLinkModal);
            if (linkCancel) linkCancel.addEventListener('click', closeLinkModal);
            if (linkOverlay) linkOverlay.addEventListener('click', closeLinkModal);

            // Delegate edit button clicks to open edit modal (works even if function defined later)
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.edit-card-btn');
                if (!btn) return;
                const id = btn.dataset.id;
                const title = btn.dataset.title || '';
                const url = btn.dataset.url || '';
                const icon = btn.dataset.icon || '';

                if (typeof window.openEditModal === 'function') {
                    window.openEditModal(id, title, url, icon);
                } else {
                    // fallback: populate form and show modal
                    linkForm.action = `/link-cards/${id}`;
                    linkForm.querySelector('input[name="title"]').value = title;
                    linkForm.querySelector('input[name="url"]').value = url;
                    linkForm.querySelector('input[name="icon"]').value = icon;
                    let methodInput = linkForm.querySelector('input[name="_method"]');
                    if (!methodInput) {
                        methodInput = document.createElement('input');
                        methodInput.type = 'hidden';
                        methodInput.name = '_method';
                        linkForm.appendChild(methodInput);
                    }
                    methodInput.value = 'PUT';
                    linkModalTitle.textContent = 'Редактировать карточку';
                    linkModalSubmit.textContent = 'Сохранить';
                    linkModal.classList.remove('hidden');
                }
            });

            // --- Edit functionality ---
            window.openEditModal = function(id, title, url, icon) {
                if (!linkModal) return;

                linkForm.action = `/link-cards/${id}`; // URL обновления
                linkForm.querySelector('input[name="title"]').value = title;
                linkForm.querySelector('input[name="url"]').value = url;
                linkForm.querySelector('input[name="icon"]').value = icon;

                let methodInput = linkForm.querySelector('input[name="_method"]');
                if (!methodInput) {
                    methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    linkForm.appendChild(methodInput);
                }
                methodInput.value = 'PUT';

                linkModalTitle.textContent = 'Редактировать карточку';
                linkModalSubmit.textContent = 'Сохранить';

                linkModal.classList.remove('hidden');
            }

            // --- Drag & Drop ---
            const grid = document.getElementById('link-cards-grid');
            if (grid) {
                const csrfToken = '{{ csrf_token() }}';
                const reorderUrl = '{{ route('link-cards.reorder') }}';
                let dragSrc = null;

                function persistOrder() {
                    const order = Array.from(grid.children)
                        .filter((el) => el.dataset.id)
                        .map((el) => Number(el.dataset.id));
                    fetch(reorderUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            order: order
                        })
                    }).catch(err => console.error('Reorder error', err));
                }

                function handleDragStart(e) {
                    dragSrc = this;
                    this.classList.add('opacity-60');
                    e.dataTransfer.effectAllowed = 'move';
                    e.dataTransfer.setData('text/plain', '');
                }

                function handleDragOver(e) {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';
                }

                function handleDrop(e) {
                    e.preventDefault();
                    if (!dragSrc || dragSrc === this) return;

                    const items = Array.from(grid.children);
                    const srcIndex = items.indexOf(dragSrc);
                    const targetIndex = items.indexOf(this);

                    if (srcIndex < targetIndex) {
                        grid.insertBefore(dragSrc, this.nextSibling);
                    } else {
                        grid.insertBefore(dragSrc, this);
                    }

                    persistOrder();
                }

                function handleDragEnd() {
                    this.classList.remove('opacity-60');
                    dragSrc = null;
                }

                Array.from(grid.children).forEach((card) => {
                    if (card.classList.contains('link-card-add')) return;
                    card.addEventListener('dragstart', handleDragStart);
                    card.addEventListener('dragover', handleDragOver);
                    card.addEventListener('drop', handleDrop);
                    card.addEventListener('dragend', handleDragEnd);
                });
            }

            // --- Monthly expense pay modal handling ---
            const meModal = document.getElementById('monthly-expense-pay-modal');
            const meOverlay = document.getElementById('monthly-expense-pay-overlay');
            const meClose = document.getElementById('monthly-expense-pay-close');
            const meCancel = document.getElementById('mepf-cancel');
            const meForm = document.getElementById('monthly-expense-pay-form');
            const meErrors = document.getElementById('mepf-errors');
            const meBase = '{{ url('monthly-expenses') }}';

            function openMeModal() {
                if (!meModal) return;
                meErrors.classList.add('hidden');
                meErrors.innerHTML = '';
                meModal.classList.remove('hidden');
            }

            function closeMeModal() {
                if (!meModal) return;
                meModal.classList.add('hidden');
            }

            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.monthly-expense-pay-btn');
                if (!btn) return;
                const id = btn.dataset.id;
                const month = btn.dataset.month;
                const amount = btn.dataset.amount;
                const due = btn.dataset.due;
                const title = btn.dataset.title || '';
                const note = btn.dataset.note || '';

                // populate form
                document.getElementById('mepf-id').value = id;
                document.getElementById('mepf-month').value = month;
                document.getElementById('mepf-amount').value = Number(amount).toFixed(2);
                document.getElementById('mepf-date').value = due;
                document.getElementById('mepf-description').value = title + (note ? ' — ' + note : '');

                // set form action
                meForm.action = meBase + '/' + id + '/pay';

                openMeModal();
            });

            if (meClose) meClose.addEventListener('click', closeMeModal);
            if (meCancel) meCancel.addEventListener('click', closeMeModal);
            if (meOverlay) meOverlay.addEventListener('click', closeMeModal);

            meForm.addEventListener('submit', function(e) {
                e.preventDefault();
                meErrors.classList.add('hidden');
                meErrors.innerHTML = '';

                const action = meForm.action;
                const formData = new FormData(meForm);

                fetch(action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: formData
                }).then(async (res) => {
                    if (res.ok) {
                        // success — reload to reflect status
                        closeMeModal();
                        location.reload();
                    } else if (res.status === 422) {
                        const j = await res.json();
                        const errs = j.errors || {};
                        const html = Object.values(errs).flat().map(s => `<div>${s}</div>`)
                            .join('');
                        meErrors.innerHTML = html;
                        meErrors.classList.remove('hidden');
                    } else {
                        console.error('Payment error', res);
                        meErrors.innerHTML = 'Ошибка при оплате. Попробуйте позже.';
                        meErrors.classList.remove('hidden');
                    }
                }).catch(err => {
                    console.error('Payment request failed', err);
                    meErrors.innerHTML = 'Ошибка при оплате. Попробуйте позже.';
                    meErrors.classList.remove('hidden');
                });
            });

            // --- Mark paid without creating expense ---
            const meMarkOnlyBtn = document.getElementById('mepf-mark-paid-only');
            if (meMarkOnlyBtn) {
                meMarkOnlyBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    meErrors.classList.add('hidden');
                    meErrors.innerHTML = '';

                    const id = document.getElementById('mepf-id').value;
                    const month = document.getElementById('mepf-month').value;
                    const url = meBase + '/' + id + '/mark-paid';

                    const payload = new FormData();
                    payload.append('month', month);

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        },
                        body: payload
                    }).then(async (res) => {
                        if (res.ok) {
                            closeMeModal();
                            location.reload();
                        } else if (res.status === 422) {
                            const j = await res.json();
                            const errs = j.errors || {};
                            const html = Object.values(errs).flat().map(s => `<div>${s}</div>`)
                                .join('');
                            meErrors.innerHTML = html;
                            meErrors.classList.remove('hidden');
                        } else {
                            console.error('Mark paid only error', res);
                            meErrors.innerHTML = 'Ошибка. Попробуйте позже.';
                            meErrors.classList.remove('hidden');
                        }
                    }).catch(err => {
                        console.error('Mark paid only request failed', err);
                        meErrors.innerHTML = 'Ошибка. Попробуйте позже.';
                        meErrors.classList.remove('hidden');
                    });
                });
            }

        });
    </script>
</x-app-layout>
