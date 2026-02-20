@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold">Источники клиентов</h1>
            <a href="{{ route('campaign-sources.create') }}"
                class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Добавить</a>
        </div>

        <div class="bg-white shadow rounded">
            <div class="p-4 border-b">
                <p class="text-sm text-gray-600">Перетащите элементы для изменения порядка. Изменения сохраняются
                    автоматически.</p>
            </div>

            <div id="campaign-sources-list" data-reorder-url="{{ route('campaign-sources.reorder') }}" class="divide-y"
                role="list">
                @forelse($sources as $source)
                    <div data-id="{{ $source->id }}" class="flex items-center gap-4 px-4 py-3 hover:bg-gray-50"
                        role="listitem">
                        <div class="drag-handle cursor-grab text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8 9h.01M8 15h.01M12 9h.01M12 15h.01M16 9h.01M16 15h.01" />
                            </svg>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <div class="font-medium text-gray-900">{{ $source->name }}</div>
                                        @if ($source->is_lead_source)
                                            <span
                                                class="inline-flex items-center gap-1 ml-2 px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">Лид</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-500">{{ $source->slug ?? '-' }}</div>
                                </div>
                                <div class="text-sm text-gray-400">#{{ $source->sort_order }}</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="{{ route('campaign-sources.edit', $source) }}"
                                class="text-indigo-600 hover:text-indigo-800 text-sm p-2">Редактировать</a>

                            <form action="{{ route('campaign-sources.destroy', $source) }}" method="POST"
                                onsubmit="return confirm('Удалить источник?');">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 hover:text-red-800 text-sm p-2">Удалить</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-6 text-gray-500">Пока нет источников.</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
