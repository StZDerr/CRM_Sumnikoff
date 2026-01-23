@extends('layouts.app')

@section('title', 'История участников проекта')

@section('content')
    <div class="space-y-6">

        {{-- Заголовок --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">
                    История участников проекта
                </h1>
                <p class="text-sm text-gray-500">
                    Проект: <span class="font-medium">{{ $project->title }}</span>
                </p>
            </div>

            <a href="{{ route('projects.show', $project) }}"
                class="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                ← Назад к проекту
            </a>
        </div>

        {{-- Таблица --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                            Пользователь
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                            Назначен
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                            Снят
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                            Назначил
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                            Статус
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse ($history as $item)
                        <tr class="hover:bg-gray-50">
                            {{-- Пользователь --}}
                            <td class="px-4 py-3 text-sm text-gray-800">
                                {{ $item->marketer->name ?? '—' }}
                            </td>

                            {{-- Дата назначения --}}
                            <td class="px-4 py-3 text-sm text-gray-600">
                                <input type="datetime-local" name="assigned_at" form="history-{{ $item->id }}"
                                    value="{{ $item->assigned_at?->format('Y-m-d\TH:i') }}"
                                    class="border rounded px-2 py-1 text-sm w-44" />
                            </td>

                            {{-- Дата снятия --}}
                            <td class="px-4 py-3 text-sm text-gray-600">
                                <input type="datetime-local" name="unassigned_at" form="history-{{ $item->id }}"
                                    value="{{ $item->unassigned_at?->format('Y-m-d\TH:i') }}"
                                    class="border rounded px-2 py-1 text-sm w-44 {{ empty($item->unassigned_at) ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : '' }}"
                                    {{ empty($item->unassigned_at) ? 'disabled' : '' }} />
                                @if (empty($item->unassigned_at))
                                    <div class="text-xs text-gray-400 mt-1">
                                        Назначен сейчас. Переназначение текущего маркетолога происходит через проект.
                                    </div>
                                @endif
                            </td>

                            {{-- Кто назначил --}}
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ $item->assignedBy->name ?? '—' }}
                            </td>

                            {{-- Статус --}}
                            <td class="px-4 py-3">
                                @if ($item->unassigned_at)
                                    <span
                                        class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700">
                                        Снят с проекта
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">
                                        Активен
                                    </span>
                                @endif

                                <form id="history-{{ $item->id }}" method="POST"
                                    action="{{ route('projects.history.update', [$project, $item]) }}" class="mt-2">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                        class="inline-flex items-center px-3 py-1 rounded bg-indigo-600 text-white text-xs hover:bg-indigo-700">
                                        Сохранить
                                    </button>
                                </form>

                                @if (!empty($item->unassigned_at))
                                    <form method="POST"
                                        action="{{ route('projects.history.destroy', [$project, $item]) }}" class="mt-2"
                                        onsubmit="return confirm('Удалить назначение?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center px-3 py-1 rounded bg-red-600 text-white text-xs hover:bg-red-700">
                                            Удалить
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">
                                История по проекту отсутствует
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Пагинация --}}
        @if (method_exists($history, 'links'))
            <div class="pt-4">
                {{ $history->links() }}
            </div>
        @endif

    </div>
@endsection
