@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-semibold">Проекты для юриста</h1>
        </div>

        @php
            $colspan = auth()->user()->isAdmin() ? 7 : 6;
            $statusMap = [
                'pending' => [
                    'label' => 'На рассмотрении',
                    'class' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                ],
                'processed' => ['label' => 'Обработано', 'class' => 'bg-green-100 text-green-700 border-green-200'],
                'closed' => ['label' => 'Закрыт', 'class' => 'bg-gray-200 text-gray-800 border-gray-300'],
                'reopened' => ['label' => 'Переоткрыто', 'class' => 'bg-blue-100 text-blue-700 border-blue-200'],
                'cancelled' => ['label' => 'Отменено', 'class' => 'bg-red-100 text-red-700 border-red-200'],
            ];
        @endphp

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="p-3">Проект</th>

                        @if (auth()->user()->isAdmin())
                            <th class="p-3">Юрист</th>
                        @endif

                        <th class="p-3">Организация</th>
                        <th class="p-3">Отправлено</th>
                        <th class="p-3">Статус</th>
                        <th class="p-3">Заметка</th>
                        <th class="p-3 text-right">Действия</th>
                    </tr>
                </thead>

                <tbody class="divide-y">
                    @forelse($projects as $assignment)
                        <tr class="hover:bg-gray-50">
                            {{-- Проект: ссылка на страницу проекта для юриста --}}
                            <td class="p-3 font-medium text-gray-900">
                                <a href="{{ route('lawyer.projects.project', $assignment) }}"
                                    class="text-indigo-600 hover:underline">
                                    {{ $assignment->project->title }}
                                </a>
                            </td>

                            {{-- Юрист (только для админа) --}}
                            @if (auth()->user()->isAdmin())
                                <td class="p-3">
                                    {{ $assignment->lawyer?->name ?? '—' }}
                                </td>
                            @endif

                            {{-- Организация --}}
                            <td class="p-3">
                                @if ($assignment->project->organization)
                                    <a href="{{ route('lawyer.projects.organization', $assignment) }}"
                                        class="text-indigo-600 hover:underline">
                                        {{ $assignment->project->organization->name_short ?? $assignment->project->organization->name_full }}
                                    </a>
                                @else
                                    —
                                @endif
                            </td>

                            {{-- Дата отправки --}}
                            <td class="p-3 text-gray-600">
                                {{ $assignment->sent_at?->format('d.m.Y H:i') ?? '—' }}
                                <div class="text-xs text-gray-400">
                                    {{ $assignment->sender?->name ?? '—' }}
                                </div>
                            </td>

                            {{-- Статус --}}
                            @php
                                $status = $statusMap[$assignment->status] ?? [
                                    'label' => ucfirst($assignment->status ?? 'Неизвестно'),
                                    'class' => 'bg-gray-100 text-gray-700 border-gray-200',
                                ];
                            @endphp
                            <td class="p-3">
                                <span class="inline-flex px-2 py-1 text-xs rounded border {{ $status['class'] }}">
                                    {{ $status['label'] }}
                                </span>
                            </td>

                            {{-- Заметка --}}
                            <td class="p-3 text-gray-700">
                                {{ $assignment->note ?: '—' }}
                            </td>

                            {{-- Действия --}}
                            <td class="p-3 text-right">
                                <a href="{{ route('lawyer.projects.show', $assignment) }}"
                                    class="inline-flex items-center px-3 py-1.5 text-sm
                                          bg-indigo-600 text-white rounded hover:bg-indigo-700">
                                    Просмотреть
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $colspan }}" class="p-6 text-center text-gray-500">
                                Назначенных проектов нет
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
