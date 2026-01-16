@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto px-4">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">
                Создание пользователя
            </h1>
            <a href="{{ route('users.index') }}"
                class="inline-flex items-center px-3 py-2 text-sm border rounded-md text-gray-600 hover:bg-gray-100 transition">
                ← Назад
            </a>
        </div>

        {{-- Errors --}}
        @if ($errors->any())
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-red-700">
                <ul class="list-disc list-inside space-y-1 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Card --}}
        <div class="bg-white rounded-xl shadow-sm border">
            <form action="{{ route('users.store') }}" method="POST" class="p-6 space-y-6">
                @csrf

                {{-- Основные данные --}}
                <div>
                    <h2 class="text-lg font-medium text-gray-800 mb-4">
                        Основная информация
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Имя</label>
                            <input type="text" name="name" value="{{ old('name') }}"
                                class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Логин</label>
                            <input type="text" name="login" value="{{ old('login') }}"
                                class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}"
                                class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Роль</label>

                            <select name="role"
                                class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">— Выберите роль —</option>

                                @foreach (\App\Models\User::ROLES as $role)
                                    <option value="{{ $role }}" @selected(old('role', $user->role ?? '') === $role)>
                                        @switch($role)
                                            @case('admin')
                                                Администратор
                                            @break

                                            @case('project_manager')
                                                Проект-менеджер
                                            @break

                                            @case('marketer')
                                                Маркетолог
                                            @break

                                            @case('frontend')
                                                Верстальщик
                                            @break

                                            @case('designer')
                                                Дизайнер
                                            @break

                                            @default
                                                {{ ucfirst($role) }}
                                        @endswitch
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Специальность и оклад --}}
                <div>
                    <h2 class="text-lg font-medium text-gray-800 mb-4">
                        Должность и оклад
                    </h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Специальность</label>
                            <select name="specialty_id"
                                class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">— Не выбрано —</option>
                                @foreach ($specialties as $specialty)
                                    <option value="{{ $specialty->id }}"
                                        {{ old('specialty_id') == $specialty->id ? 'selected' : '' }}>
                                        {{ $specialty->name }} — {{ number_format($specialty->salary, 0, '', ' ') }} ₽
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Начальник отдела --}}
                        <div class="rounded-lg border p-4 bg-gray-50">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" id="isDepartmentHead" name="is_department_head" value="1"
                                    class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    {{ old('is_department_head') ? 'checked' : '' }}>
                                <div>
                                    <div class="font-medium text-gray-800">
                                        Начальник отдела
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        Используется индивидуальный оклад вместо специальности
                                    </div>
                                </div>
                            </label>

                            {{-- Индивидуальный оклад --}}
                            <div id="salaryOverrideBlock" class="mt-4"
                                style="{{ old('is_department_head') ? '' : 'display:none' }}">
                                <label class="block text-sm font-medium mb-1">
                                    Индивидуальный оклад
                                </label>
                                <input type="number" name="salary_override" value="{{ old('salary_override') }}"
                                    min="0" step="1000"
                                    class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>

                        {{-- Индивидуальная премия --}}
                        <div class="mt-4">
                            <label class="block text-sm font-medium mb-1">
                                % Индивидуальной премии
                            </label>
                            <input type="number" name="individual_bonus_percent"
                                value="{{ old('individual_bonus_percent', 5) }}" min="0" max="100"
                                step="1"
                                class="w-32 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>

                {{-- Пароль --}}
                <div>
                    <h2 class="text-lg font-medium text-gray-800 mb-4">
                        Безопасность
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Пароль</label>
                            <input type="password" name="password"
                                class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Повтор пароля</label>
                            <input type="password" name="password_confirmation"
                                class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <a href="{{ route('users.index') }}"
                        class="px-4 py-2 text-sm border rounded-md text-gray-600 hover:bg-gray-100 transition">
                        Отмена
                    </a>
                    <button type="submit"
                        class="px-6 py-2 text-sm font-medium bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                        Создать пользователя
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- JS --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const checkbox = document.getElementById('isDepartmentHead');
            const block = document.getElementById('salaryOverrideBlock');

            if (!checkbox || !block) return;

            checkbox.addEventListener('change', () => {
                block.style.display = checkbox.checked ? 'block' : 'none';
            });
        });
    </script>
@endsection
