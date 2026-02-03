@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-semibold">Удаленные пользователи</h1>
            <a href="{{ route('users.index') }}" class="px-4 py-2 bg-gray-700 text-white rounded">Назад</a>
        </div>

        <div class="bg-white rounded shadow overflow-hidden">
            @php
                $roles = [
                    'admin' => 'Администратор',
                    'project_manager' => 'Проект-менеджер',
                    'marketer' => 'Маркетолог',
                    'frontend' => 'Верстальщик',
                    'designer' => 'Дизайнер',
                    'lawyer' => 'Юрист',
                ];
                $canManage = auth()->user()->isAdmin() || auth()->user()->isProjectManager();
            @endphp

            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3 text-left">№</th>
                        <th class="p-3 text-left">Имя</th>
                        <th class="p-3 text-left">Роль</th>
                        <th class="p-3 text-left">Удалён</th>
                        <th class="p-3 text-left">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($deletedUsers as $user)
                        <tr class="border-t">
                            <td class="p-3">{{ ($deletedUsers->firstItem() ?? 0) + $loop->iteration - 1 }}</td>
                            <td class="p-3">
                                <span class="text-gray-900">{{ $user->name }}</span>
                            </td>
                            <td class="p-3">{{ $roles[$user->role] ?? $user->role }}</td>
                            <td class="p-3">
                                {{ $user->deleted_at ? $user->deleted_at->format('d.m.Y H:i') : '—' }}
                            </td>
                            <td class="p-3 flex gap-2">
                                @if ($canManage)
                                    <a href="{{ route('attendance.userShow', $user) }}"
                                        class="px-3 py-1 rounded-md bg-green-600 text-white text-sm font-medium hover:bg-green-700 transition">Табель</a>

                                    <form action="{{ route('users.restore', $user) }}" method="POST"
                                        onsubmit="return confirm('Восстановить пользователя?')">
                                        @csrf
                                        <button type="submit"
                                            class="px-3 py-1 rounded-md bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">Восстановить</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr class="border-t">
                            <td colspan="5" class="p-3 text-gray-500">Удалённых пользователей нет.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $deletedUsers->links() }}
        </div>
    </div>
@endsection
