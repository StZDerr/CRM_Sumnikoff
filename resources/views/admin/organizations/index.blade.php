@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold">Организации</h1>
            <a href="{{ route('organizations.create') }}"
                class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Добавить</a>
        </div>

        <div class="bg-white shadow rounded">
            <div class="p-4 border-b flex items-center gap-4">
                <form method="GET" action="{{ route('organizations.index') }}" class="flex flex-wrap items-center gap-2">
                    <input type="search" name="q" value="{{ $q ?? '' }}"
                        placeholder="Поиск по названию или ИНН..." class="border rounded px-3 py-2 w-72 text-sm" />
                    <select name="status" class="border rounded px-3 py-2 text-sm">
                        <option value="">Все статусы</option>
                        @foreach ($statuses as $id => $name)
                            <option value="{{ $id }}" @selected((string) ($filterStatus ?? '') === (string) $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                    <select name="source" class="border rounded px-3 py-2 text-sm">
                        <option value="">Все источники</option>
                        @foreach ($sources as $id => $name)
                            <option value="{{ $id }}" @selected((string) ($filterSource ?? '') === (string) $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                    <button class="px-3 py-2 bg-gray-100 rounded text-sm">Фильтровать</button>
                    <a href="{{ route('organizations.index') }}" class="text-sm text-gray-500 ml-2">Сброс</a>
                </form>

                <div class="ml-auto text-sm text-gray-500">Всего: {{ $organizations->total() }}</div>
            </div>

            <div class="divide-y">
                @forelse($organizations as $org)
                    <div class="flex items-center gap-4 px-4 py-3 hover:bg-gray-50">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <div>
                                    {{-- Название кликабельное --}}
                                    <a href="{{ route('organizations.show', $org) }}"
                                        class="font-medium text-gray-900 hover:text-indigo-600">
                                        {{ $org->name_full }}
                                    </a>

                                    <div class="text-xs text-gray-500">
                                        {{ $org->inn ? 'ИНН: ' . $org->inn . ' • ' : '' }}
                                        {{ $org->phone ? $org->phone . ' • ' : '' }}
                                        {{ $org->email ?? '-' }}
                                    </div>
                                </div>

                                <div class="text-right">
                                    @if ($org->status)
                                        <div class="text-xs text-white rounded px-2 py-1 mb-1 inline-block bg-indigo-600">
                                            {{ $org->status->name }}
                                        </div>
                                    @endif
                                    @if ($org->source)
                                        <div class="text-xs text-gray-700 rounded px-2 py-1 inline-block border">
                                            {{ $org->source->name }}
                                        </div>
                                    @endif
                                    <div class="text-sm text-gray-400 mt-1">{{ $org->created_at?->format('Y-m-d') }}</div>
                                </div>
                            </div>
                        </div>

                        @if (auth()->user()->isAdmin())
                            <div class="flex items-center gap-2">
                                {{-- Убираем кнопку "Просмотр" --}}
                                <a href="{{ route('organizations.edit', $org) }}"
                                    class="text-indigo-600 hover:text-indigo-800 text-sm p-2">Редактировать</a>

                                <form action="{{ route('organizations.destroy', $org) }}" method="POST"
                                    onsubmit="return confirm('Удалить организацию?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 hover:text-red-800 text-sm p-2">Удалить</button>
                                </form>
                            </div>
                        @endif
                    </div>

                @empty
                    <div class="px-4 py-6 text-gray-500">Пока нет организаций.</div>
                @endforelse
            </div>

            <div class="p-4">
                {{ $organizations->links() }}
            </div>
        </div>
    </div>
@endsection
