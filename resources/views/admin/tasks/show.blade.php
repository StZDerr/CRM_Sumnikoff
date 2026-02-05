@extends('layouts.app')

@section('content')
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 space-y-6">
            <div class="bg-white rounded shadow p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold">{{ $task->title }}</h1>
                        <div class="text-sm text-gray-500 mt-1">Проект: {{ $task->project->title ?? '-' }}</div>
                    </div>
                    <div>
                        <span class="px-3 py-1 rounded text-white"
                            style="background-color: {{ $task->status->color ?? '#6B7280' }}">
                            {{ $task->status->name ?? '-' }}
                        </span>
                    </div>
                </div>

                <div class="mt-4 text-gray-700 whitespace-pre-line">{{ $task->description }}</div>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <div class="text-sm text-gray-500">Ответственный</div>
                        <div class="font-medium">{{ $task->assignee->name ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Постановщик</div>
                        <div class="font-medium">{{ $task->creator->name ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Дедлайн</div>
                        <div class="font-medium">{{ $task->deadline_at?->format('d.m.Y H:i') ?? 'Без срока' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Закрыта</div>
                        <div class="font-medium">{{ $task->closed_at?->format('d.m.Y H:i') ?? 'Нет' }}</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded shadow p-6">
                <h2 class="text-lg font-semibold mb-4">История / чат</h2>
                <div class="space-y-4">
                    @foreach ($task->comments as $comment)
                        <div class="border rounded p-3">
                            <div class="flex items-center justify-between">
                                <div class="text-sm font-medium">{{ $comment->user->name ?? 'Система' }}</div>
                                <div class="text-xs text-gray-500">{{ $comment->created_at->format('d.m.Y H:i') }}</div>
                            </div>
                            <div class="text-sm text-gray-700 mt-2">{{ $comment->message }}</div>
                            @if (!empty($comment->meta))
                                <pre class="text-xs text-gray-500 mt-2 whitespace-pre-wrap">{{ json_encode($comment->meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            @endif
                        </div>
                    @endforeach
                </div>

                <form method="POST" action="{{ route('tasks.comments.store', $task) }}" class="mt-6 space-y-2">
                    @csrf
                    <textarea name="message" class="w-full border rounded p-2" rows="3" placeholder="Комментарий..."></textarea>
                    <button class="px-4 py-2 bg-indigo-600 text-white rounded">Отправить</button>
                </form>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Участники</h3>
                <div class="text-sm text-gray-600">Соисполнители</div>
                <ul class="mb-4">
                    @forelse ($task->coExecutors as $user)
                        <li>{{ $user->name }}</li>
                    @empty
                        <li class="text-gray-400">Нет</li>
                    @endforelse
                </ul>
                <div class="text-sm text-gray-600">Наблюдатели</div>
                <ul>
                    @forelse ($task->observers as $user)
                        <li>{{ $user->name }}</li>
                    @empty
                        <li class="text-gray-400">Нет</li>
                    @endforelse
                </ul>
            </div>

            <div class="bg-white rounded shadow p-6 space-y-4">
                <h3 class="text-lg font-semibold">Действия</h3>

                <form method="POST" action="{{ route('tasks.change-status', $task) }}" class="space-y-2">
                    @csrf
                    <label class="text-sm text-gray-600">Сменить статус</label>
                    <select name="status_id" class="w-full border rounded p-2">
                        @foreach (\App\Models\TaskStatus::ordered()->get() as $status)
                            <option value="{{ $status->id }}" @selected($task->status_id === $status->id)>
                                {{ $status->name }}</option>
                        @endforeach
                    </select>
                    <button class="px-4 py-2 bg-blue-600 text-white rounded">Применить</button>
                </form>

                <form method="POST" action="{{ route('tasks.change-deadline', $task) }}" class="space-y-2">
                    @csrf
                    <label class="text-sm text-gray-600">Перенос дедлайна</label>
                    <input type="datetime-local" name="deadline_at" value="{{ $task->deadline_at?->format('Y-m-d\TH:i') }}"
                        class="w-full border rounded p-2" />
                    <input type="text" name="reason" class="w-full border rounded p-2" placeholder="Причина" />
                    <button class="px-4 py-2 bg-blue-600 text-white rounded">Сохранить</button>
                </form>

                <form method="POST" action="{{ route('tasks.close', $task) }}">
                    @csrf
                    <button class="px-4 py-2 bg-green-600 text-white rounded w-full">Закрыть задачу</button>
                </form>

                <form method="POST" action="{{ route('tasks.disputes.store', $task) }}" class="space-y-2">
                    @csrf
                    <input type="text" name="reason" class="w-full border rounded p-2"
                        placeholder="Причина несогласия" />
                    <button class="px-4 py-2 bg-red-600 text-white rounded w-full">Открыть спор</button>
                </form>
            </div>

            @if ($task->disputes->count())
                <div class="bg-white rounded shadow p-6">
                    <h3 class="text-lg font-semibold mb-2">Споры</h3>
                    @foreach ($task->disputes as $dispute)
                        <div class="border rounded p-3 mb-3">
                            <div class="text-sm">Статус: {{ $dispute->status }}</div>
                            <div class="text-xs text-gray-500">Открыл: {{ $dispute->openedBy->name ?? '-' }}</div>
                            <div class="text-xs text-gray-500">Причина: {{ $dispute->reason ?? '-' }}</div>
                            @if ($dispute->status === \App\Models\TaskDispute::STATUS_OPEN)
                                <form method="POST" action="{{ route('tasks.disputes.resolve', $dispute) }}"
                                    class="mt-2 flex gap-2">
                                    @csrf
                                    <input type="hidden" name="resolution"
                                        value="{{ \App\Models\TaskDispute::STATUS_APPROVED_CLOSE }}" />
                                    <button class="px-3 py-1 bg-green-600 text-white rounded">Подтвердить закрытие</button>
                                </form>
                                <form method="POST" action="{{ route('tasks.disputes.resolve', $dispute) }}"
                                    class="mt-2 flex gap-2">
                                    @csrf
                                    <input type="hidden" name="resolution"
                                        value="{{ \App\Models\TaskDispute::STATUS_REJECTED_CLOSE }}" />
                                    <button class="px-3 py-1 bg-yellow-600 text-white rounded">Отклонить закрытие</button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
