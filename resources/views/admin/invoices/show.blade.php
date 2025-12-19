@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <!-- Кнопка назад -->
                <a href="{{ url()->previous() }}" class="px-3 py-1 border rounded hover:bg-gray-100">
                    ← Назад
                </a>
                <h1 class="text-2xl">{{ $invoice->number }}</h1>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('invoices.edit', $invoice) }}"
                    class="px-3 py-1 border rounded hover:bg-gray-100">Редактировать</a>
                <form action="{{ route('invoices.destroy', $invoice) }}" method="POST"
                    onsubmit="return confirm('Удалить счёт?')">
                    @csrf
                    @method('DELETE')
                    <button class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700">Удалить</button>
                </form>
            </div>
        </div>

        <div class="bg-white shadow rounded p-4 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <div class="text-xs text-gray-500">Дата</div>
                    <div class="font-medium">{{ $invoice->issued_at->format('Y-m-d') }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Проект</div>
                    <div class="font-medium">{{ $invoice->project?->title ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Сумма</div>
                    <div class="font-medium">{{ number_format($invoice->amount, 2, '.', ' ') }} ₽</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Назначение</div>
                    <div class="font-medium">{{ $invoice->paymentMethod?->title ?? '-' }}</div>
                </div>
                <div class="md:col-span-2">
                    <div class="text-xs text-gray-500">Вложения</div>
                    <div class="mt-1">
                        @if ($invoice->attachments)
                            <div class="flex gap-2 flex-wrap">
                                @foreach ($invoice->attachments as $path)
                                    <a href="{{ Storage::url($path) }}" class="inline-block p-1 border rounded text-sm"
                                        target="_blank">Файл</a>
                                @endforeach
                            </div>
                        @else
                            <div class="text-sm text-gray-500">Нет вложений</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
