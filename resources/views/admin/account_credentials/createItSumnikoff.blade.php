@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto px-4 py-8">
        <a href="{{ route('account-credentials.itSumnikoff') }}"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gradient-to-r from-indigo-600 via-blue-600 to-cyan-500 text-white text-sm font-semibold shadow-md hover:from-indigo-700 hover:via-blue-700 hover:to-cyan-600 focus:outline-none focus:ring-2 focus:ring-indigo-300 transform transition hover:-translate-y-0.5">
            Назад к списку
        </a>

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Создать доступ IT Sumnikoff</h1>
            <p class="text-gray-500 mt-1">Общие учётные данные, не привязанные к проекту.</p>
        </div>

        <div class="bg-white shadow-md rounded-lg border border-gray-200 p-6">
            <form action="{{ route('account-credentials.storeItSumnikoff') }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Название аккаунта</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Логин</label>
                        <input type="text" name="login" value="{{ old('login') }}"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Пароль</label>
                        <input type="text" name="password" value="" required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Примечания</label>
                        <textarea name="notes" rows="4"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('notes') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Статус</label>
                        <select name="status"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="active">Действующий</option>
                            <option value="stop_list">Stop List</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit"
                        class="inline-flex items-center px-6 py-2 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
@endsection
