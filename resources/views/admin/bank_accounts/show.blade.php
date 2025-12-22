@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold">Счёт: {{ $bankAccount->title }}</h1>
            <div class="flex items-center gap-3">
                <a href="{{ route('bank-accounts.edit', $bankAccount) }}"
                    class="text-indigo-600 hover:underline">Редактировать</a>
                <a href="{{ route('bank-accounts.index') }}" class="text-sm text-gray-500">Назад</a>
            </div>
        </div>

        <div class="bg-white shadow rounded p-6">
            <dl class="grid grid-cols-1 gap-4">
                <div>
                    <dt class="text-xs text-gray-500">Наименование</dt>
                    <dd class="text-sm">{{ $bankAccount->title }}</dd>
                </div>

                <div>
                    <dt class="text-xs text-gray-500">Р/сч</dt>
                    <dd class="text-sm">{{ $bankAccount->account_number }}</dd>
                </div>

                <div>
                    <dt class="text-xs text-gray-500">к/сч</dt>
                    <dd class="text-sm">{{ $bankAccount->correspondent_account ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-xs text-gray-500">БИК</dt>
                    <dd class="text-sm">{{ $bankAccount->bik ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-xs text-gray-500">ИНН</dt>
                    <dd class="text-sm">{{ $bankAccount->inn ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-xs text-gray-500">Банк</dt>
                    <dd class="text-sm">{{ $bankAccount->bank_name ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-xs text-gray-500">Примечание</dt>
                    <dd class="text-sm">{{ $bankAccount->notes ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-xs text-gray-500">Создан</dt>
                    <dd class="text-sm">{{ $bankAccount->created_at?->format('Y-m-d H:i') ?? '-' }}</dd>
                </div>
            </dl>
        </div>
    </div>
@endsection
