@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold">Расход #{{ $expense->id }}</h1>
            <div class="flex items-center gap-3">
                <a href="{{ route('expenses.edit', $expense) }}" class="text-indigo-600 hover:underline">Редактировать</a>

                <form action="{{ route('expenses.destroy', $expense) }}" method="POST" class="inline"
                    onsubmit="return confirm('Удалить расход?');">
                    @csrf
                    @method('DELETE')
                    <button class="text-red-600 hover:text-red-800">Удалить</button>
                </form>

                <a href="{{ route('expenses.index') }}" class="text-sm text-gray-500">Назад</a>
            </div>
        </div>

        <div class="bg-white shadow rounded p-6">
            <dl class="grid grid-cols-1 gap-4">
                <div>
                    <dt class="text-xs text-gray-500">Дата</dt>
                    <dd class="text-sm">{{ $expense->expense_date?->format('Y-m-d H:i') }}</dd>
                </div>

                <div>
                    <dt class="text-xs text-gray-500">Сумма</dt>
                    <dd class="text-sm">{{ number_format($expense->amount, 2, '.', ' ') }} ₽</dd>
                </div>

                <div>
                    <dt class="text-xs text-gray-500">Категория</dt>
                    <dd class="text-sm">{{ $expense->category?->title ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-xs text-gray-500">Контрагент</dt>
                    <dd class="text-sm">{{ $expense->organization?->title ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-xs text-gray-500">Метод / Счёт</dt>
                    <dd class="text-sm">
                        {{ $expense->paymentMethod?->title ?? ($expense->bankAccount?->display_name ?? '-') }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs text-gray-500">Проект</dt>
                    <dd class="text-sm">{{ $expense->project?->title ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-xs text-gray-500">Статус</dt>
                    <dd class="text-sm">{{ $expense->status }}</dd>
                </div>

                <div>
                    <dt class="text-xs text-gray-500">Номер документа</dt>
                    <dd class="text-sm">{{ $expense->document_number ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-xs text-gray-500">Примечание</dt>
                    <dd class="text-sm">{{ $expense->description ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-xs text-gray-500">Документы</dt>
                    <dd class="text-sm">
                        @forelse($expense->documents as $doc)
                            <div class="mb-2">
                                <a href="{{ $doc->url }}" target="_blank"
                                    class="text-indigo-600 hover:underline">{{ $doc->original_name ?? $doc->path }}</a>
                            </div>
                        @empty
                            -
                        @endforelse
                    </dd>
                </div>
            </dl>
        </div>
    </div>
@endsection
