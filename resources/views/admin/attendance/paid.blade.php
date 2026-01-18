@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto px-4 py-6">
        <h1 class="text-xl font-semibold mb-4">Архив оплаченных табелей</h1>

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
                            @php
                                $canView =
                                    auth()->user()->isAdmin() ||
                                    auth()->user()->isProjectManager() ||
                                    (auth()->user()->isMarketer() &&
                                        $report->projectBonuses->contains(function ($b) {
                                            return optional($b->project)->marketer_id === auth()->id();
                                        }));
                            @endphp

                            @if ($canView)
                                <a href="{{ route('attendance.show', $report) }}"
                                    class="inline-flex items-center px-3 py-1 rounded bg-indigo-600 text-white text-sm">Открыть</a>
                            @else
                                <span class="text-sm text-gray-400">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
