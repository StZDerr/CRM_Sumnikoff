@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto px-4 py-8">
        {{-- Заголовок --}}
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Редактировать доступ</h1>
        <p class="text-gray-500 mb-6">Здесь вы можете изменить логин, пароль, статус и привязку к проекту и организации.</p>

        {{-- Карточка формы --}}
        <div class="bg-white shadow-md rounded-lg border border-gray-200 p-6">
            <form action="{{ route('account-credentials.update', $accountCredential) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Вставляем форму --}}
                @include('admin.account_credentials._form', ['project' => $project])

                {{-- Кнопка сохранения --}}
                <div class="mt-6 flex justify-end">
                    <button type="submit"
                        class="inline-flex items-center px-6 py-2 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                        Сохранить изменения
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
