@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-semibold">Пользователи</h1>
            <a href="{{ route('users.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded">Создать</a>
        </div>

        <div class="bg-white rounded shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3 text-left">ID</th>
                        <th class="p-3 text-left">Имя</th>
                        <th class="p-3 text-left">Логин</th>
                        <th class="p-3 text-left">Email</th>
                        <th class="p-3 text-left">Роль</th>
                        <th class="p-3 text-left">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr class="border-t">
                            <td class="p-3">{{ $user->id }}</td>
                            <td class="p-3">{{ $user->name }}</td>
                            <td class="p-3">{{ $user->login }}</td>
                            <td class="p-3">{{ $user->email ?? '-' }}</td>
                            <td class="p-3">{{ $user->role === 'admin' ? 'Администратор' : 'Менеджер' }}</td>
                            <td class="p-3">
                                <a href="{{ route('users.edit', $user) }}"
                                    class="text-indigo-600 hover:underline">Редактировать</a>
                                <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline-block ms-3"
                                    onsubmit="return confirm('Удалить пользователя?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 hover:underline">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </div>
@endsection
