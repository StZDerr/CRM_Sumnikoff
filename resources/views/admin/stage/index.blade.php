@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold">Этапы работ</h1>
            <a href="{{ route('stages.create') }}"
                class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                Добавить
            </a>
        </div>

        <div class="bg-white shadow rounded">
            <div class="p-4 border-b">
                <p class="text-sm text-gray-600">Перетащите элементы для изменения порядка. Изменения сохраняются
                    автоматически.</p>
            </div>

            <div id="stages-list" data-reorder-url="{{ route('stages.reorder') }}" class="divide-y" role="list">
                @forelse($stages as $stage)
                    <div data-id="{{ $stage->id }}" class="flex items-center gap-4 px-4 py-3 hover:bg-gray-50"
                        role="listitem">
                        <div class="drag-handle cursor-grab text-gray-400">
                            <!-- drag icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8 9h.01M8 15h.01M12 9h.01M12 15h.01M16 9h.01M16 15h.01" />
                            </svg>
                        </div>

                        <div style="background: {{ $stage->color ?? '#CBD5E1' }}" class="w-6 h-6 rounded border"></div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-medium text-gray-900">{{ $stage->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $stage->slug ?? '-' }}</div>
                                </div>
                                <div class="text-sm text-gray-400">#{{ $stage->sort_order }}</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="{{ route('stages.edit', $stage) }}"
                                class="text-indigo-600 hover:text-indigo-800 text-sm p-2">Редактировать</a>

                            <form action="{{ route('stages.destroy', $stage) }}" method="POST"
                                onsubmit="return confirm('Удалить этап?');">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 hover:text-red-800 text-sm p-2">Удалить</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-6 text-gray-500">Пока нет этапов.</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
