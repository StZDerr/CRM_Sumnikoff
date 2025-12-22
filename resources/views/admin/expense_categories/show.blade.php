@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold">{{ $expenseCategory->title }}</h1>
            <div class="flex items-center gap-3">
                <a href="{{ route('expense-categories.edit', $expenseCategory) }}"
                    class="inline-flex items-center px-3 py-2 border rounded hover:bg-gray-50">Редактировать</a>
                <a href="{{ route('expense-categories.index') }}"
                    class="inline-flex items-center px-3 py-2 border rounded hover:bg-gray-50">← Назад</a>
            </div>
        </div>

        <div class="bg-white shadow rounded p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wide">Название</div>
                    <div class="mt-1 font-medium text-gray-900">{{ $expenseCategory->title }}</div>
                </div>

                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wide">Slug</div>
                    <div class="mt-1 font-medium text-gray-900">{{ $expenseCategory->slug ?? '-' }}</div>
                </div>

                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wide">Позиция</div>
                    <div class="mt-1 font-medium text-gray-900">{{ $expenseCategory->sort_order ?? 0 }}</div>
                </div>

                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wide">Создано</div>
                    <div class="mt-1 text-sm text-gray-700">{{ $expenseCategory->created_at?->format('Y-m-d H:i') ?? '-' }}
                    </div>
                </div>

                <div class="col-span-2">
                    <div class="text-xs text-gray-500 uppercase tracking-wide">Обновлено</div>
                    <div class="mt-1 text-sm text-gray-700">{{ $expenseCategory->updated_at?->format('Y-m-d H:i') ?? '-' }}
                    </div>
                </div>
            </div>

            <hr>

            <div class="flex items-center gap-3">
                <form action="{{ route('expense-categories.destroy', $expenseCategory) }}" method="POST"
                    onsubmit="return confirm('Удалить категорию расходов?');">
                    @csrf
                    @method('DELETE')
                    <button class="px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700">Удалить</button>
                </form>
            </div>
        </div>
    </div>
@endsection
