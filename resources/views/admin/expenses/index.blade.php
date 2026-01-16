@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold">Расходы</h1>
            <div class="flex items-center gap-3">
                {{-- Кнопка "Новый расход (Офис)" --}}
                @if (isset($officeCategories) && $officeCategories->count())
                    <button type="button" id="openOfficeExpenseBtn"
                        class="inline-flex items-center gap-2 px-3 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Новый расход (Офис)
                    </button>
                @endif
                <a href="{{ route('expenses.create') }}"
                    class="inline-flex px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Добавить</a>
            </div>
        </div>

        {{-- Фильтры --}}
        <div class="mb-4 flex items-center gap-4">
            <a href="{{ route('expenses.index') }}"
                class="px-3 py-1.5 rounded text-sm {{ !request('office') ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                Все расходы
            </a>
            <a href="{{ route('expenses.index', ['office' => 1]) }}"
                class="px-3 py-1.5 rounded text-sm {{ request('office') == '1' ? 'bg-emerald-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                <span class="inline-flex items-center gap-1">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    Только офисные
                </span>
            </a>
        </div>

        <div class="bg-white shadow rounded">
            <table class="w-full text-sm">
                <thead class="text-left text-xs text-gray-500 border-b">
                    <tr>
                        <th class="p-3">Дата</th>
                        <th class="p-3">Сумма</th>
                        <th class="p-3">Категория</th>
                        <th class="p-3">Контрагент</th>
                        <th class="p-3">Метод / Счёт</th>
                        <th class="p-3">Проект</th>
                        <th class="p-3">Статус</th>
                        <th class="p-3 text-right">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($items as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="p-3">
                                <a href="{{ route('expenses.show', $item) }}">{{ $item->expense_date?->format('d/m/Y H:i') }}
                                </a>
                            </td>
                            <td class="p-3">{{ number_format($item->amount, 2, '.', ' ') }} ₽</td>
                            <td class="p-3">
                                <div class="flex items-center gap-1">
                                    {{ $item->category?->title ?? '-' }}
                                    @if ($item->category?->is_office)
                                        <span
                                            class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-700"
                                            title="Офисный расход">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="p-3">{{ $item->organization?->title ?? '-' }}</td>
                            <td class="p-3">{{ $item->paymentMethod?->title ?? ($item->bankAccount?->title ?? '-') }}
                            </td>
                            <td class="p-3">{{ $item->project?->title ?? '-' }}</td>
                            <td class="p-3">
                                @if ($item->status === 'paid')
                                    <span class="text-green-600">Оплачено</span>
                                @elseif($item->status === 'partial')
                                    <span class="text-yellow-600">Частично</span>
                                @else
                                    <span class="text-gray-600">Ожидает</span>
                                @endif
                            </td>
                            <td class="p-3 text-right">
                                <a href="{{ route('expenses.show', $item) }}"
                                    class="text-indigo-600 hover:underline mr-3">Открыть</a>
                                <a href="{{ route('expenses.edit', $item) }}"
                                    class="text-indigo-600 hover:underline mr-3">Редактировать</a>
                                <form action="{{ route('expenses.destroy', $item) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Удалить расход?');">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 hover:text-red-800">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-4 text-gray-500">Пока нет расходов.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="p-4">{{ $items->links() }}</div>
        </div>
    </div>

    {{-- Модальное окно для офисного расхода --}}
    @if (isset($officeCategories) && $officeCategories->count())
        @include('admin.expenses._office_modal')
    @endif
@endsection
