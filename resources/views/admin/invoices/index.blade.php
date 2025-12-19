@extends('layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto">
        @php $createParams = isset($project) ? ['project' => $project->id] : []; @endphp

        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl">
                @if (isset($project))
                    Счета проекта: {{ $project->title }}
                    <a href="{{ route('invoices.index') }}" class="text-sm text-gray-500 ml-3">Сбросить</a>
                @else
                    Выставленные счета
                @endif
            </h1>

            <a href="{{ route('invoices.create', $createParams) }}"
                class="px-4 py-2 bg-indigo-600 text-white rounded">Создать</a>
        </div>

        <div class="bg-white shadow rounded p-4">
            <table class="w-full text-sm">
                <thead class="text-left text-xs text-gray-500">
                    <tr>
                        <th>Номер</th>
                        <th>Дата</th>
                        <th>Проект</th>
                        <th>Сумма</th>
                        <th>Платёж</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach ($invoices as $inv)
                        <tr class="hover:bg-gray-50">
                            <td class="py-2">{{ $inv->number }}</td>
                            <td>{{ $inv->issued_at->format('Y-m-d') }}</td>
                            <td>{{ $inv->project->title ?? ($inv->project->name_short ?? '-') }}</td>
                            <td>{{ number_format($inv->amount, 2, '.', ' ') }} ₽</td>
                            <td>{{ $inv->paymentMethod?->title ?? '-' }}</td>
                            <td class="text-right">
                                <a href="{{ route('invoices.show', $inv) }}"
                                    class="text-indigo-600 hover:underline">Открыть</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-4">
                {{ $invoices->links() }}
            </div>
        </div>
    </div>
@endsection
