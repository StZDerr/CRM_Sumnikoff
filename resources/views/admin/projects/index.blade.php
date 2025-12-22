@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold">Проекты</h1>
            <a href="{{ route('projects.create') }}"
                class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Создать
                проект</a>
        </div>

        <div class="bg-white shadow rounded">
            <div class="p-4 border-b flex items-center gap-4">
                <form method="GET" action="{{ route('projects.index') }}" class="flex flex-wrap items-center gap-2">
                    <input type="search" name="q" value="{{ $q ?? '' }}" placeholder="Поиск по названию..."
                        class="border rounded px-3 py-2 w-72 text-sm" />
                    <select name="organization" class="border rounded px-3 py-2 text-sm w-36">
                        <option value="">Организации</option>
                        @foreach ($organizations as $id => $name)
                            <option value="{{ $id }}" @selected((string) ($org ?? '') === (string) $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                    <select name="importance" class="border rounded px-3 py-2 text-sm w-36">
                        <option value="">Важность</option>
                        @foreach ($importances ?? [] as $id => $name)
                            <option value="{{ $id }}" @selected((string) ($importance ?? '') === (string) $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                    <input type="date" name="contract_date" class="w-36 border rounded px-3 py-2 text-sm"
                        value="{{ $contract_date ?? '' }}" /> <select name="marketer"
                        class="border rounded px-3 py-2 text-sm w-36">
                        <option value="">Маркетологи</option>
                        @foreach ($marketers as $id => $name)
                            <option value="{{ $id }}" @selected((string) ($marketer ?? '') === (string) $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                    <select name="balance_status" class="border rounded px-3 py-2 text-sm w-36">
                        <option value="">Статус баланса</option>
                        <option value="debt" @selected((string) ($balance_status ?? '') === 'debt')>Должники</option>
                        <option value="paid" @selected((string) ($balance_status ?? '') === 'paid')>Оплачено</option>
                        <option value="overpaid" @selected((string) ($balance_status ?? '') === 'overpaid')>Переплата</option>
                    </select>
                    <button class="px-3 py-2 bg-gray-100 rounded text-sm">Фильтровать</button>
                    <a href="{{ route('projects.index') }}" class="text-sm text-gray-500 ml-2">Сброс</a>
                </form>

                <div class="ml-auto text-sm text-gray-500">Всего: {{ $projects->total() }}</div>
            </div>

            <div class="divide-y">
                @forelse($projects as $project)
                    <div class="flex items-center gap-4 px-4 py-3 hover:bg-gray-50">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <div>
                                    <a href="{{ route('projects.show', $project) }}"
                                        class="font-medium text-gray-900">{{ $project->title }}</a>
                                    <div class="text-xs text-gray-500">
                                        {{ $project->organization?->name_short ?? ($project->organization?->name_full ?? '-') }}
                                        • {{ $project->city ?? '-' }}
                                    </div>
                                </div>

                                <div class="text-right">
                                    <div class="text-sm text-gray-500">{{ $project->marketer?->name ?? '-' }}</div>
                                    <div class="text-sm text-gray-400 mt-1">
                                        @php
                                            $bal = $project->balance;
                                        @endphp

                                        @if (!is_null($bal) && $bal < 0)
                                            <button type="button"
                                                class="inline-flex items-center px-2 py-1 bg-red-600 text-white rounded text-sm"
                                                data-tippy
                                                data-tippy-content="Долг: {{ number_format(abs($bal), 2, '.', ' ') }} ₽{{ $project->balance_calculated_at ? ' (на ' . \Illuminate\Support\Carbon::make($project->balance_calculated_at)->format('Y-m-d') . ')' : '' }}">
                                                Долг
                                            </button>
                                        @elseif (!is_null($bal) && $bal > 0)
                                            <button type="button"
                                                class="inline-flex items-center px-2 py-1 bg-green-600 text-white rounded text-sm"
                                                data-tippy
                                                data-tippy-content="Переплата: {{ number_format($bal, 2, '.', ' ') }} ₽{{ $project->balance_calculated_at ? ' (на ' . \Illuminate\Support\Carbon::make($project->balance_calculated_at)->format('Y-m-d') . ')' : '' }}">
                                                Переплата
                                            </button>
                                        @elseif (!is_null($bal) && $bal > 0)
                                            <button type="button"
                                                class="inline-flex items-center px-2 py-1 bg-green-600 text-white rounded text-sm"
                                                data-tippy
                                                data-tippy-content="Переплата: {{ number_format($bal, 2, '.', ' ') }} ₽{{ $project->balance_calculated_at ? ' (на ' . \Illuminate\Support\Carbon::make($project->balance_calculated_at)->format('Y-m-d') . ')' : '' }}">
                                                Переплата
                                            </button>
                                        @elseif (!is_null($bal) && round($bal, 2) == 0)
                                            <button type="button"
                                                class="inline-flex items-center px-2 py-1 bg-green-600 text-white rounded text-sm"
                                                data-tippy
                                                data-tippy-content="Оплачено: {{ number_format($project->received_total ?? 0, 2, '.', ' ') }} ₽{{ $project->received_calculated_at ? ' (на ' . \Illuminate\Support\Carbon::make($project->received_calculated_at)->format('Y-m-d') . ')' : '' }}">
                                                Оплаченно
                                            </button>
                                        @else
                                            <span class="text-sm text-gray-400">—</span>
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
                            <a href="{{ route('projects.show', $project) }}"
                                class="text-indigo-600 hover:text-indigo-800 text-sm p-2">Просмотр</a>

                            <a href="{{ route('projects.edit', $project) }}"
                                class="text-indigo-600 hover:text-indigo-800 text-sm p-2">Редактировать</a>

                            <form action="{{ route('projects.destroy', $project) }}" method="POST"
                                onsubmit="return confirm('Удалить проект?');">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 hover:text-red-800 text-sm p-2">Удалить</button>
                            </form>
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
