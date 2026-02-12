@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto px-4 py-6">
        <h1 class="text-xl font-semibold mb-4">Табели на оплату Аванса</h1>

        <table class="min-w-full bg-white shadow rounded">
            <thead>
                <tr class="bg-gray-100 border-b">
                    <th class="p-3 text-left">Сотрудник</th>
                    <th class="p-3 text-left">Месяц</th>
                    <th class="p-3 text-left">Статус</th>
                    <th class="p-3 text-left">Итоговая ЗП</th>
                    <th class="p-3 text-left">Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($reports as $report)
                    <tr class="border-b">
                        <td class="p-3">{{ $report->user->name }}</td>
                        <td class="p-3">
                            {{ \Carbon\Carbon::parse($report->month)->locale('ru')->translatedFormat('F Y') }}</td>
                        <td class="p-3">{{ $report->status_label }}</td>
                        <td class="p-3">{{ number_format($report->total_salary, 0, '', ' ') }} ₽</td>
                        <td class="p-3">
                            <a href="{{ route('attendance.show', $report->id) }}"
                                class="px-2 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">Просмотр</a>
                            <button type="button" id="openAdvanceModal-{{ $report->id }}"
                                class="px-2 py-1 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                                Выдать аванс
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Модальные окна для каждого отчета --}}
        @foreach ($reports as $report)
            @include('admin.attendance._payable_modal', ['report' => $report])
        @endforeach
    </div>
@endsection
