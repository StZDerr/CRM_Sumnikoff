@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto px-4 py-8">
        {{-- Заголовок --}}
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Доступ: IT Sumnikoff</h1>

        {{-- Карточка с информацией --}}
        <div class="bg-white shadow rounded-lg border border-gray-200 p-6 space-y-6">
            {{-- Основные данные --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    {{-- {{ dd($accountCredential) }} --}}
                    <span class="block text-gray-500 text-sm">Имя</span>
                    <span class="text-gray-800 font-medium">{{ $accountCredential->name }}</span>
                </div>

                <div>
                    {{-- {{ dd($accountCredential) }} --}}
                    <span class="block text-gray-500 text-sm">Логин</span>
                    <span class="text-gray-800 font-medium">{{ $accountCredential->login }}</span>
                </div>

                <div>
                    <span class="block text-gray-500 text-sm">Пароль</span>
                    <span class="text-gray-800 font-medium">{{ $accountCredential->password }}</span>
                </div>

                <div>
                    <span class="block text-gray-500 text-sm">Статус</span>
                    <span
                        class="inline-block px-2 py-1 rounded text-white font-semibold
                    {{ $accountCredential->status == 'active' ? 'bg-green-500' : 'bg-red-500' }}">
                        {{ $accountCredential->status == 'active' ? 'Действующий' : 'Stop List' }}
                    </span>
                </div>

            </div>

            {{-- Примечания --}}
            @if ($accountCredential->notes)
                <div>
                    <span class="block text-gray-500 text-sm mb-1">Примечания</span>
                    <div class="bg-gray-50 border border-gray-200 rounded p-3 text-gray-800 whitespace-pre-wrap">
                        {{ $accountCredential->notes }}
                    </div>
                </div>
            @endif

            {{-- Кнопки --}}
            <div class="flex space-x-2 mt-4">
                <a href="{{ route('account-credentials.itSumnikoff') }}"
                    class="inline-flex items-center px-4 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition">
                    ← Назад к списку
                </a>
                @if (auth()->user()->isAdmin() || auth()->user()->isProjectManager())
                    <a href="{{ route('account-credentials.editItSumnikoff', $accountCredential) }}"
                        class="inline-flex items-center px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition">
                        Редактировать
                    </a>
                @endif

            </div>
        </div>
    </div>
@endsection
