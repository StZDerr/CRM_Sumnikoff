@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold">Редактирование ежемесячного расхода</h1>
            <a href="{{ route('monthly-expenses.index') }}"
                class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 transition">
                Назад
            </a>
        </div>

        <form method="POST" action="{{ route('monthly-expenses.update', $monthlyExpense) }}">
            @csrf
            @method('PUT')
            @include('admin.monthly_expenses._form')
        </form>
    </div>
@endsection
