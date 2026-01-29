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

                    <div class="mt-3 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                        @foreach ($expense->documents as $doc)
                            <div class="bg-white border rounded p-2 flex items-center gap-3">
                                @if (str_starts_with($doc->mime ?? '', 'image/'))
                                    <a href="{{ route('documents.download', $doc) }}" class="inline-block">
                                        <img src="{{ $doc->url }}" alt="{{ $doc->original_name ?? 'image' }}"
                                            class="h-20 w-20 object-cover rounded" />
                                    </a>
                                    <div class="text-sm text-gray-600 truncate">{{ $doc->original_name ?? $doc->path }}
                                    </div>
                                @else
                                    <div class="flex-1">
                                        <a href="{{ route('documents.download', $doc) }}"
                                            class="text-indigo-600 hover:underline break-words">{{ $doc->original_name ?? $doc->path }}</a>
                                        <div class="text-xs text-gray-500">
                                            {{ strtoupper(pathinfo($doc->path ?? '', PATHINFO_EXTENSION)) }}</div>
                                    </div>
                                @endif

                                @if (auth()->user()->isAdmin())
                                    <button type="button" class="text-red-600 text-sm doc-delete-btn"
                                        data-url="{{ route('documents.destroy', $doc) }}">Удалить</button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        // Удаление документов через AJAX (для страницы просмотра)
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.doc-delete-btn');
            if (!btn) return;
            if (!confirm('Удалить документ?')) return;

            const url = btn.getAttribute('data-url');
            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(r => {
                if (!r.ok) throw new Error('Ошибка при удалении');
                return r.json();
            }).then(() => {
                const item = btn.closest('.bg-white.border.rounded.p-2');
                if (item) item.remove();
            }).catch(err => {
                console.error(err);
                alert('Не удалось удалить документ.');
            });
        });
    </script>

@endsection
