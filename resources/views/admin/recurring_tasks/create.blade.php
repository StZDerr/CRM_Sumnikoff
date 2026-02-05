@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto bg-white rounded shadow p-6">
        <h1 class="text-xl font-semibold mb-4">Новая регулярная задача</h1>

        <form method="POST" action="{{ route('recurring-tasks.store') }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Проект</label>
                    <select name="project_id" class="w-full border rounded p-2">
                        @foreach ($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->title }}</option>
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
                <label class="block text-sm font-medium mb-1">Статус</label>
                <select name="status_id" class="w-full border rounded p-2">
                    @foreach ($statuses as $status)
                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Название</label>
                <input name="title" class="w-full border rounded p-2" required />
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Описание</label>
                <textarea name="description" class="w-full border rounded p-2" rows="3"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Дата начала</label>
                    <input type="date" name="starts_at" class="w-full border rounded p-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Дата окончания</label>
                    <input type="date" name="ends_at" class="w-full border rounded p-2" />
                </div>
            </div>

            <div class="border rounded p-4 bg-gray-50">
                <h3 class="font-medium mb-2">Правило</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm mb-1">Тип</label>
                        <select name="rules[0][type]" class="w-full border rounded p-2">
                            <option value="daily">Ежедневно</option>
                            <option value="weekly">Еженедельно</option>
                            <option value="monthly">Ежемесячно</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Интервал дней (для daily)</label>
                        <input type="number" name="rules[0][interval_days]" class="w-full border rounded p-2" />
                    </div>
                </div>

                <div class="mt-3">
                    <label class="block text-sm mb-1">Дни недели (weekly)</label>
                    <div class="flex flex-wrap gap-3 text-sm">
                        @foreach ([1 => 'Пн', 2 => 'Вт', 3 => 'Ср', 4 => 'Чт', 5 => 'Пт', 6 => 'Сб', 7 => 'Вс'] as $day => $label)
                            <label class="flex items-center gap-1">
                                <input type="checkbox" name="rules[0][weekly_days][]" value="{{ $day }}" />
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm mb-1">Время</label>
                        <input type="time" name="rules[0][time_of_day]" class="w-full border rounded p-2" />
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Дата начала повторения</label>
                        <input type="date" name="rules[0][start_date]" class="w-full border rounded p-2" />
                    </div>
                </div>

                <div class="mt-3">
                    <label class="block text-sm mb-1">Ежемесячно: неделя и день</label>
                    <div class="grid grid-cols-2 gap-2">
                        <select name="rules[0][monthly_rules][0][week]" class="border rounded p-2">
                            @foreach ([1, 2, 3, 4, 5] as $week)
                                <option value="{{ $week }}">{{ $week }} неделя</option>
                            @endforeach
                        </select>
                        <select name="rules[0][monthly_rules][0][weekday]" class="border rounded p-2">
                            @foreach ([1 => 'Пн', 2 => 'Вт', 3 => 'Ср', 4 => 'Чт', 5 => 'Пт', 6 => 'Сб', 7 => 'Вс'] as $day => $label)
                                <option value="{{ $day }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex gap-2">
                <button class="px-4 py-2 bg-green-600 text-white rounded">Создать</button>
                <a href="{{ route('recurring-tasks.index') }}" class="px-4 py-2 bg-gray-200 rounded">Отмена</a>
            </div>
        </form>
    </div>
@endsection
