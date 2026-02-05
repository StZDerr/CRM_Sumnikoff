@extends('layouts.app')

@section('content')
    @php $rule = $recurringTask->rules->first(); @endphp
    <div class="max-w-4xl mx-auto bg-white rounded shadow p-6">
        <h1 class="text-xl font-semibold mb-4">Редактирование регулярной задачи</h1>

        <form method="POST" action="{{ route('recurring-tasks.update', $recurringTask) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Проект</label>
                    <select name="project_id" class="w-full border rounded p-2">
                        @foreach ($projects as $project)
                            <option value="{{ $project->id }}" @selected($recurringTask->project_id === $project->id)>
                                {{ $project->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Ответственный</label>
                    <select name="assignee_id" class="w-full border rounded p-2">
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected($recurringTask->assignee_id === $user->id)>
                                {{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Статус</label>
                <select name="status_id" class="w-full border rounded p-2">
                    @foreach ($statuses as $status)
                        <option value="{{ $status->id }}" @selected($recurringTask->status_id === $status->id)>
                            {{ $status->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Название</label>
                <input name="title" value="{{ $recurringTask->title }}" class="w-full border rounded p-2" required />
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Описание</label>
                <textarea name="description" class="w-full border rounded p-2" rows="3">{{ $recurringTask->description }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Дата начала</label>
                    <input type="date" name="starts_at" value="{{ $recurringTask->starts_at?->format('Y-m-d') }}"
                        class="w-full border rounded p-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Дата окончания</label>
                    <input type="date" name="ends_at" value="{{ $recurringTask->ends_at?->format('Y-m-d') }}"
                        class="w-full border rounded p-2" />
                </div>
            </div>

            <div class="border rounded p-4 bg-gray-50">
                <h3 class="font-medium mb-2">Правило</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm mb-1">Тип</label>
                        <select name="rules[0][type]" class="w-full border rounded p-2">
                            <option value="daily" @selected($rule?->type === 'daily')>Ежедневно</option>
                            <option value="weekly" @selected($rule?->type === 'weekly')>Еженедельно</option>
                            <option value="monthly" @selected($rule?->type === 'monthly')>Ежемесячно</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Интервал дней (для daily)</label>
                        <input type="number" name="rules[0][interval_days]" value="{{ $rule?->interval_days }}"
                            class="w-full border rounded p-2" />
                    </div>
                </div>

                <div class="mt-3">
                    <label class="block text-sm mb-1">Дни недели (weekly)</label>
                    <div class="flex flex-wrap gap-3 text-sm">
                        @php $weeklyDays = $rule?->weekly_days ?? []; @endphp
                        @foreach ([1 => 'Пн', 2 => 'Вт', 3 => 'Ср', 4 => 'Чт', 5 => 'Пт', 6 => 'Сб', 7 => 'Вс'] as $day => $label)
                            <label class="flex items-center gap-1">
                                <input type="checkbox" name="rules[0][weekly_days][]" value="{{ $day }}"
                                    @checked(in_array($day, $weeklyDays, true)) />
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm mb-1">Время</label>
                        <input type="time" name="rules[0][time_of_day]" value="{{ $rule?->time_of_day }}"
                            class="w-full border rounded p-2" />
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Дата начала повторения</label>
                        <input type="date" name="rules[0][start_date]"
                            value="{{ $rule?->start_date?->format('Y-m-d') }}" class="w-full border rounded p-2" />
                    </div>
                </div>

                <div class="mt-3">
                    <label class="block text-sm mb-1">Ежемесячно: неделя и день</label>
                    @php $monthlyRule = $rule?->monthly_rules[0] ?? ['week' => 1, 'weekday' => 1]; @endphp
                    <div class="grid grid-cols-2 gap-2">
                        <select name="rules[0][monthly_rules][0][week]" class="border rounded p-2">
                            @foreach ([1, 2, 3, 4, 5] as $week)
                                <option value="{{ $week }}" @selected((int) $monthlyRule['week'] === $week)>
                                    {{ $week }} неделя</option>
                            @endforeach
                        </select>
                        <select name="rules[0][monthly_rules][0][weekday]" class="border rounded p-2">
                            @foreach ([1 => 'Пн', 2 => 'Вт', 3 => 'Ср', 4 => 'Чт', 5 => 'Пт', 6 => 'Сб', 7 => 'Вс'] as $day => $label)
                                <option value="{{ $day }}" @selected((int) $monthlyRule['weekday'] === $day)>
                                    {{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex gap-2">
                <button class="px-4 py-2 bg-indigo-600 text-white rounded">Сохранить</button>
                <a href="{{ route('recurring-tasks.show', $recurringTask) }}"
                    class="px-4 py-2 bg-gray-200 rounded">Отмена</a>
            </div>
        </form>
    </div>
@endsection
