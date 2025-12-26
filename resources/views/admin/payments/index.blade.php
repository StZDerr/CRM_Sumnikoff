@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl">
                @if (isset($project))
                    Поступления проекта: {{ $project->title }}
                    <a href="{{ route('payments.index') }}" class="text-sm text-gray-500 ml-3">Сбросить</a>
                @else
                    Поступления
                @endif
            </h1>
            @php $createParams = isset($project) ? ['project' => $project->id] : []; @endphp

            <a href="{{ route('payments.create', $createParams) }}"
                class="px-4 py-2 bg-indigo-600 text-white rounded">Добавить</a>
        </div>
        <div class="bg-white shadow rounded p-4">
            <table class="w-full text-sm">
                <thead class="text-left text-xs text-gray-500">
                    <tr>
                        <th>Дата</th>
                        <th>Проект</th>
                        <th>Сумма</th>
                        <th>Метод</th>
                        <th>Счёт</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach ($payments as $p)
                        <tr class="hover:bg-gray-50">
                            <td class="py-2">{{ optional($p->payment_date)->format('Y-m-d H:i') ?? '-' }}</td>
                            <td>{{ $p->project?->title ?? '-' }}</td>
                            <td>{{ number_format($p->amount, 2, '.', ' ') }} ₽</td>
                            <td>{{ $p->paymentMethod?->title ?? '-' }}</td>
                            <td>{{ $p->invoice?->number ?? '-' }}</td>
                            <td class="text-right">
                                <a href="{{ route('payments.show', $p) }}"
                                    class="text-indigo-600 hover:underline">Открыть</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-4">
                {{ $payments->links() }}
            </div>
        </div>
    </div>
@endsection
