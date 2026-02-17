@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-semibold">Пользователь #{{ $user->id }}</h1>
            <a href="{{ route('users.index') }}"
                class="px-3 py-2 border rounded text-sm text-gray-700 hover:bg-gray-100">Назад</a>
        </div>

        <div class="bg-white rounded shadow p-4">
            @if ($user->avatar)
                <div class="mb-4">
                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="Аватар"
                        class="h-20 w-20 rounded-full object-cover border" />
                </div>
            @endif

            <p><strong>Имя:</strong> {{ $user->name }}</p>
            <p><strong>Логин:</strong> {{ $user->login }}</p>
            <p><strong>Должность:</strong> {{ $user->position ?? '&mdash;' }}</p>
            <p><strong>Вид работы:</strong> {{ $user->work_type ?? '&mdash;' }}</p>
            <p><strong>Email:</strong> {{ $user->email ?? '-' }}</p>
            <p><strong>Роль:</strong> {{ $user->role === 'admin' ? 'Администратор' : 'Менеджер' }}</p>
            <p><strong>Создан:</strong> {{ $user->created_at->format('d.m.Y H:i') }}</p>
            <p><strong>Прогноз ФОТ:</strong> {!! $user->forecast_amount ? number_format($user->forecast_amount, 0, '', ' ') . ' ₽' : '&mdash;' !!}</p>
        </div>
    </div>
@endsection
