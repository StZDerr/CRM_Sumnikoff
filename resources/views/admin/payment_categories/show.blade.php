@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold">Категория: {{ $paymentCategory->title }}</h1>
            <div class="flex items-center gap-3">
                <a href="{{ route('payment-categories.edit', $paymentCategory) }}"
                    class="text-indigo-600 hover:underline">Редактировать</a>
                <a href="{{ route('payment-categories.index') }}" class="text-sm text-gray-500">Назад</a>
            </div>
        </div>

        <div class="bg-white shadow rounded p-6">
            <dl class="grid grid-cols-1 gap-4">
                <div>
                    <dt class="text-xs text-gray-500">Название</dt>
                    <dd class="text-sm">{{ $paymentCategory->title }}</dd>
                </div>

                <div>
                    <dt class="text-xs text-gray-500">Slug</dt>
                    <dd class="text-sm">{{ $paymentCategory->slug ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-xs text-gray-500">Позиция</dt>
                    <dd class="text-sm">{{ $paymentCategory->sort_order ?? '-' }}</dd>
                </div>
            </dl>
        </div>
    </div>
@endsection
