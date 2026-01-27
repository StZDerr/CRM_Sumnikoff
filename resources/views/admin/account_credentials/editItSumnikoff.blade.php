@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto px-4 py-8">
        {{-- Заголовок --}}
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Редактировать: IT Sumnikoff</h1>

        {{-- Форма редактирования --}}
        <form action="{{ route('account-credentials.updateItSumnikoff', $accountCredential) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="bg-white shadow rounded-lg border border-gray-200 p-6 space-y-6">
                {{-- Основные данные --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Имя --}}
                    <div>
                        <label class="block text-gray-500 text-sm mb-1" for="name">Имя</label>
                        <input type="text" id="name" name="name"
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            value="{{ old('name', $accountCredential->name) }}" required>
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Логин --}}
                    <div>
                        <label class="block text-gray-500 text-sm mb-1" for="login">Логин</label>
                        <input type="text" id="login" name="login"
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            value="{{ old('login', $accountCredential->login) }}">
                        @error('login')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Пароль --}}
                    <div>
                        <label class="block text-gray-500 text-sm mb-1" for="password">Пароль</label>
                        <input type="text" id="password" name="password"
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            value="{{ old('password', $accountCredential->password) }}">
                        @error('password')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Статус --}}
                    <div>
                        <label class="block text-gray-500 text-sm mb-1" for="status">Статус</label>
                        <select id="status" name="status"
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="active" {{ $accountCredential->status == 'active' ? 'selected' : '' }}>
                                Действующий</option>
                            <option value="stop_list" {{ $accountCredential->status == 'stop_list' ? 'selected' : '' }}>Stop
                                List</option>
                        </select>
                        @error('status')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Примечания --}}
                <div>
                    <label class="block text-gray-500 text-sm mb-1" for="notes">Примечания</label>
                    <textarea id="notes" name="notes" rows="4"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes', $accountCredential->notes) }}</textarea>
                    @error('notes')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Кнопки --}}
                <div class="flex space-x-2 mt-4">
                    <a href="{{ route('account-credentials.itSumnikoff') }}"
                        class="inline-flex items-center px-4 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition">
                        ← Назад к списку
                    </a>
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition">
                        Сохранить
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
