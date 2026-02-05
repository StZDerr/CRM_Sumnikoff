@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">Задачи</h1>
        <div class="flex items-center gap-2">
            @if (auth()->user()->isAdmin() || auth()->user()->isProjectManager())
                <a href="{{ route('task-statuses.index') }}"
                    class="px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-800">Статусы задач</a>
            @endif
            <a href="{{ route('recurring-tasks.index') }}"
                class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Регулярные</a>
            <a href="{{ route('tasks.create') }}" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Новая
                задача</a>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="bg-white rounded shadow p-4">
            <h2 class="text-lg font-semibold mb-4">Сроки</h2>
            <div class="space-y-4">
                @php
                    $labels = [
                        'overdue' => 'Просрочено',
                        'today' => 'Сегодня',
                        'week' => 'Неделя',
                        'no_deadline' => 'Без срока',
                        'done' => 'Завершённые',
                    ];
                @endphp

                @foreach ($deadlineGroups as $key => $group)
                    <div>
                        <div class="text-sm text-gray-500 mb-2">{{ $labels[$key] ?? $key }}</div>
                        <div class="space-y-2">
                            @forelse ($group as $task)
                                <a href="{{ route('tasks.show', $task) }}"
                                    class="block p-3 rounded border hover:bg-gray-50">
                                    <div class="flex items-center justify-between">
                                        <div class="font-medium">{{ $task->title }}</div>
                                        <span class="text-xs px-2 py-1 rounded"
                                            style="background-color: {{ $task->status->color ?? '#E5E7EB' }}">
                                            {{ $task->status->name ?? '-' }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        Проект: {{ $task->project->title ?? '-' }} • Ответственный:
                                        {{ $task->assignee->name ?? '-' }}
                                    </div>
                                </a>
                            @empty
                                <div class="text-sm text-gray-400">Нет задач</div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded shadow p-4">
            <h2 class="text-lg font-semibold mb-4">Мой план</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="plan-board">
                @foreach ($statuses as $status)
                    <div class="border rounded p-3 bg-gray-50">
                        <div class="flex items-center justify-between mb-2">
                            <div class="font-medium">{{ $status->name }}</div>
                            <span class="w-3 h-3 rounded-full"
                                style="background-color: {{ $status->color ?? '#9CA3AF' }}"></span>
                        </div>
                        <div class="space-y-2 min-h-[60px]" data-status-id="{{ $status->id }}">
                            @foreach ($planTasks[$status->id] ?? [] as $task)
                                <div class="p-2 rounded bg-white border cursor-move" draggable="true"
                                    data-task-id="{{ $task->id }}">
                                    <div class="text-sm font-medium">{{ $task->title }}</div>
                                    <div class="text-xs text-gray-500">{{ $task->project->title ?? '-' }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="bg-white rounded shadow p-4 mt-6">
        <h2 class="text-lg font-semibold mb-4">Все задачи</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500">
                        <th class="py-2">Задача</th>
                        <th class="py-2">Проект</th>
                        <th class="py-2">Статус</th>
                        <th class="py-2">Ответственный</th>
                        <th class="py-2">Дедлайн</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tasks as $task)
                        <tr class="border-t">
                            <td class="py-2">
                                <a class="text-indigo-600 hover:underline"
                                    href="{{ route('tasks.show', $task) }}">{{ $task->title }}</a>
                            </td>
                            <td class="py-2">{{ $task->project->title ?? '-' }}</td>
                            <td class="py-2">{{ $task->status->name ?? '-' }}</td>
                            <td class="py-2">{{ $task->assignee->name ?? '-' }}</td>
                            <td class="py-2">{{ $task->deadline_at?->format('d.m.Y H:i') ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $tasks->links() }}</div>
    </div>

    @push('scripts')
        <script>
            document.querySelectorAll('[draggable="true"]').forEach(function(item) {
                item.addEventListener('dragstart', function(e) {
                    e.dataTransfer.setData('text/plain', item.dataset.taskId);
                });
            });

            document.querySelectorAll('[data-status-id]').forEach(function(column) {
                column.addEventListener('dragover', function(e) {
                    e.preventDefault();
                });

                column.addEventListener('drop', function(e) {
                    e.preventDefault();
                    const taskId = e.dataTransfer.getData('text/plain');
                    const statusId = column.dataset.statusId;

                    if (!taskId || !statusId) return;

                    fetch(`{{ url('tasks') }}/${taskId}/status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            status_id: statusId
                        })
                    }).then(() => window.location.reload());
                });
            });
        </script>
    @endpush
@endsection
