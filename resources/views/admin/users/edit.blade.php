@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-semibold">Редактировать пользователя</h1>
            <a href="{{ route('users.index') }}"
                class="px-3 py-2 border rounded text-sm text-gray-700 hover:bg-gray-100">Назад</a>
        </div>

        @if ($errors->any())
            <div class="mb-4 text-red-600">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block mb-1">Имя</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}"
                    class="w-full border p-2 rounded" required />
            </div>

            <div class="mb-4">
                <label class="block mb-1">Логин</label>
                <input type="text" name="login" value="{{ old('login', $user->login) }}"
                    class="w-full border p-2 rounded" required />
            </div>

            <div class="mb-4">
                <label class="block mb-1">Email (необязательно)</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}"
                    class="w-full border p-2 rounded" />
            </div>

            <div class="mb-4">
                <label class="block mb-1">Роль</label>
                <select name="role" class="w-full border p-2 rounded">
                    <option value="manager" {{ old('role', $user->role) === 'manager' ? 'selected' : '' }}>Менеджер</option>
                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Администратор
                    </option>
                </select>
            </div>

            <div class="mb-4">
                <label class="block mb-1">Пароль (оставьте пустым, чтобы не менять)</label>
                <input type="password" name="password" class="w-full border p-2 rounded" />
            </div>

            <div class="mb-4">
                <label class="block mb-1">Подтвердите пароль</label>
                <input type="password" name="password_confirmation" class="w-full border p-2 rounded" />
            </div>

            <div>
                <button class="px-4 py-2 bg-indigo-600 text-white rounded">Сохранить</button>
            </div>
        </form>
    </div>
@endsection
