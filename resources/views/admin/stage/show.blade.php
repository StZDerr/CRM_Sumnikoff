@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold">Этап #{{ $stage->id }}</h1>
            <div class="flex items-center gap-3">
                <a href="{{ route('stages.index') }}"
                    class="inline-flex items-center px-3 py-2 border rounded hover:bg-gray-50">← Назад</a>
                <a href="{{ route('stages.edit', $stage) }}"
                    class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Редактировать</a>
            </div>
        </div>

        <div class="bg-white shadow rounded p-6 space-y-4">
            <div>
                <div class="text-sm text-gray-500">Название</div>
                <div class="font-medium text-lg">{{ $stage->name }}</div>
            </div>

            <div>
                <div class="text-sm text-gray-500">Slug</div>
                <div>{{ $stage->slug ?? '-' }}</div>
            </div>

            <div>
                <div class="text-sm text-gray-500">Описание</div>
                <div>{{ $stage->description ?? '-' }}</div>
            </div>

            <div class="flex items-center gap-4">
                <div>
                    <div class="text-sm text-gray-500">Цвет</div>
                    <div class="w-12 h-8 rounded" style="background: {{ $stage->color ?? '#CBD5E1' }}"></div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Позиция</div>
                    <div>#{{ $stage->sort_order }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection
