@extends('layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto px-4 py-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-semibold">Табель — {{ $report->user->name }} за
                {{ \Carbon\Carbon::parse($report->month)->translatedFormat('F Y') }}</h1>
            <a href="{{ route('attendance.approvals') }}"
                class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 transition">Назад к согласованию</a>
        </div>

        <div class="bg-white rounded shadow p-6 space-y-6">
            <div class="grid grid-cols-2 gap-4">
                <div class="border rounded p-4">
                    <div class="text-sm text-gray-500">Статус</div>
                    <div class="mt-2 font-medium">{{ $report->status_label }}</div>
                </div>

                <div class="border rounded p-4">
                    <div class="text-sm text-gray-500">Итоговая ЗП</div>
                    <div class="mt-2 text-indigo-600 font-semibold">
                        {{ number_format(($report->base_salary / 22) * $report->ordinary_days, 0, '', ' ') }} +
                        {{ number_format(($report->base_salary / 22) * ($report->remote_days / 2), 0, '', ' ') }} +
                        {{ number_format($report->audits_count * 300, 0, '', ' ') }} +
                        {{ number_format($report->individual_bonus, 0, '', ' ') }} +
                        {{ number_format($report->custom_bonus, 0, '', ' ') }} =

                        {{ number_format($report->total_salary, 0, '', ' ') }} ₽
                    </div>
                </div>

                <div class="border rounded p-4">
                    <div class="text-sm text-gray-500">Обычные дни</div>
                    <div class="mt-2 font-medium">{{ $report->ordinary_days }} </div>
                </div>


                <div class="border rounded p-4">
                    <div class="text-sm text-gray-500">Стоимость 1 дня (делим на 22):</div>
                    <div class="mt-2 font-medium">{{ number_format($report->base_salary / 22, 0, '', ' ') }} ₽</div>
                </div>

                <div class="border rounded p-4">
                    <div class="text-sm text-gray-500">ЗП за {{ $report->ordinary_days }} обычных дней</div>
                    <div class="mt-2 font-medium">
                        {{ number_format(($report->base_salary / 22) * $report->ordinary_days, 0, '', ' ') }} </div>
                </div>

                <div class="border rounded p-4">
                    <div class="text-sm text-gray-500">Удалённые дни (половина)</div>
                    <div class="mt-2 font-medium">{{ $report->remote_days }}</div>
                </div>

                <div class="border rounded p-4">
                    <div class="text-sm text-gray-500">ЗП за удалённые дни</div>
                    <div class="mt-2 font-medium">
                        {{ number_format(($report->base_salary / 22) * ($report->remote_days / 2), 0, '', ' ') }} </div>
                </div>

                <div class="border rounded p-4">
                    <div class="text-sm text-gray-500">Аудиты (по 300 рублей) (x {{ $report->audits_count }})</div>
                    <div class="mt-2 font-medium">{{ number_format($report->audits_count * 300, 0, '', ' ') }} ₽</div>
                </div>

                <div class="border rounded p-4">
                    <div class="text-sm text-gray-500">Инд. премия за проекты</div>
                    <div class="mt-2 font-medium">{{ number_format($report->individual_bonus, 0, '', ' ') }} ₽</div>
                </div>

                <div class="border rounded p-4">
                    <div class="text-sm text-gray-500">Произвольная премия</div>
                    <div class="mt-2 font-medium">{{ number_format($report->custom_bonus, 0, '', ' ') }} ₽</div>
                </div>

                <div class="border rounded p-4">
                    <div class="text-sm text-gray-500">Создан</div>
                    <div class="mt-2 font-medium">{{ $report->created_at->translatedFormat('d F Y H:i') }}@if ($report->creator)
                            — {{ $report->creator->name }}
                        @endif
                    </div>
                </div>

                <div class="border rounded p-4">
                    <div class="text-sm text-gray-500">Утверждён/Отклонён</div>
                    <div class="mt-2 font-medium">
                        @if ($report->approved_at)
                            {{ $report->approved_at->translatedFormat('d F Y H:i') }} —
                            {{ optional($report->updater)->name ?? (optional($report->approver)->name ?? '—') }}
                        @else
                            —
                        @endif
                    </div>
                </div>
            </div>

            @if ($report->comment)
                <div class="border rounded p-4 bg-gray-50">
                    <div class="text-sm text-gray-500">Комментарий</div>
                    <div class="mt-2">{{ $report->comment }}</div>
                </div>
            @endif

            <div class="flex items-center justify-end gap-3">
                @if ($report->status === 'submitted')
                    <form action="{{ route('attendance.approve', $report->id) }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">Одобрить</button>
                    </form>

                    <form action="{{ route('attendance.reject', $report->id) }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">Отклонить</button>
                    </form>
                @endif

                <a href="{{ route('attendance.approvals') }}"
                    class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 transition">Закрыть</a>
            </div>
        </div>
    </div>
@endsection
