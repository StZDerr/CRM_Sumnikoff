@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto py-6">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl">Операции</h1>
        </div>

        <div class="bg-white shadow rounded p-4">
            <table class="w-full text-sm">
                <thead class="text-left text-xs text-gray-500">
                    <tr>
                        <th>Дата</th>
                        <th>Тип</th>
                        <th>Проект</th>
                        <th>Описание</th>
                        <th class="text-right">Сумма</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach ($operations as $op)
                        @php $m = $op['model']; @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="py-2">{{ optional($op['date'])->format('Y-m-d H:i') ?? '-' }}</td>
                            <td>{{ $op['type'] === 'payment' ? 'Поступление' : 'Расход' }}</td>
                            <td>{{ $m->project?->title ?? '-' }}</td>
                            <td class="max-w-xs truncate">
                                {{ $op['type'] === 'payment' ? $m->note ?? '' : $m->description ?? '' }}</td>
                            <td class="text-right {{ $op['type'] === 'payment' ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format($op['amount'], 2, '.', ' ') }} ₽
                            </td>
                            <td class="text-right">
                                @if ($op['type'] === 'payment')
                                    <a href="{{ route('payments.show', $m) }}"
                                        class="text-indigo-600 hover:underline">Открыть</a>
                                @else
                                    <a href="{{ route('expenses.show', $m) }}"
                                        class="text-indigo-600 hover:underline">Открыть</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-4">
                {{ $operations->links() }}
            </div>
        </div>
    </div>
@endsection
