@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold">Ежемесячные расходы</h1>
            <a href="{{ route('monthly-expenses.create') }}"
                class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                Добавить
            </a>
        </div>

        <div class="bg-white shadow rounded">
            <div class="divide-y">
                @forelse ($items as $item)
                    <div class="flex items-center gap-4 px-4 py-3 hover:bg-gray-50">
                        <div class="w-20 text-center">
                            <div class="text-sm font-semibold text-gray-900">{{ $item->day_of_month ?? '—' }}</div>
                            <div class="text-xs text-gray-500">День</div>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-medium text-gray-900">{{ $item->title }}</div>
                                    <div class="text-xs text-gray-500">
                                        {{ $item->user?->name ?? '—' }}
                                        @if (!empty($item->note))
                                            • {{ $item->note }}
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-semibold text-gray-900">
                                        {{ number_format($item->amount, 2, '.', ' ') }} ₽
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $item->is_active ? 'Активен' : 'Отключён' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="{{ route('monthly-expenses.edit', $item) }}"
                                class="text-indigo-600 hover:text-indigo-800 text-sm p-2">Редактировать</a>

                            <form action="{{ route('monthly-expenses.destroy', $item) }}" method="POST"
                                onsubmit="return confirm('Удалить ежемесячный расход?');">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 hover:text-red-800 text-sm p-2">Удалить</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-6 text-gray-500">Пока нет ежемесячных расходов.</div>
                @endforelse
            </div>

            <div class="p-4">{{ $items->links() }}</div>
        </div>
    </div>
@endsection
