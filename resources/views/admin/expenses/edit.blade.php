@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold">Редактировать расход</h1>
            <a href="{{ url()->previous() }}"
                class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 transition">
                ← Назад
            </a>
        </div>

        <div class="bg-white shadow rounded p-6">
            <form action="{{ route('expenses.update', $expense) }}" method="POST" enctype="multipart/form-data"
                class="space-y-6">
                @csrf @method('PUT')
                @include('admin.expenses._form')
                <div class="flex items-center justify-between">
                    <a href="{{ route('expenses.index') }}" class="text-sm text-gray-500">Отмена</a>
                    <button class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
@endsection
