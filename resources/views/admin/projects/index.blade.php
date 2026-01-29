@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold">Проекты</h1>
            @can('create', \App\Models\Project::class)
                <a href="{{ route('projects.create') }}"
                    class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Создать
                    проект</a>
            @endcan
        </div>

        <div class="bg-white shadow rounded">
            <div class="p-4 border-b">
                <div class="flex flex-wrap items-center gap-2 mb-3">
                    <div class="text-xs text-gray-500 mr-2">Быстрый выбор:</div>
                    @php
                        $baseParams = request()->except(['page', 'importance']);
                    @endphp
                    <a href="{{ route('projects.index', $baseParams) }}"
                        class="px-3 py-1 rounded-full text-xs border {{ empty($importance) ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-700 border-gray-200 hover:border-gray-400' }}">
                        Все
                    </a>
                    @foreach ($importancesList ?? [] as $imp)
                        @php
                            $isActive = (string) ($importance ?? '') === (string) $imp->id;
                            $bg = $imp->color ?: '#E5E7EB';
                            $text = $imp->color ? '#FFFFFF' : '#374151';
                        @endphp
                        <a href="{{ route('projects.index', array_merge($baseParams, ['importance' => $imp->id])) }}"
                            class="px-3 py-1 rounded-full text-xs border transition"
                            style="background-color: {{ e($bg) }}; color: {{ e($text) }}; border-color: {{ $isActive ? e($bg) : '#E5E7EB' }}; opacity: {{ $isActive ? '1' : '0.85' }};">
                            {{ $imp->name }}
                        </a>
                    @endforeach
                </div>

                <div class="flex items-center gap-4">
                    <details class="mb-0 w-full rounded shadow-sm bg-white">
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

                        <form method="GET" action="{{ route('projects.index') }}" class="p-4">
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                                <div class="lg:col-span-4">
                                    <div class="p-3 bg-gray-50 rounded border border-gray-100">
                                        <div class="flex flex-wrap items-end gap-3">
                                            <div class="w-full">
                                                <label class="text-xs text-gray-500">Поиск</label>
                                                <input type="search" name="q" value="{{ $q ?? '' }}"
                                                    placeholder="Поиск по названию..."
                                                    class="w-full border-gray-200 rounded-md px-3 py-2 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500" />
                                            </div>

                                            <div class="w-40">
                                                <label class="text-xs text-gray-500">Дата контракта</label>
                                                <input type="date" name="contract_date"
                                                    value="{{ $contract_date ?? '' }}"
                                                    class="w-full border-gray-200 rounded-md px-3 py-2 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500" />
                                            </div>
                                        </div>

                                        <div class="mt-3 flex flex-wrap gap-2">
                                            <button type="button"
                                                class="text-sm px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-100"
                                                onclick="this.form && (this.form.contract_date.value = '')">Все</button>
                                            <button type="button"
                                                class="text-sm px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-100"
                                                onclick="document.querySelector('[name=contract_date]').value = new Date().toISOString().slice(0,10)">Сегодня</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="lg:col-span-8">
                                    <div
                                        class="p-3 bg-gray-50 rounded border border-gray-100 grid grid-cols-1 md:grid-cols-3 gap-3">
                                        @if (auth()->user()->isAdmin() || auth()->user()->isProjectManager())
                                            <div>
                                                <label class="text-xs text-gray-500">Организация</label>
                                                <select name="organization"
                                                    class="w-full border-gray-200 rounded-md px-3 py-2 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                                    <option value="">— все —</option>
                                                    @foreach ($organizations as $id => $name)
                                                        <option value="{{ $id }}" @selected((string) ($org ?? '') === (string) $id)>
                                                            {{ $name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div>
                                                <label class="text-xs text-gray-500">Маркетолог</label>
                                                <select name="marketer"
                                                    class="w-full border-gray-200 rounded-md px-3 py-2 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                                    <option value="">— все —</option>
                                                    @foreach ($marketers as $id => $name)
                                                        <option value="{{ $id }}" @selected((string) ($marketer ?? '') === (string) $id)>
                                                            {{ $name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif

                                        @if (auth()->user()->isAdmin())
                                            <div>
                                                <label class="text-xs text-gray-500">Статус баланса</label>
                                                <select name="balance_status"
                                                    class="w-full border-gray-200 rounded-md px-3 py-2 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                                    <option value="">— все —</option>
                                                    <option value="debt" @selected((string) ($balance_status ?? '') === 'debt')>Должники</option>
                                                    <option value="paid" @selected((string) ($balance_status ?? '') === 'paid')>Оплачено</option>
                                                    <option value="overpaid" @selected((string) ($balance_status ?? '') === 'overpaid')>Переплата</option>
                                                </select>
                                            </div>
                                        @endif

                                        <div>
                                            <label class="text-xs text-gray-500">Важность</label>
                                            <select name="importance"
                                                class="w-full border-gray-200 rounded-md px-3 py-2 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                                <option value="">— все —</option>
                                                @foreach ($importances ?? [] as $id => $name)
                                                    <option value="{{ $id }}" @selected((string) ($importance ?? '') === (string) $id)>
                                                        {{ $name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="md:col-span-1">
                                            <label class="text-xs text-gray-500">Сортировка дня</label>
                                            <div class="flex items-center gap-2 mt-2">
                                                <button type="submit" name="sort_due" value="asc"
                                                    class="text-sm px-3 py-1 rounded {{ (string) ($sort_due ?? '') === 'asc' ? 'bg-gray-900 text-white' : 'bg-white border border-gray-200' }}">По
                                                    возрастанию</button>
                                                <button type="submit" name="sort_due" value="desc"
                                                    class="text-sm px-3 py-1 rounded {{ (string) ($sort_due ?? '') === 'desc' ? 'bg-gray-900 text-white' : 'bg-white border border-gray-200' }}">По
                                                    убыванию</button>
                                            </div>
                                        </div>

                                        <div class="md:col-span-2 md:flex md:items-end md:justify-end">
                                            <div class="w-full">
                                                <div class="flex items-center gap-2">
                                                    <button type="submit"
                                                        class="inline-flex items-center px-3 py-2 bg-gray-900 text-white rounded">Применить</button>
                                                    <a href="{{ route('projects.index') }}"
                                                        class="text-sm text-gray-500">Сброс</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </details>

                    <div class="ml-auto text-sm text-gray-500">Всего: {{ $projects->total() }}</div>
                </div>
            </div>

            <div class="divide-y">
                @forelse($projects as $project)
                    <div class="flex items-center gap-4 px-4 py-3 hover:bg-gray-50">
                        <div class="w-36 text-center">
                            @if (auth()->user()->isAdmin())
                                <div class="text-xs text-gray-500">День оплаты</div>
                            @else
                                <div class="text-xs text-gray-500">День отчета</div>
                            @endif
                            <div class="text-sm text-gray-700 mt-1">
                                {{ $project->payment_due_day ?? ($project->contract_date ? $project->contract_date->day : '—') }}
                            </div>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <div>
                                    <a href="{{ route('projects.show', $project) }}"
                                        class="font-medium text-gray-900">{{ $project->title }}</a>
                                    @if (!empty($project->importance?->name))
                                        @php
                                            $impColor = $project->importance?->color;
                                            $impBg = $impColor ?: '#E5E7EB';
                                            $impText = $impColor ? '#FFFFFF' : '#374151';
                                        @endphp
                                        <span
                                            class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                            style="background-color: {{ e($impBg) }}; color: {{ e($impText) }};">
                                            {{ $project->importance->name }}
                                        </span>
                                    @endif
                                    <div class="text-xs text-gray-500">
                                        {{ $project->organization?->name_short ?? ($project->organization?->name_full ?? '-') }}
                                        • {{ $project->city ?? '-' }}
                                    </div>
                                </div>

                                <div class="text-right">
                                    <div class="text-sm text-gray-500">{{ $project->marketer?->name ?? '-' }}</div>
                                    <div class="text-sm text-gray-400 mt-1">
                                        @php
                                            // Рассчитываем баланс: платежи - счета
                                            $invoicesTotal = $project->invoices_total;
                                            $paymentsTotal = $project->payments_total;
                                            $bal = $paymentsTotal - $invoicesTotal;
                                            $hasInvoices = $invoicesTotal > 0;
                                        @endphp

                                        {{-- Пометка закрытого / закрывающегося проекта --}}
                                        @if (!empty($project->closed_at))
                                            @php
                                                $closedDate = \Illuminate\Support\Carbon::make(
                                                    $project->closed_at,
                                                )->startOfDay();
                                                $today = \Illuminate\Support\Carbon::today();
                                                $tomorrow = $today->copy()->addDay();
                                                $inSevenDays = $today->copy()->addDays(7);
                                            @endphp

                                            @if ($closedDate->lt($today))
                                                <button type="button"
                                                    class="inline-flex items-center px-2 py-1 bg-black text-white rounded text-sm mr-2"
                                                    data-tippy
                                                    data-tippy-content="Дата закрытия: {{ $closedDate->format('Y-m-d') }}">
                                                    Закрыт
                                                </button>
                                            @elseif ($closedDate->isSameDay($today))
                                                <button type="button"
                                                    class="inline-flex items-center px-2 py-1 bg-gray-200 text-gray-800 rounded text-sm mr-2"
                                                    data-tippy
                                                    data-tippy-content="Дата закрытия: {{ $closedDate->format('Y-m-d') }}">
                                                    Сегодня закрытия
                                                </button>
                                            @elseif ($closedDate->isSameDay($tomorrow))
                                                <button type="button"
                                                    class="inline-flex items-center px-2 py-1 bg-gray-200 text-gray-800 rounded text-sm mr-2"
                                                    data-tippy
                                                    data-tippy-content="Дата закрытия: {{ $closedDate->format('Y-m-d') }}">
                                                    Остался 1 день до закрытия
                                                </button>
                                            @elseif ($closedDate->lte($inSevenDays))
                                                <button type="button"
                                                    class="inline-flex items-center px-2 py-1 bg-gray-200 text-gray-800 rounded text-sm mr-2"
                                                    data-tippy
                                                    data-tippy-content="Дата закрытия: {{ $closedDate->format('Y-m-d') }}">
                                                    На стадии закрытия
                                                </button>
                                            @endif
                                        @endif

                                        {{-- Пометка бартерного проекта --}}
                                        @if (isset($project->payment_type) && $project->payment_type === 'barter')
                                            <button type="button"
                                                class="inline-flex items-center px-2 py-1 bg-yellow-500 text-white rounded text-sm mr-2"
                                                data-tippy
                                                data-tippy-content="Бартерный проект — счета не выставляются автоматически">
                                                Бартер
                                            </button>
                                        @endif

                                        {{-- Пометка своих проектов --}}
                                        @if (isset($project->payment_type) && $project->payment_type === 'own')
                                            <button type="button"
                                                class="inline-flex items-center px-2 py-1 bg-indigo-600 text-white rounded text-sm mr-2"
                                                data-tippy
                                                data-tippy-content="Свой проект — счета не выставляются автоматически">
                                                Свой проект
                                            </button>
                                        @endif
                                        @if (auth()->user()->isAdmin())
                                            @if ($hasInvoices && $bal < 0)
                                                {{-- Долг: счета > платежей --}}
                                                <button type="button"
                                                    class="inline-flex items-center px-2 py-1 bg-red-600 text-white rounded text-sm"
                                                    data-tippy
                                                    data-tippy-content="Счета: {{ number_format($invoicesTotal, 0, '.', ' ') }} ₽<br>Оплачено: {{ number_format($paymentsTotal, 0, '.', ' ') }} ₽<br>Долг: {{ number_format(abs($bal), 0, '.', ' ') }} ₽">
                                                    Долг
                                                </button>
                                            @elseif ($hasInvoices && $bal > 0)
                                                {{-- Переплата: платежей > счетов --}}
                                                <button type="button"
                                                    class="inline-flex items-center px-2 py-1 bg-green-600 text-white rounded text-sm"
                                                    data-tippy
                                                    data-tippy-content="Счета: {{ number_format($invoicesTotal, 0, '.', ' ') }} ₽<br>Оплачено: {{ number_format($paymentsTotal, 0, '.', ' ') }} ₽<br>Переплата: {{ number_format($bal, 0, '.', ' ') }} ₽">
                                                    Переплата
                                                </button>
                                            @elseif ($hasInvoices && round($bal, 2) == 0)
                                                {{-- Оплачено: счета = платежам --}}
                                                <button type="button"
                                                    class="inline-flex items-center px-2 py-1 bg-green-600 text-white rounded text-sm"
                                                    data-tippy
                                                    data-tippy-content="Счета: {{ number_format($invoicesTotal, 0, '.', ' ') }} ₽<br>Оплачено: {{ number_format($paymentsTotal, 0, '.', ' ') }} ₽">
                                                    Оплачено
                                                </button>
                                            @elseif (!$hasInvoices && $paymentsTotal > 0)
                                                {{-- Нет счетов, но есть платежи --}}
                                                <button type="button"
                                                    class="inline-flex items-center px-2 py-1 bg-blue-600 text-white rounded text-sm"
                                                    data-tippy
                                                    data-tippy-content="Нет счетов<br>Оплачено: {{ number_format($paymentsTotal, 0, '.', ' ') }} ₽">
                                                    Без счёта
                                                </button>
                                            @else
                                                <span class="text-sm text-gray-400">—</span>
                                            @endif
                                        @endif

                                    </div>
                                </div>
                            </div>

                            @if ($project->stages->count())
                                <div class="text-xs text-gray-500 mt-2">
                                    Виды продвижения: {{ $project->stages->pluck('name')->join(' • ') }}
                                </div>
                            @endif
                        </div>

                        <div class="flex items-center gap-2">
                            @can('view', $project)
                                <a href="{{ route('projects.show', $project) }}"
                                    class="text-indigo-600 hover:text-indigo-800 text-sm p-2">Просмотр</a>
                            @endcan

                            @can('update', $project)
                                <a href="{{ route('projects.edit', $project) }}"
                                    class="text-indigo-600 hover:text-indigo-800 text-sm p-2">Редактировать</a>
                            @endcan

                            @can('delete', $project)
                                <form action="{{ route('projects.destroy', $project) }}" method="POST"
                                    onsubmit="return confirm('Удалить проект?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 hover:text-red-800 text-sm p-2">Удалить</button>
                                </form>
                            @endcan
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-6 text-gray-500">Пока нет проектов.</div>
                @endforelse
            </div>

            <div class="p-4">
                {{ $projects->links() }}
            </div>
        </div>
    </div>
@endsection
