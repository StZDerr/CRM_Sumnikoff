@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <a href="{{ url()->previous() }}" class="px-3 py-1 border rounded hover:bg-gray-100">← Назад</a>
                <h1 class="text-2xl">Поступление — {{ number_format($payment->amount, 2) }} ₽</h1>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('payments.edit', $payment) }}"
                    class="px-3 py-1 border rounded hover:bg-gray-100">Редактировать</a>
                <form action="{{ route('payments.destroy', $payment) }}" method="POST"
                    onsubmit="return confirm('Удалить поступление?')">
                    @csrf
                    @method('DELETE')
                    <button class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700">Удалить</button>
                </form>
            </div>
        </div>

        <div class="bg-white shadow rounded p-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <div class="text-xs text-gray-500">Дата</div>
                    <div class="font-medium">{{ optional($payment->payment_date)->format('Y-m-d H:i') ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Проект</div>
                    <div class="font-medium">{{ $payment->project?->title ?? '-' }}</div>
                </div>

                <div>
                    <div class="text-xs text-gray-500">Сумма</div>
                    <div class="font-medium">{{ number_format($payment->amount, 2) }} ₽</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Способ оплаты</div>
                    <div class="font-medium">{{ $payment->paymentMethod?->title ?? '-' }}</div>
                </div>

                <div class="md:col-span-2">
                    <div class="text-xs text-gray-500">Счёт</div>
                    <div class="font-medium">{{ $payment->invoice?->number ?? '—' }}</div>
                </div>

                @if ($payment->transaction_id)
                    <div class="md:col-span-2">
                        <div class="text-xs text-gray-500">Транзакция</div>
                        <div class="font-medium">{{ $payment->transaction_id }}</div>
                    </div>
                @endif

                @if ($payment->note)
                    <div class="md:col-span-2">
                        <div class="text-xs text-gray-500">Примечание</div>
                        <div class="font-medium whitespace-pre-line">{{ $payment->note }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
