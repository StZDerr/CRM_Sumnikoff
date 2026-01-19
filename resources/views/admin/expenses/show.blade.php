@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto py-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Расход #{{ $expense->id }}</h1>
            <div class="flex items-center gap-4">
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('expenses.edit', $expense) }}"
                        class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition">
                        Редактировать
                    </a>

                    <form action="{{ route('expenses.destroy', $expense) }}" method="POST" class="inline"
                        onsubmit="return confirm('Удалить расход?');">
                        @csrf
                        @method('DELETE')
                        <button class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">Удалить</button>
                    </form>
                @endif

                <a href="{{ url()->previous() }}"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">
                    Назад
                </a>
            </div>
        </div>

        <div class="bg-white shadow-lg rounded-lg overflow-hidden divide-y divide-gray-100">
            @if ($expense->expense_date)
                <div class="p-6 flex justify-between items-center">
                    <span class="text-gray-500 text-sm">Дата</span>
                    <span class="text-gray-800 font-medium">{{ $expense->expense_date->format('Y-m-d H:i') }}</span>
                </div>
            @endif

            @if ($expense->amount)
                <div class="p-6 flex justify-between items-center bg-gray-50">
                    <span class="text-gray-500 text-sm">Сумма</span>
                    <span class="text-gray-800 font-medium">{{ number_format($expense->amount, 2, '.', ' ') }} ₽</span>
                </div>
            @endif

            @if ($expense->category)
                <div class="p-6 flex justify-between items-center">
                    <span class="text-gray-500 text-sm">Категория</span>
                    <span class="text-gray-800 font-medium">{{ $expense->category->title }}</span>
                </div>
            @endif

            @if ($expense->organization)
                <div class="p-6 flex justify-between items-center bg-gray-50">
                    <span class="text-gray-500 text-sm">Контрагент</span>
                    <span class="text-gray-800 font-medium">{{ $expense->organization->title }}</span>
                </div>
            @endif

            @if ($expense->paymentMethod || $expense->bankAccount)
                <div class="p-6 flex justify-between items-center">
                    <span class="text-gray-500 text-sm">Метод / Счёт</span>
                    <span class="text-gray-800 font-medium">
                        {{ $expense->paymentMethod?->title ?? $expense->bankAccount?->display_name }}
                    </span>
                </div>
            @endif

            @if ($expense->project)
                <div class="p-6 flex justify-between items-center bg-gray-50">
                    <span class="text-gray-500 text-sm">Проект</span>
                    <span class="text-gray-800 font-medium">{{ $expense->project->title }}</span>
                </div>
            @endif

            @if ($expense->status)
                <div class="p-6 flex justify-between items-center">
                    <span class="text-gray-500 text-sm">Статус</span>
                    <span class="text-gray-800 font-medium">
                        {{ $expense->status_label }}
                    </span>
                </div>
            @endif

            @if ($expense->document_number)
                <div class="p-6 flex justify-between items-center bg-gray-50">
                    <span class="text-gray-500 text-sm">Номер документа</span>
                    <span class="text-gray-800 font-medium">{{ $expense->document_number }}</span>
                </div>
            @endif

            @if ($expense->description)
                <div class="p-6">
                    <span class="text-gray-500 text-sm">Примечание</span>
                    <p class="mt-1 text-gray-800">{{ $expense->description }}</p>
                </div>
            @endif

            @if ($expense->documents && $expense->documents->count())
                <div class="p-6 bg-gray-50">
                    <span class="text-gray-500 text-sm">Документы</span>
                    <ul class="mt-2 space-y-2">
                        @foreach ($expense->documents as $doc)
                            <li>
                                <a href="{{ $doc->url }}" target="_blank" class="text-indigo-600 hover:underline">
                                    {{ $doc->original_name ?? $doc->path }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
@endsection
