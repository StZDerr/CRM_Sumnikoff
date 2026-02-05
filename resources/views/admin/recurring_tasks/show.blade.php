@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="bg-white rounded shadow p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">{{ $recurringTask->title }}</h1>
                <a href="{{ route('recurring-tasks.edit', $recurringTask) }}"
                    class="px-3 py-2 bg-indigo-600 text-white rounded">Редактировать</a>
            </div>
            <div class="text-sm text-gray-500 mt-1">Проект: {{ $recurringTask->project->title ?? '-' }}</div>
            <div class="mt-3 text-gray-700">{{ $recurringTask->description }}</div>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>Ответственный: <span class="font-medium">{{ $recurringTask->assignee->name ?? '-' }}</span></div>
                <div>Статус: <span class="font-medium">{{ $recurringTask->status->name ?? '-' }}</span></div>
                <div>Активна: <span class="font-medium">{{ $recurringTask->is_active ? 'Да' : 'Нет' }}</span></div>
                <div>Период: <span class="font-medium">{{ $recurringTask->starts_at?->format('d.m.Y') ?? '-' }} -
                        {{ $recurringTask->ends_at?->format('d.m.Y') ?? '-' }}</span></div>
            </div>
        </div>

        <div class="bg-white rounded shadow p-6">
            <h2 class="text-lg font-semibold mb-2">Правила</h2>
            <div class="space-y-2 text-sm">
                @foreach ($recurringTask->rules as $rule)
                    <div class="border rounded p-3">
                        <div>Тип: <strong>{{ $rule->type }}</strong></div>
                        <div>Интервал дней: {{ $rule->interval_days ?? '-' }}</div>
                        <div>Дни недели: {{ $rule->weekly_days ? implode(', ', $rule->weekly_days) : '-' }}</div>
                        <div>Время: {{ $rule->time_of_day ?? '-' }}</div>
                        <div>Дата начала: {{ $rule->start_date?->format('d.m.Y') ?? '-' }}</div>
                        <div>Ежемесячно:
                            {{ $rule->monthly_rules ? json_encode($rule->monthly_rules, JSON_UNESCAPED_UNICODE) : '-' }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded shadow p-6">
            <h2 class="text-lg font-semibold mb-2">Созданные задачи</h2>
            <div class="space-y-2">
                @forelse ($recurringTask->tasks as $task)
                    <a href="{{ route('tasks.show', $task) }}" class="block border rounded p-3 hover:bg-gray-50">
                        {{ $task->title }} — {{ $task->recurring_occurrence_date?->format('d.m.Y') ?? '-' }}
                    </a>
                @empty
                    <div class="text-sm text-gray-400">Пока нет задач</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
