@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold">Уровень важности #{{ $importance->id }}</h1>
            <div class="flex items-center gap-3">
                <a href="{{ route('importances.index') }}"
                    class="inline-flex items-center px-3 py-2 border rounded hover:bg-gray-50">
                    ← Назад
                </a>
                <a href="{{ route('importances.edit', $importance) }}"
                    class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                    Редактировать
                </a>
            </div>
        </div>

        <div class="bg-white shadow rounded p-6 space-y-4">
            <div>
                <div class="text-sm text-gray-500">Название</div>
                <div class="font-medium text-lg">{{ $importance->name }}</div>
            </div>

            <div>
                <div class="text-sm text-gray-500">Slug</div>
                <div>{{ $importance->slug ?? '-' }}</div>
            </div>

            <div class="flex items-center gap-4">
                <div>
                    <div class="text-sm text-gray-500">Цвет</div>
                    <div class="w-12 h-8 rounded" style="background: {{ $importance->color ?? '#CBD5E1' }}"></div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Позиция</div>
                    <div>#{{ $importance->sort_order }}</div>
                </div>
            </div>

            <div>
                <div class="text-sm text-gray-500">Создан</div>
                <div>{{ optional($importance->created_at)->format('d.m.Y H:i') ?? '-' }}</div>
            </div>

            <div>
                <div class="text-sm text-gray-500">Обновлён</div>
                <div>{{ optional($importance->updated_at)->format('d.m.Y H:i') ?? '-' }}</div>
            </div>

            <div class="pt-4 border-t flex items-center gap-3">
                <a href="{{ route('importances.edit', $importance) }}"
                    class="px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Редактировать</a>

                <form action="{{ route('importances.destroy', $importance) }}" method="POST"
                    onsubmit="return confirm('Удалить уровень важности?');">
                    @csrf
                    @method('DELETE')
                    <button class="px-3 py-2 border text-red-600 hover:bg-red-50 rounded">Удалить</button>
                </form>
            </div>
        </div>
    </div>
@endsection
