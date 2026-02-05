@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto bg-white rounded shadow p-6">
        <h1 class="text-xl font-semibold mb-4">Новая задача</h1>

        <form method="POST" action="{{ route('tasks.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium mb-1">Проект</label>
                <select name="project_id" class="w-full border rounded p-2">
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->title }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Название</label>
                <input name="title" class="w-full border rounded p-2" required />
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Описание</label>
                <textarea name="description" class="w-full border rounded p-2" rows="4"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Статус</label>
                    <select name="status_id" class="w-full border rounded p-2">
                        @foreach ($statuses as $status)
                            <option value="{{ $status->id }}">{{ $status->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Ответственный</label>
                    <select name="assignee_id" class="w-full border rounded p-2">
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Дедлайн</label>
                <input type="datetime-local" name="deadline_at" class="w-full border rounded p-2" />
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Соисполнители</label>
                <select name="co_executor_ids[]" multiple class="w-full border rounded p-2">
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Наблюдатели</label>
                <select name="observer_ids[]" multiple class="w-full border rounded p-2">
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Создать</button>
                <a href="{{ route('tasks.index') }}" class="px-4 py-2 bg-gray-200 rounded">Отмена</a>
            </div>
        </form>
    </div>
@endsection
