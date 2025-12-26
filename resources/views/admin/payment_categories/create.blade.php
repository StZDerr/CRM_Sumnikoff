@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold">Создать категорию платежа</h1>
            <a href="{{ route('payment-categories.index') }}" class="text-sm text-gray-500">Назад</a>
        </div>

        <div class="bg-white shadow rounded p-6">
            <form action="{{ route('payment-categories.store') }}" method="POST" class="space-y-6">
                @csrf
                @include('admin.payment_categories._form', ['paymentCategory' => $paymentCategory])
                <div class="flex items-center justify-between">
                    <a href="{{ route('payment-categories.index') }}" class="text-sm text-gray-500">Отмена</a>
                    <button class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Создать</button>
                </div>
            </form>
        </div>
    </div>
@endsection
