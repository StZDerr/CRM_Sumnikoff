@extends('layouts.app')

@section('content')
    <div class="max-w-full mx-auto py-6">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-semibold">Операции</h1>

            <div class="flex gap-2">
                {{-- Доход (только admin) --}}
                @if (auth()->user()->isAdmin())
                    <a href="#" id="openPaymentOffcanvas" data-url="{{ route('payments.create') }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white
                      hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Доход
                    </a>
                @endif

                {{-- Расход --}}
                <a href="#" id="openExpenseOffcanvas" data-url="{{ route('expenses.create') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white
                  hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                    </svg>
                    Расход
                </a>

                {{-- Расходы не наши --}}
                @if (isset($notOurExpenseCategories) && $notOurExpenseCategories->count())
                    <button type="button" id="openNotOurExpenseBtn"
                        class="inline-flex items-center gap-2 rounded-lg bg-slate-700 px-4 py-2 text-sm font-medium text-white
            hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18.364 5.636l-12.728 12.728M5.636 5.636l12.728 12.728" />
                        </svg>
                        Клиентские расходы
                    </button>
                @endif

                @if (auth()->user()->isAdmin())
                    {{-- Расход (Офис) --}}
                    @if (isset($officeCategories) && $officeCategories->count())
                        <button type="button" id="openOfficeExpenseBtn"
                            class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white
            hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            Расход (Офис)
                        </button>
                    @endif

                    {{-- Зарплата (ЗП) --}}
                    @if (isset($salaryCategories) && $salaryCategories->count())
                        <button type="button" id="openSalaryExpenseBtn"
                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white
            hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 12v-2" />
                            </svg>
                            ЗП
                        </button>
                    @endif

                    {{-- Домен --}}
                    @if (isset($domainHostingCategories) && $domainHostingCategories->count())
                        <button type="button" id="openDomainHostingCategoriesBtn"
                            class="group inline-flex items-center gap-2 rounded-xl
                                bg-gradient-to-r from-indigo-600 to-violet-600
                                px-4 py-2 text-sm font-semibold text-white
                                shadow-md shadow-indigo-500/30
                                hover:from-indigo-700 hover:to-violet-700
                                hover:shadow-lg hover:shadow-indigo-500/40
                                focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                                transition-all duration-200">
                            {{-- Иконка домен + сервер --}}
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 transition-transform duration-200 hover:scale-110" fill="none"
                                viewBox="0 0 26 27" stroke="currentColor" stroke-width="2">
                                <path d="M2.33301 17.0908H23.6663" stroke="currentColor" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path d="M2.33301 9.09082H23.6663" stroke="currentColor" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path
                                    d="M13 25.0908C19.6274 25.0908 25 19.7182 25 13.0908C25 6.4634 19.6274 1.09082 13 1.09082C6.37258 1.09082 1 6.4634 1 13.0908C1 19.7182 6.37258 25.0908 13 25.0908Z"
                                    stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" />
                                <path
                                    d="M8.56543 14.0908C8.76021 17.3432 9.89724 20.3411 11.71 22.8145C11.5867 22.9501 11.4622 23.085 11.334 23.2158L11.2451 23.3057C9.30961 20.7035 8.09464 17.5335 7.89648 14.0908H8.56543ZM18.1045 14.0908C17.9063 17.5332 16.6911 20.7026 14.7559 23.3047L14.667 23.2148C14.5387 23.0839 14.4133 22.9501 14.29 22.8145C16.1029 20.341 17.2417 17.3435 17.4365 14.0908H18.1045ZM14.7549 2.87598C16.6906 5.47823 17.9063 8.64792 18.1045 12.0908H17.4365C17.2417 8.83789 16.1032 5.83978 14.29 3.36621C14.4132 3.23071 14.5389 3.09757 14.667 2.9668L14.7549 2.87598ZM11.334 2.9668C11.462 3.09741 11.587 3.23088 11.71 3.36621C9.89698 5.83969 8.76028 8.83812 8.56543 12.0908H7.89648C8.09468 8.64813 9.30967 5.47814 11.2451 2.87598L11.334 2.9668Z"
                                    fill="black" stroke="currentColor" stroke-width="2" />
                            </svg>

                            <span>Домены</span>
                        </button>
                    @endif


                    {{-- Выставить счёт --}}
                    <a href="{{ route('invoices.create') }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white
        hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6M7 4h10a2 2 0 012 2v14l-4-2-4 2-4-2-4 2V6a2 2 0 012-2z" />
                        </svg>
                        Выставить счёт
                    </a>
                @endif
            </div>

            <!-- Offcanvas: payment create -->
            <div id="paymentOffcanvas" class="fixed inset-0 z-50 hidden">
                <div class="fixed inset-0 bg-black bg-opacity-50" id="paymentOffcanvasBackdrop"></div>
                <div class="fixed right-0 top-0 h-full w-full sm:w-2/3 md:w-1/2 lg:w-1/3 bg-white transform translate-x-full transition-transform duration-300"
                    id="paymentOffcanvasPanel" role="dialog" aria-modal="true">
                    <div class="p-4 flex items-center justify-between border-b">
                        <h3 class="text-lg font-medium">Новый доход</h3>
                        <button id="closePaymentOffcanvas" class="text-gray-600 hover:text-gray-900">&times;</button>
                    </div>
                    <div id="paymentOffcanvasContent" class="p-4 overflow-auto h-full">
                        <div class="text-center text-gray-500">Загрузка...</div>
                    </div>
                </div>
            </div>

            <!-- Offcanvas: expense create -->
            <div id="expenseOffcanvas" class="fixed inset-0 z-50 hidden">
                <div class="fixed inset-0 bg-black bg-opacity-50" id="expenseOffcanvasBackdrop"></div>
                <div class="fixed right-0 top-0 h-full w-full sm:w-2/3 md:w-1/2 lg:w-1/3 bg-white transform translate-x-full transition-transform duration-300"
                    id="expenseOffcanvasPanel" role="dialog" aria-modal="true">
                    <div class="p-4 flex items-center justify-between border-b">
                        <h3 class="text-lg font-medium">Новый расход</h3>
                        <button id="closeExpenseOffcanvas" class="text-gray-600 hover:text-gray-900">&times;</button>
                    </div>
                    <div id="expenseOffcanvasContent" class="p-4 overflow-auto h-full">
                        <div class="text-center text-gray-500">Загрузка...</div>
                    </div>
                </div>
            </div>
        </div>


        {{-- Фильтры --}}
        <details class="mb-4 rounded shadow-sm bg-white">
            <summary
                class="cursor-pointer select-none px-4 py-3 font-semibold text-gray-800 flex items-center justify-between">
                <span>Фильтры</span>
                <svg class="h-4 w-4 text-gray-500 transition-transform duration-200" viewBox="0 0 20 20"
                    fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd"
                        d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06l-4.24 4.24a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08z"
                        clip-rule="evenodd" />
                </svg>
            </summary>
            <form method="GET" action="{{ route('operation.index') }}" class="p-4">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                    <!-- Period + Search -->
                    <div class="lg:col-span-4">
                        <div class="p-3 bg-gray-50 rounded border border-gray-100">
                            <div class="flex flex-wrap items-end gap-3">
                                <div class="w-40">
                                    <label class="text-xs text-gray-500">От</label>
                                    <input type="date" name="date_from"
                                        value="{{ request('date_from', request('month') ? \Illuminate\Support\Carbon::createFromFormat('Y-m', request('month'))->startOfMonth()->format('Y-m-d') : '') }}"
                                        class="w-full border-gray-200 rounded-md px-3 py-2 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                </div>

                                <div class="w-40">
                                    <label class="text-xs text-gray-500">До</label>
                                    <input type="date" name="date_to"
                                        value="{{ request('date_to', request('month') ? \Illuminate\Support\Carbon::createFromFormat('Y-m', request('month'))->endOfMonth()->format('Y-m-d') : '') }}"
                                        class="w-full border-gray-200 rounded-md px-3 py-2 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                </div>

                                <div class="flex-1 min-w-[180px]">
                                    <label class="text-xs text-gray-500">Поиск</label>
                                    <input type="search" name="q" value="{{ request('q') }}"
                                        placeholder="По описанию, счёту, заметке"
                                        class="w-full border-gray-200 rounded-md px-3 py-2 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                </div>

                                <div class="w-48">
                                    <label class="text-xs text-gray-500">Месяц</label>
                                    <select name="month" onchange="onMonthSelect(this.value)"
                                        class="w-full border-gray-200 rounded-md px-3 py-2 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="">— месяц —</option>

                                        @php
                                            // Use controller-provided $availableMonths if present, otherwise
                                            // derive months from the current $operations collection (shows only months with records)
                                            if (isset($availableMonths) && is_iterable($availableMonths)) {
                                                $monthsList = collect($availableMonths)->values();
                                            } else {
                                                $opsColl =
                                                    is_object($operations) && method_exists($operations, 'items')
                                                        ? collect($operations->items())
                                                        : collect($operations ?? []);

                                                $monthsList = $opsColl
                                                    ->map(function ($op) {
                                                        $date = $op['date'] ?? null;
                                                        if (!$date) {
                                                            return null;
                                                        }
                                                        try {
                                                            $c = \Illuminate\Support\Carbon::parse($date);
                                                            return $c->format('Y-m');
                                                        } catch (\Throwable $e) {
                                                            return null;
                                                        }
                                                    })
                                                    ->filter()
                                                    ->unique()
                                                    ->sortDesc()
                                                    ->values();
                                            }
                                        @endphp

                                        @foreach ($monthsList as $val)
                                            @php
                                                try {
                                                    $d = \Illuminate\Support\Carbon::createFromFormat(
                                                        'Y-m',
                                                        $val,
                                                    )->locale('ru');
                                                    $label = mb_convert_case(
                                                        $d->translatedFormat('F Y'),
                                                        MB_CASE_TITLE,
                                                        'UTF-8',
                                                    );
                                                } catch (\Throwable $e) {
                                                    $label = $val;
                                                }
                                            @endphp
                                            <option value="{{ $val }}" @selected(request('month') === $val)>
                                                {{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-2">
                                <button type="button"
                                    class="text-sm px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-100"
                                    onclick="setPreset('today')">Сегодня</button>
                                <button type="button"
                                    class="text-sm px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-100"
                                    onclick="setPreset('yesterday')">Вчера</button>
                                <button type="button"
                                    class="text-sm px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-100"
                                    onclick="setPreset('week')">Неделя</button>
                                <button type="button"
                                    class="text-sm px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-100"
                                    onclick="setPreset('month')">Месяц</button>
                                <button type="button"
                                    class="text-sm px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-100"
                                    onclick="setPreset('quarter')">Квартал</button>
                            </div>
                        </div>
                    </div>

                    <!-- Main filters -->
                    <div class="lg:col-span-8">
                        <div class="p-3 bg-gray-50 rounded border border-gray-100 grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div>
                                <label class="text-xs text-gray-500">Тип</label>
                                <select name="type"
                                    class="w-full border-gray-200 rounded-md px-3 py-2 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="all" @selected(request('type', 'all') === 'all')>Все</option>
                                    <option value="payment" @selected(request('type') === 'payment')>Платёж</option>
                                    <option value="expense" @selected(request('type') === 'expense')>Расход</option>
                                </select>
                            </div>

                            <div>
                                <label class="text-xs text-gray-500">Проект</label>
                                <select name="project_id"
                                    class="js-project-select w-full border-gray-200 rounded-md px-3 py-2 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">— все —</option>
                                    @foreach ($projects as $project)
                                        <option value="{{ $project->id }}" @selected((string) request('project_id') === (string) $project->id)>
                                            {{ $project->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="text-xs text-gray-500">Категория расхода</label>
                                <select name="expense_category_id"
                                    class="w-full border-gray-200 rounded-md px-3 py-2 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">— все —</option>
                                    @foreach ($expenseCategories as $cat)
                                        <option value="{{ $cat->id }}" @selected((string) request('expense_category_id') === (string) $cat->id)>
                                            {{ $cat->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="text-xs text-gray-500">Категория платёжа</label>
                                <select name="payment_category_id"
                                    class="w-full border-gray-200 rounded-md px-3 py-2 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">— все —</option>
                                    @foreach ($paymentCategories as $pc)
                                        <option value="{{ $pc->id }}" @selected((string) request('payment_category_id') === (string) $pc->id)>
                                            {{ $pc->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="text-xs text-gray-500">Статус расхода</label>
                                <select name="expense_status"
                                    class="w-full border-gray-200 rounded-md px-3 py-2 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">— все —</option>
                                    @foreach (\App\Models\Expense::STATUSES as $k => $v)
                                        <option value="{{ $k }}" @selected(request('expense_status') === $k)>
                                            {{ $v['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="text-xs text-gray-500">Источник / метод</label>
                                <select name="payment_method_id"
                                    class="w-full border-gray-200 rounded-md px-3 py-2 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">— все —</option>
                                    @foreach ($paymentMethods as $pm)
                                        <option value="{{ $pm->id }}" @selected((string) request('payment_method_id') === (string) $pm->id)>
                                            {{ $pm->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="text-xs text-gray-500">Счёт</label>
                                <select name="bank_account_id"
                                    class="w-full border-gray-200 rounded-md px-3 py-2 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">— все —</option>
                                    @foreach ($bankAccounts as $ba)
                                        <option value="{{ $ba->id }}" @selected((string) request('bank_account_id') === (string) $ba->id)>
                                            {{ $ba->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="text-xs text-gray-500">Кто создал</label>
                                <select name="created_by"
                                    class="w-full border-gray-200 rounded-md px-3 py-2 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">— все —</option>
                                    @foreach ($users as $u)
                                        <option value="{{ $u->id }}" @selected((string) request('created_by') === (string) $u->id)>
                                            {{ $u->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="text-xs text-gray-500">Проектность</label>
                                <select name="has_project"
                                    class="w-full border-gray-200 rounded-md px-3 py-2 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">— все —</option>
                                    <option value="1" @selected(request('has_project') === '1')>С проектом</option>
                                    <option value="0" @selected(request('has_project') === '0')>Без проекта</option>
                                </select>
                            </div>

                            <div>
                                <label class="text-xs text-gray-500">Сортировать по сумме</label>
                                <select name="sort_amount"
                                    class="w-full border-gray-200 rounded-md px-3 py-2 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">— по дате —</option>
                                    <option value="asc" @selected(request('sort_amount') === 'asc')>По возрастанию</option>
                                    <option value="desc" @selected(request('sort_amount') === 'desc')>По убыванию</option>
                                </select>
                            </div>

                            <div class="md:col-span-3 flex items-center gap-3">
                                <div class="flex items-center gap-3">
                                    <label class="text-xs text-gray-500">Флаги</label>
                                    <label class="inline-flex items-center gap-2 text-sm">
                                        <input type="radio" name="expense_flag" value=""
                                            @checked(!request('expense_flag'))>
                                        Все
                                    </label>
                                    <label class="inline-flex items-center gap-2 text-sm">
                                        <input type="radio" name="expense_flag" value="salary"
                                            @checked(request('expense_flag') === 'salary')>
                                        ЗП
                                    </label>
                                    <label class="inline-flex items-center gap-2 text-sm">
                                        <input type="radio" name="expense_flag" value="office"
                                            @checked(request('expense_flag') === 'office')>
                                        Офис
                                    </label>
                                    <label class="inline-flex items-center gap-2 text-sm">
                                        <input type="radio" name="expense_flag" value="domains"
                                            @checked(request('expense_flag') === 'domains')>
                                        Домены
                                    </label>
                                </div>

                                <div class="flex items-center gap-2 ml-auto">
                                    <label class="text-xs text-gray-500">Сумма от / до</label>
                                    <input type="number" step="0.01" name="amount_min"
                                        value="{{ request('amount_min') }}"
                                        class="border-gray-200 rounded-md px-3 py-2 text-sm w-32 bg-white"
                                        placeholder="min">
                                    <input type="number" step="0.01" name="amount_max"
                                        value="{{ request('amount_max') }}"
                                        class="border-gray-200 rounded-md px-3 py-2 text-sm w-32 bg-white"
                                        placeholder="max">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-3 flex items-center gap-2">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded text-sm">Применить</button>
                    <a href="{{ route('operation.index') }}" class="px-4 py-2 border rounded text-sm">Сброс</a>

                    <div class="ml-auto text-sm text-gray-700">
                        <span class="font-semibold">Доход:</span> {{ number_format($sumIncome ?? 0, 2, '.', ' ') }} ₽
                        &nbsp;•&nbsp;
                        <span class="font-semibold">Расход:</span> {{ number_format($sumExpense ?? 0, 2, '.', ' ') }} ₽
                        &nbsp;•&nbsp;
                        <span class="font-semibold">Баланс:</span>
                        {{ number_format(($sumIncome ?? 0) - ($sumExpense ?? 0), 2, '.', ' ') }} ₽
                    </div>
                </div>
            </form>
        </details>

        <script>
            function setPreset(preset) {
                const from = document.querySelector('input[name="date_from"]');
                const to = document.querySelector('input[name="date_to"]');
                if (!from || !to) return;

                const today = new Date();
                const start = new Date(today);
                const end = new Date(today);

                if (preset === 'today') {
                    // today
                } else if (preset === 'yesterday') {
                    start.setDate(start.getDate() - 1);
                    end.setDate(end.getDate() - 1);
                } else if (preset === 'week') {
                    const day = (start.getDay() + 6) % 7; // Monday=0
                    start.setDate(start.getDate() - day);
                    end.setDate(start.getDate() + 6);
                } else if (preset === 'month') {
                    start.setDate(1);
                    end.setMonth(end.getMonth() + 1, 0);
                } else if (preset === 'quarter') {
                    const q = Math.floor(start.getMonth() / 3);
                    start.setMonth(q * 3, 1);
                    end.setMonth(q * 3 + 3, 0);
                }

                const fmt = (d) => {
                    const y = d.getFullYear();
                    const m = String(d.getMonth() + 1).padStart(2, '0');
                    const day = String(d.getDate()).padStart(2, '0');
                    return `${y}-${m}-${day}`;
                };

                from.value = fmt(start);
                to.value = fmt(end);

                // clear month selector when using date presets
                const monthSel = document.querySelector('select[name="month"]');
                if (monthSel) monthSel.value = '';

                const form = from.closest('form');
                if (form) {
                    if (typeof form.requestSubmit === 'function') {
                        form.requestSubmit();
                    } else {
                        form.submit();
                    }
                }
            }

            function onMonthSelect(value) {
                const from = document.querySelector('input[name="date_from"]');
                const to = document.querySelector('input[name="date_to"]');
                if (!from || !to) return;

                if (!value) {
                    from.value = '';
                    to.value = '';
                    return;
                }

                const parts = value.split('-');
                if (parts.length !== 2) return;
                const y = parseInt(parts[0], 10);
                const m = parseInt(parts[1], 10);
                const start = new Date(y, m - 1, 1);
                const end = new Date(y, m, 0);

                const fmt = (d) => {
                    const y = d.getFullYear();
                    const mm = String(d.getMonth() + 1).padStart(2, '0');
                    const dd = String(d.getDate()).padStart(2, '0');
                    return `${y}-${mm}-${dd}`;
                };

                from.value = fmt(start);
                to.value = fmt(end);

                const form = from.closest('form');
                if (form) {
                    if (typeof form.requestSubmit === 'function') {
                        form.requestSubmit();
                    } else {
                        form.submit();
                    }
                }
            }
        </script>

        <div class="bg-white shadow rounded p-4">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-100 text-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left">Время</th>
                        <th class="px-4 py-3 text-left">Тип</th>
                        <th class="px-4 py-3 text-left">Проект</th>
                        <th class="px-4 py-3 text-left">Описание</th>
                        <th class="px-4 py-3 text-right">Сумма</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php
                        $items =
                            is_object($operations) && method_exists($operations, 'items')
                                ? collect($operations->items())
                                : collect($operations);
                        $groups = $items->groupBy(fn($op) => optional($op['date'])->format('Y-m-d') ?? 'Без даты');
                    @endphp

                    @foreach ($groups as $date => $rows)
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
                            <td colspan="6" class="bg-indigo-50 py-2">
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

                        @foreach ($rows as $op)
                            @php $m = $op['model']; @endphp
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-2 font-medium text-gray-700">
                                    {{ optional($op['date'])->format('H:i') ?? '-' }}</td>
                                <td class="px-4 py-2">
                                    @if ($op['type'] === 'payment')
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-full bg-green-100 text-green-700 text-xs font-semibold shadow-sm">
                                            Поступление
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-full bg-red-100 text-red-700 text-xs font-semibold shadow-sm">
                                            Расход
                                        </span>
                                        @php
                                            $buttonBaseClasses =
                                                'inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all duration-200';
                                        @endphp

                                        @if ($m->category?->is_salary)
                                            <span
                                                class="{{ $buttonBaseClasses }} bg-gradient-to-r from-red-500 to-pink-500 text-white shadow-red-500/30 hover:from-red-600 hover:to-pink-600 hover:shadow-red-500/40 focus:ring-red-500"
                                                title="Расход на ЗП">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="h-4 w-4 transition-transform duration-200 hover:scale-110"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                    stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 12v-2" />
                                                </svg>
                                            </span>
                                        @elseif ($m->category?->is_office)
                                            <span
                                                class="{{ $buttonBaseClasses }} bg-gradient-to-r from-emerald-500 to-green-500 text-white shadow-emerald-500/30 hover:from-emerald-600 hover:to-green-600 hover:shadow-emerald-500/40 focus:ring-emerald-500"
                                                title="Офисный расход">
                                                <svg class="h-4 w-4 transition-transform duration-200 hover:scale-110"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                    stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                </svg>
                                            </span>
                                        @elseif ($m->category?->is_domains_hosting)
                                            <span
                                                class="{{ $buttonBaseClasses }} bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow-indigo-500/30 hover:from-indigo-700 hover:to-violet-700 hover:shadow-indigo-500/40 focus:ring-indigo-500"
                                                title="Оплата доменов и хостинга">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="h-4 w-4 transition-transform duration-200 hover:scale-110"
                                                    fill="none" viewBox="0 0 26 27" stroke="currentColor"
                                                    stroke-width="2">
                                                    <path d="M2.33301 17.0908H23.6663" stroke="currentColor"
                                                        stroke-linecap="round" stroke-linejoin="round" />
                                                    <path d="M2.33301 9.09082H23.6663" stroke="currentColor"
                                                        stroke-linecap="round" stroke-linejoin="round" />
                                                    <path
                                                        d="M13 25.0908C19.6274 25.0908 25 19.7182 25 13.0908C25 6.4634 19.6274 1.09082 13 1.09082C6.37258 1.09082 1 6.4634 1 13.0908C1 19.7182 6.37258 25.0908 13 25.0908Z"
                                                        stroke="currentColor" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                    <path
                                                        d="M8.56543 14.0908C8.76021 17.3432 9.89724 20.3411 11.71 22.8145C11.5867 22.9501 11.4622 23.085 11.334 23.2158L11.2451 23.3057C9.30961 20.7035 8.09464 17.5335 7.89648 14.0908H8.56543ZM18.1045 14.0908C17.9063 17.5332 16.6911 20.7026 14.7559 23.3047L14.667 23.2148C14.5387 23.0839 14.4133 22.9501 14.29 22.8145C16.1029 20.341 17.2417 17.3435 17.4365 14.0908H18.1045ZM14.7549 2.87598C16.6906 5.47823 17.9063 8.64792 18.1045 12.0908H17.4365C17.2417 8.83789 16.1032 5.83978 14.29 3.36621C14.4132 3.23071 14.5389 3.09757 14.667 2.9668L14.7549 2.87598ZM11.334 2.9668C11.462 3.09741 11.587 3.23088 11.71 3.36621C9.89698 5.83969 8.76028 8.83812 8.56543 12.0908H7.89648C8.09468 8.64813 9.30967 5.47814 11.2451 2.87598L11.334 2.9668Z"
                                                        fill="black" stroke="currentColor" stroke-width="2" />
                                                </svg>
                                            </span>
                                        @endif

                                        {{-- Статус расхода (только для строк-расходов) --}}
                                        @if ($op['type'] === 'expense')
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-{{ $m->status_color }}-100 text-{{ $m->status_color }}-700 ml-2">
                                                {{ $m->status_label }}
                                            </span>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-gray-700 font-medium">
                                    @if ($m->project)
                                        @can('view', $m->project)
                                            <a href="{{ route('projects.show', $m->project) }}"
                                                class="text-blue-600 hover:text-blue-800 hover:underline">
                                                {{ $m->project->title }}
                                            </a>
                                        @else
                                            <span class="text-gray-400">
                                                {{ $m->project->title }}
                                            </span>
                                        @endcan
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-2 max-w-xs truncate text-gray-600">
                                    @if ($op['type'] === 'expense' && ($m->category?->is_domains_hosting || $m->domain_id))
                                        <span class="inline-flex items-center gap-1 text-indigo-600 mr-2"
                                            title="Оплата домена">

                                            <span class="text-xs">Оплата за
                                                домен{{ $m->domain?->name ? ': ' . $m->domain?->name : '' }}</span>
                                        </span>
                                    @endif
                                    {{ $op['type'] === 'payment' ? $m->note ?? '' : $m->description ?? '' }}
                                    @if ($op['type'] === 'expense' && $m->category?->is_salary && $m->salary_recipient)
                                        <div class="mt-1 text-xs text-gray-500">Кому:
                                            {{ optional($m->salaryRecipient)->name ?? $m->salary_recipient }}</div>
                                    @endif
                                </td>
                                <td
                                    class="px-4 py-2 text-right font-semibold {{ $op['type'] === 'payment' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format($op['amount'], 2, '.', ' ') }} ₽
                                </td>
                                <td class="px-4 py-2 text-right">
                                    <a href="{{ $op['type'] === 'payment' ? route('payments.show', $m) : route('expenses.show', $m) }}"
                                        class="text-indigo-600 hover:text-indigo-800 font-medium transition">Открыть</a>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>


            <div class="mt-4">
                {{ $operations->links() }}
            </div>
        </div>
    </div>

    <script>
        (function() {
            const openBtn = document.getElementById('openPaymentOffcanvas');
            if (!openBtn) return;
            const panel = document.getElementById('paymentOffcanvasPanel');
            const container = document.getElementById('paymentOffcanvas');
            const content = document.getElementById('paymentOffcanvasContent');
            const backdrop = document.getElementById('paymentOffcanvasBackdrop');
            const closeBtn = document.getElementById('closePaymentOffcanvas');

            function open(url) {
                container.classList.remove('hidden');
                setTimeout(() => panel.classList.remove('translate-x-full'), 20);
                // fetch form (AJAX)
                fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html'
                        }
                    }).then(r => r.text())
                    .then(html => {
                        content.innerHTML = html;

                        // Инициализация Tom Select для загруженной формы
                        if (typeof window.initTomSelect === 'function') {
                            window.initTomSelect(content);
                        }

                        // Execute inline scripts from the loaded HTML (so initPaymentForm is defined)
                        const scripts = content.querySelectorAll('script');
                        scripts.forEach(s => {
                            try {
                                if (!s.src) {
                                    // Evaluate inline script content
                                    (0, eval)(s.textContent);
                                }
                            } catch (err) {
                                console.error('Error executing script from loaded form:', err);
                            }
                        });

                        // initialize payment form (if partial provided initialization function)
                        if (typeof initPaymentForm === 'function') {
                            try {
                                initPaymentForm(content);
                            } catch (e) {
                                console.error(e);
                            }
                        }

                        attachFormHandler();
                    })
                    .catch(() => {
                        content.innerHTML = '<div class="text-red-600">Ошибка загрузки формы</div>';
                    });
            }

            function close() {
                panel.classList.add('translate-x-full');
                setTimeout(() => container.classList.add('hidden'), 300);
            }

            function attachFormHandler() {
                const form = content.querySelector('form');
                if (!form) return;
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    // remove previous errors
                    content.querySelectorAll('.error-text').forEach(n => n.remove());

                    const fd = new FormData(form);
                    const action = form.getAttribute('action') || '{{ route('payments.store') }}';
                    fetch(action, {
                        method: form.getAttribute('method') || 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: fd,
                    }).then(async r => {
                        if (r.ok && (r.status === 201 || r.status === 200)) {
                            const json = await r.json();
                            close();
                            if (json.redirect) {
                                window.location.href = json.redirect;
                            } else {
                                window.location.reload();
                            }
                        } else if (r.status === 422) {
                            const json = await r.json();
                            const errs = json.errors || {};
                            Object.keys(errs).forEach(k => {
                                const el = form.querySelector('[name="' + k + '"]');
                                if (el) {
                                    let node = el.nextElementSibling;
                                    if (!node || !node.classList.contains('error-text')) {
                                        node = document.createElement('div');
                                        node.classList.add('text-sm', 'text-red-600',
                                            'error-text');
                                        el.after(node);
                                    }
                                    node.textContent = errs[k].join(', ');
                                }
                            });
                        } else {
                            const txt = await r.text();
                            content.innerHTML =
                                '<div class="text-red-600">Ошибка отправки формы</div>';
                        }
                    }).catch(() => {
                        content.innerHTML = '<div class="text-red-600">Ошибка сети</div>';
                    });
                });

                // attach cancel inside content
                const cancel = content.querySelector('#paymentOffcanvasCancel');
                if (cancel) {
                    cancel.addEventListener('click', function() {
                        backdrop.click();
                    });
                }
            }

            openBtn.addEventListener('click', function(e) {
                e.preventDefault();
                open(this.dataset.url);
            });
            if (closeBtn) closeBtn.addEventListener('click', close);
            if (backdrop) backdrop.addEventListener('click', close);
        })();

        // Expense Offcanvas Logic
        (function() {
            const openBtn = document.getElementById('openExpenseOffcanvas');
            if (!openBtn) return;
            const panel = document.getElementById('expenseOffcanvasPanel');
            const container = document.getElementById('expenseOffcanvas');
            const content = document.getElementById('expenseOffcanvasContent');
            const backdrop = document.getElementById('expenseOffcanvasBackdrop');
            const closeBtn = document.getElementById('closeExpenseOffcanvas');

            function open(url) {
                container.classList.remove('hidden');
                setTimeout(() => panel.classList.remove('translate-x-full'), 20);
                // fetch form (AJAX)
                fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html'
                        }
                    }).then(r => r.text())
                    .then(html => {
                        content.innerHTML = html;

                        // Инициализация Tom Select для загруженной формы
                        if (typeof window.initTomSelect === 'function') {
                            window.initTomSelect(content);
                        }

                        // Execute inline scripts from the loaded HTML
                        const scripts = content.querySelectorAll('script');
                        scripts.forEach(s => {
                            try {
                                if (!s.src) {
                                    (0, eval)(s.textContent);
                                }
                            } catch (err) {
                                console.error('Error executing script from loaded form:', err);
                            }
                        });

                        attachFormHandler();
                    })
                    .catch(() => {
                        content.innerHTML = '<div class="text-red-600">Ошибка загрузки формы</div>';
                    });
            }

            function close() {
                panel.classList.add('translate-x-full');
                setTimeout(() => container.classList.add('hidden'), 300);
            }

            function attachFormHandler() {
                const form = content.querySelector('form');
                if (!form) return;
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    // remove previous errors
                    content.querySelectorAll('.error-text').forEach(n => n.remove());

                    const fd = new FormData(form);
                    const action = form.getAttribute('action') || '{{ route('expenses.store') }}';
                    fetch(action, {
                        method: form.getAttribute('method') || 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: fd,
                    }).then(async r => {
                        if (r.ok && (r.status === 201 || r.status === 200)) {
                            const json = await r.json();
                            close();
                            if (json.redirect) {
                                window.location.href = json.redirect;
                            } else {
                                window.location.reload();
                            }
                        } else if (r.status === 422) {
                            const json = await r.json();
                            const errs = json.errors || {};
                            Object.keys(errs).forEach(k => {
                                const el = form.querySelector('[name="' + k + '"]');
                                if (el) {
                                    let node = el.nextElementSibling;
                                    if (!node || !node.classList.contains('error-text')) {
                                        node = document.createElement('div');
                                        node.classList.add('text-sm', 'text-red-600',
                                            'error-text');
                                        el.after(node);
                                    }
                                    node.textContent = errs[k].join(', ');
                                }
                            });
                        } else {
                            const txt = await r.text();
                            content.innerHTML =
                                '<div class="text-red-600">Ошибка отправки формы</div>';
                        }
                    }).catch(() => {
                        content.innerHTML = '<div class="text-red-600">Ошибка сети</div>';
                    });
                });

                // attach cancel inside content
                const cancel = content.querySelector('#expenseOffcanvasCancel');
                if (cancel) {
                    cancel.addEventListener('click', function() {
                        backdrop.click();
                    });
                }
            }

            openBtn.addEventListener('click', function(e) {
                e.preventDefault();
                open(this.dataset.url);
            });
            if (closeBtn) closeBtn.addEventListener('click', close);
            if (backdrop) backdrop.addEventListener('click', close);
        })();
    </script>

    {{-- Модальное окно для офисного расхода --}}
    @if (isset($officeCategories) && $officeCategories->count())
        @include('admin.expenses._office_modal')
    @endif

    {{-- Модальное окно для зарплатного расхода --}}
    @if (isset($salaryCategories) && $salaryCategories->count())
        @include('admin.expenses._salary_modal')
    @endif

    {{-- Модальное окно для доменов/хостинга --}}
    @if (isset($domainHostingCategories) && $domainHostingCategories->count())
        @include('admin.expenses._domain_hosting_modal')
    @endif

    {{-- Модальное окно для не наших расходов --}}
    @if (isset($notOurExpenseCategories) && $notOurExpenseCategories->count())
        @include('admin.expenses._not_our_modal')
    @endif
@endsection
