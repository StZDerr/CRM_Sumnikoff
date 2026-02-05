@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">Регулярные задачи</h1>
        <a href="{{ route('recurring-tasks.create') }}" class="px-4 py-2 bg-green-600 text-white rounded">Создать</a>
    </div>

    <div class="bg-white rounded shadow p-4">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500">
                    <th class="py-2">Название</th>
                    <th class="py-2">Проект</th>
                    <th class="py-2">Ответственный</th>
                    <th class="py-2">Активна</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($recurringTasks as $task)
                    <tr class="border-t">
                        <td class="py-2">
                            <a href="{{ route('recurring-tasks.show', $task) }}" class="text-indigo-600 hover:underline">
                                {{ $task->title }}
                            </a>
                        </td>
                        <td class="py-2">{{ $task->project->title ?? '-' }}</td>
                        <td class="py-2">{{ $task->assignee->name ?? '-' }}</td>
                        <td class="py-2">{{ $task->is_active ? 'Да' : 'Нет' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">{{ $recurringTasks->links() }}</div>
    </div>
@endsection
