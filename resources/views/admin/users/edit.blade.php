@extends('layouts.app')

@push('scripts')
    @vite('resources/js/password-generator.js')
@endpush

@section('content')
    <div class="max-w-4xl mx-auto px-4">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">
                Редактирование пользователя
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
            <form action="{{ route('users.update', $user) }}" method="POST" enctype="multipart/form-data"
                class="p-6 space-y-6">
                @csrf
                @method('PUT')

                {{-- Основные данные --}}
                <div>
                    <h2 class="text-lg font-medium text-gray-800 mb-4">
                        Основная информация
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Имя</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}"
                                class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Логин</label>
                            <input type="text" name="login" value="{{ old('login', $user->login) }}"
                                class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Email</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}"
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

                        <div>
                            <label class="block text-sm font-medium mb-1">Дата рождения</label>
                            <input type="date" name="birth_date"
                                value="{{ old('birth_date', $user->birth_date ? $user->birth_date->format('Y-m-d') : '') }}"
                                class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Должность</label>
                            <input type="text" name="position" value="{{ old('position', $user->position) }}"
                                placeholder="Например: Старший маркетолог"
                                class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Вид работы</label>
                            <input type="text" name="work_type" value="{{ old('work_type', $user->work_type) }}"
                                placeholder="Офис / Удалённо / Гибрид"
                                class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Аватар (изображение)</label>

                            @if ($user->avatar)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="Аватар"
                                        class="h-20 w-20 rounded-full object-cover border" />
                                </div>
                            @endif

                            <input type="file" name="avatar" accept="image/*"
                                class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">

                            @if ($user->avatar)
                                <label class="mt-2 inline-flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="remove_avatar" value="1"
                                        class="rounded border-gray-300 text-red-600">
                                    <span class="text-sm text-gray-600">Удалить текущий аватар</span>
                                </label>
                            @endif

                            <div class="text-xs text-gray-500 mt-1">Файл будет сохранён в папке
                                <code>storage/app/public/avatars</code>.</div>
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
                                        {{ old('specialty_id', $user->specialty_id) == $specialty->id ? 'selected' : '' }}>
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
                                    {{ old('is_department_head', $user->is_department_head) ? 'checked' : '' }}>
                                <div>
                                    <div class="font-medium text-gray-800">Начальник отдела</div>
                                    <div class="text-sm text-gray-500">
                                        Используется индивидуальный оклад вместо специальности
                                    </div>
                                </div>
                            </label>

                            {{-- Индивидуальный оклад --}}
                            <div id="salaryOverrideBlock" class="mt-4"
                                style="{{ old('is_department_head', $user->is_department_head) ? '' : 'display:none' }}">
                                <label class="block text-sm font-medium mb-1">Индивидуальный оклад</label>
                                <input type="number" name="salary_override"
                                    value="{{ old('salary_override', $user->salary_override) }}" min="0"
                                    step="1000"
                                    class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>

                        {{-- Индивидуальная премия --}}
                        <div class="mt-4">
                            <label class="block text-sm font-medium mb-1">
                                % Индивидуальной премии
                            </label>
                            <input type="number" name="individual_bonus_percent"
                                value="{{ old('individual_bonus_percent', $user->individual_bonus_percent ?? 5) }}"
                                min="0" max="100" step="1"
                                class="w-32 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium mb-1">Прогноз ФОТ (руб)</label>
                            <input type="number" name="forecast_amount"
                                value="{{ old('forecast_amount', $user->forecast_amount) }}" min="0"
                                step="100"
                                class="w-48 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <div class="text-xs text-gray-500 mt-1">При заполнении значение будет использовано в отчёте
                                прогнозного ФОТ вместо авт. расчёта.</div>
                        </div>
                    </div>
                </div>

                {{-- Социальные сети --}}
                <div>
                    <h2 class="text-lg font-medium text-gray-800 mb-4">
                        Социальные сети
                    </h2>

                    <div id="socialsContainer" class="space-y-2">
                        @php
                            $oldSocials = old('socials', $user->socials->toArray());
                        @endphp

                        @forelse($oldSocials as $i => $social)
                            <div class="flex gap-2 items-center">
                                <select name="socials[{{ $i }}][platform]"
                                    class="rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">—</option>
                                    <option value="vk" @selected(($social['platform'] ?? '') === 'vk')>ВК</option>
                                    <option value="telegram" @selected(($social['platform'] ?? '') === 'telegram')>ТГ</option>
                                    <option value="maks" @selected(($social['platform'] ?? '') === 'maks')>МАКС</option>
                                </select>
                                <input type="text" name="socials[{{ $i }}][url]"
                                    placeholder="Ссылка или логин" value="{{ $social['url'] ?? '' }}"
                                    class="flex-1 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <button type="button"
                                    class="removeSocial px-2 py-1 text-red-600 hover:bg-red-100 rounded">×</button>
                            </div>
                        @empty
                            <div class="flex gap-2 items-center">
                                <select name="socials[0][platform]"
                                    class="rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">—</option>
                                    <option value="vk">ВК</option>
                                    <option value="telegram">ТГ</option>
                                    <option value="maks">МАКС</option>
                                </select>
                                <input type="text" name="socials[0][url]" placeholder="Ссылка или логин"
                                    class="flex-1 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <button type="button"
                                    class="removeSocial px-2 py-1 text-red-600 hover:bg-red-100 rounded">×</button>
                            </div>
                        @endforelse
                    </div>

                    <button type="button" id="addSocial"
                        class="mt-2 px-3 py-1 text-sm bg-gray-200 rounded-md hover:bg-gray-300 transition">
                        Добавить соцсеть
                    </button>
                </div>


                {{-- Пароль --}}
                <div>
                    <h2 class="text-lg font-medium text-gray-800 mb-4">
                        Безопасность
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Поля для ввода пароля --}}
                        <div>
                            <label class="block text-sm font-medium mb-1">Пароль</label>
                            <input type="password" id="password" name="password"
                                class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Повтор пароля</label>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>

                    {{-- Сгенерированный пароль --}}
                    <div class="mt-4">
                        <label class="block text-sm font-medium mb-1">Сгенерированный пароль</label>
                        <div class="flex gap-2">
                            <input type="text" id="generatedPassword" readonly
                                class="w-full rounded-md border-gray-300 bg-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                            <button type="button" id="copyPassword"
                                class="px-3 py-1 text-sm bg-gray-200 rounded-md hover:bg-gray-300 transition">
                                Скопировать
                            </button>
                            <button type="button" id="generatePassword"
                                class="px-3 py-1 text-sm bg-gray-200 rounded-md hover:bg-gray-300 transition">
                                Сгенерировать
                            </button>
                        </div>
                    </div>

                    {{-- Только для пользователя с ID = 1: сброс и показ пароля --}}
                    @if ($user->id === 1)
                        <div class="mt-4 rounded-md border border-yellow-100 bg-yellow-50 p-3"
                            id="reset-show-password-block">
                            <label class="block text-sm font-medium mb-1">Пароль пользователя (ID: 1)</label>
                            <div class="flex gap-2 items-center">
                                <input type="text" id="revealedPassword" readonly
                                    class="w-full rounded-md border-gray-300 bg-white text-sm px-3 py-2">
                                <button type="button" id="copyRevealedPassword"
                                    class="px-3 py-1 text-sm bg-gray-200 rounded-md hover:bg-gray-300 transition">
                                    Скопировать
                                </button>
                                <button type="button" id="resetShowPassword"
                                    class="px-3 py-1 text-sm bg-yellow-500 text-white rounded-md hover:bg-yellow-600 transition">
                                    Сбросить и показать
                                </button>
                            </div>
                            <div class="text-xs text-gray-500 mt-2">Нельзя восстановить пароль из хэша — этот action
                                сбросит пароль и покажет новый для копирования.</div>
                        </div>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <a href="{{ route('users.index') }}"
                        class="px-4 py-2 text-sm border rounded-md text-gray-600 hover:bg-gray-100 transition">
                        Отмена
                    </a>
                    <button type="submit"
                        class="px-6 py-2 text-sm font-medium bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                        Сохранить изменения
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
            const container = document.getElementById('socialsContainer');
            const addBtn = document.getElementById('addSocial');

            addBtn.addEventListener('click', () => {
                const index = container.children.length;
                const div = document.createElement('div');
                div.className = 'flex gap-2 items-center';
                div.innerHTML = `
            <select name="socials[${index}][platform]" class="rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">—</option>
                <option value="vk">ВК</option>
                <option value="telegram">ТГ</option>
                <option value="maks">МАКС</option>
            </select>
            <input type="text" name="socials[${index}][url]" placeholder="Ссылка или логин" class="flex-1 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            <button type="button" class="removeSocial px-2 py-1 text-red-600 hover:bg-red-100 rounded">×</button>
        `;
                container.appendChild(div);
            });

            container.addEventListener('click', (e) => {
                if (e.target.classList.contains('removeSocial')) {
                    e.target.parentElement.remove();
                }
            });

            // copy generated password
            const copyBtn = document.getElementById('copyPassword');
            if (copyBtn) {
                copyBtn.addEventListener('click', () => {
                    const val = document.getElementById('generatedPassword').value || '';
                    if (!val) return;
                    navigator.clipboard?.writeText(val).then(() => {
                        copyBtn.textContent = 'Скопировано';
                        setTimeout(() => (copyBtn.textContent = 'Скопировать'), 1500);
                    });
                });
            }

            // Reset & show password for user ID 1 (AJAX)
            const resetBtn = document.getElementById('resetShowPassword');
            const revealedInput = document.getElementById('revealedPassword');
            const copyRevealed = document.getElementById('copyRevealedPassword');
            if (resetBtn && revealedInput) {
                resetBtn.addEventListener('click', async (ev) => {
                    if (!confirm('Сбросить пароль для пользователя ID=1 и показать новый?')) return;
                    resetBtn.disabled = true;
                    resetBtn.textContent = 'Сброс...';
                    try {
                        const res = await fetch('/users/{{ $user->id }}/reset-show-password', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({})
                        });
                        if (!res.ok) {
                            const txt = await res.text();
                            throw new Error(txt || 'Ошибка сервера');
                        }
                        const data = await res.json();
                        revealedInput.value = data.password;
                        // also populate password fields so the form shows the new value
                        const pass = document.getElementById('password');
                        const passConf = document.getElementById('password_confirmation');
                        if (pass) pass.value = data.password;
                        if (passConf) passConf.value = data.password;
                        // reflect in generatedPassword field as well
                        const gen = document.getElementById('generatedPassword');
                        if (gen) gen.value = data.password;
                    } catch (err) {
                        alert('Не удалось сбросить пароль: ' + (err.message || err));
                    } finally {
                        resetBtn.disabled = false;
                        resetBtn.textContent = 'Сбросить и показать';
                    }
                });
            }

            if (copyRevealed) {
                copyRevealed.addEventListener('click', () => {
                    const v = revealedInput.value || '';
                    if (!v) return;
                    navigator.clipboard?.writeText(v).then(() => {
                        copyRevealed.textContent = 'Скопировано';
                        setTimeout(() => (copyRevealed.textContent = 'Скопировать'), 1500);
                    });
                });
            }
        });
    </script>
@endsection
