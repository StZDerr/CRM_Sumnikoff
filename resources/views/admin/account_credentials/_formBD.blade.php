@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto px-4 py-8">
        {{-- Заголовок --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">
                {{ isset($accountCredential) ? 'Редактировать доступ' : 'Создать доступ к БД проекта ' . $project->title }}
            </h1>
            <p class="text-gray-500 mt-1">
                Управляйте логинами, паролями и привязкой к проекту и организации.
            </p>
        </div>
        {{-- Карточка формы --}}
        <div class="bg-white shadow-md rounded-lg border border-gray-200 p-6">
            <form
                action="{{ isset($accountCredential)
                    ? route('account-credentials.update', [$project, $accountCredential])
                    : route('account-credentials.storeBD', $project) }}"
                method="POST">
                @csrf
                @if (isset($accountCredential))
                    @method('PUT')
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Название аккаунта --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Название аккаунта</label>
                        <input type="text" name="name"
                            value="{{ old('name', $accountCredential->name ?? 'Доступ к БД') }}"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            required>
                    </div>

                    {{-- Имя БД --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Имя БД</label>
                        <input type="text" name="db_name" value="{{ old('db_name', $accountCredential->db_name ?? '') }}"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    {{-- Логин --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Логин</label>
                        <input type="text" name="login" value="{{ old('login', $accountCredential->login ?? '') }}"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    {{-- Пароль --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Пароль</label>
                        <input type="text" name="password" value=""
                            placeholder="{{ isset($accountCredential) ? 'Оставьте пустым, чтобы не менять' : '' }}"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            {{ isset($accountCredential) ? '' : 'required' }}>
                    </div>

                    {{-- Статус --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Статус</label>
                        <select name="status"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="active"
                                {{ old('status', $accountCredential->status ?? 'active') == 'active' ? 'selected' : '' }}>
                                Действующий</option>
                            <option value="stop_list"
                                {{ old('status', $accountCredential->status ?? '') == 'stop_list' ? 'selected' : '' }}>Stop
                                List</option>
                            List</option>
                        </select>
                    </div>

                    {{-- Проект (отображение) --}}
                    <input type="hidden" name="project_id" value="{{ $project->id }}">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Проект</label>
                        <input type="text" value="{{ $project->title }}" disabled
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100">
                    </div>

                    {{-- Организация (отображение) --}}
                    <input type="hidden" name="organization_id" value="{{ $project->organization->id }}">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Организация</label>
                        <input type="text" value="{{ $project->organization->name_full ?? '-' }}" disabled
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100">
                    </div>

                    {{-- Примечания --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Примечания</label>
                        <textarea name="notes" rows="4"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('notes', $accountCredential->notes ?? '') }}</textarea>
                    </div>
                </div>

                {{-- Кнопка сохранения --}}
                <div class="mt-6 flex justify-end">
                    <button type="submit"
                        class="inline-flex items-center px-6 py-2 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        Сохранить
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
