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
                    <th class="p-3 text-left">Аванс</th>
                    <th class="p-3 text-left">Основная ЗП</th>
                    <th class="p-3 text-left">Итого</th>
                    <th class="p-3 text-left">Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($reports as $report)
                    <tr class="border-b">
                        <td class="p-3">
                            @if ($report->user)
                                <div class="flex items-center gap-2">
                                    <span>{{ $report->user->name }}</span>

                                    @if ($report->user->trashed())
                                        <!-- deleted user trash-in-red-square icon (bootstrap bi-trash3) -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 16 16"
                                            aria-hidden="true">
                                            <title>Удалённый пользователь</title>
                                            <rect x="0" y="0" width="16" height="16" rx="3" fill="#ef4444" />
                                            <path fill="#ffffff"
                                                d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5M11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47M8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5" />
                                        </svg>
                                    @endif
                                </div>
                            @else
                                <div class="flex items-center gap-2 text-gray-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 16 16"
                                        aria-hidden="true">
                                        <title>Удалённый пользователь</title>
                                        <rect x="0" y="0" width="16" height="16" rx="3" fill="#ef4444" />
                                        <path fill="#ffffff"
                                            d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5M11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47M8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5" />
                                    </svg>
                                    {{ 'Удалённый пользователь (ID: ' . $report->user_id . ')' }}
                                </div>
                            @endif
                        </td>
                        <td class="p-3">
                            {{ \Carbon\Carbon::parse($report->month)->locale('ru')->translatedFormat('F Y') }}</td>
                        <td class="p-3">{{ $report->status_label }}</td>
                        <td class="p-3">{{ number_format($report->advance_amount ?? 0, 0, '', ' ') }} ₽</td>
                        <td class="p-3">{{ number_format($report->total_salary ?? 0, 0, '', ' ') }} ₽</td>
                        <td class="p-3 font-semibold">
                            {{ number_format(($report->total_salary ?? 0) + ($report->advance_amount ?? 0), 0, '', ' ') }} ₽
                        </td>
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
