@extends('layouts.app')

@section('content')
    <div class="max-w-2xl mx-auto bg-white rounded shadow p-6">
        <h1 class="text-xl font-semibold mb-4">Статусы задач</h1>

        <form method="POST" action="{{ route('task-statuses.store') }}" class="space-y-3 mb-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <input name="name" class="border rounded p-2" placeholder="Название" required />
                <input name="slug" class="border rounded p-2" placeholder="slug (необязательно)" />
                <input type="color" name="color" class="border rounded p-2 h-10" value="#9CA3AF" />
                <input name="sort_order" class="border rounded p-2" type="number" placeholder="Порядок" />
            </div>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_default" value="1" />
                По умолчанию
            </label>
            <button class="px-4 py-2 bg-indigo-600 text-white rounded">Добавить</button>
        </form>

        <div class="space-y-2">
            @foreach ($statuses as $status)
                <div class="border rounded p-3 flex items-center justify-between">
                    <div>
                        <div class="font-medium">{{ $status->name }}</div>
                        <div class="text-xs text-gray-500">{{ $status->slug }}</div>
                    </div>
                    <span class="w-4 h-4 rounded-full" style="background-color: {{ $status->color ?? '#9CA3AF' }}"></span>
                </div>
            @endforeach
        </div>
    </div>
@endsection
