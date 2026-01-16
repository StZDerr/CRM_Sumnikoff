@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold">Расходы</h1>
            <a href="{{ route('expenses.create') }}"
                class="inline-flex px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Добавить</a>
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
                            <td class="p-3">{{ $item->category?->title ?? '-' }}</td>
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
@endsection
