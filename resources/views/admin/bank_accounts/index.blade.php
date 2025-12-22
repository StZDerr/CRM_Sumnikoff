@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold">Банковские счета</h1>
            <a href="{{ route('bank-accounts.create') }}"
                class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                Добавить
            </a>
        </div>

        <div class="bg-white shadow rounded">
            <table class="w-full text-sm">
                <thead class="text-left text-xs text-gray-500 border-b">
                    <tr>
                        <th class="p-3">Название</th>
                        <th class="p-3">Р/сч</th>
                        <th class="p-3">к/сч</th>
                        <th class="p-3">Банк</th>
                        <th class="p-3">БИК / ИНН</th>
                        <th class="p-3 text-right">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($items as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="p-3">{{ $item->title }}</td>
                            <td class="p-3">{{ $item->account_number }}</td>
                            <td class="p-3">{{ $item->correspondent_account ?? '-' }}</td>
                            <td class="p-3">{{ $item->bank_name ?? '-' }}</td>
                            <td class="p-3">{{ $item->bik ?? '-' }} / {{ $item->inn ?? '-' }}</td>
                            <td class="p-3 text-right">
                                <a href="{{ route('bank-accounts.show', $item) }}"
                                    class="text-indigo-600 hover:underline mr-3">Открыть</a>
                                <a href="{{ route('bank-accounts.edit', $item) }}"
                                    class="text-indigo-600 hover:underline mr-3">Редактировать</a>
                                <form action="{{ route('bank-accounts.destroy', $item) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Удалить банковский счёт?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 hover:text-red-800">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-4 text-gray-500">Пока нет банковских счётов.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="p-4">
                {{ $items->links() }}
            </div>
        </div>
    </div>
@endsection
