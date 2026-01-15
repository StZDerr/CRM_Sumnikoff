@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto px-4 py-6">
        <h1 class="text-xl font-semibold mb-4">Табели на согласование</h1>

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
                        <td class="p-3">{{ \Carbon\Carbon::parse($report->month)->translatedFormat('F Y') }}</td>
                        <td class="p-3">{{ $report->status_label }}</td>
                        <td class="p-3">{{ number_format($report->total_salary, 0, '', ' ') }} ₽</td>
                        <td class="p-3">
                            <a href="{{ route('attendance.show', $report->id) }}"
                                class="px-2 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">Просмотр</a>
                            <form action="{{ route('attendance.approve', $report->id) }}" method="POST"
                                class="inline-block">
                                @csrf
                                <button type="submit" class="px-2 py-1 bg-green-600 text-white rounded hover:bg-green-700">
                                    Одобрить
                                </button>
                            </form>
                            <form action="{{ route('attendance.reject', $report->id) }}" method="POST"
                                class="inline-block">
                                @csrf
                                <button type="submit" class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700">
                                    Отклонить
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
