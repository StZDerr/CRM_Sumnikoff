@extends('layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto px-4 py-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-semibold">Табель — {{ $report->user->name }} за
                {{ \Carbon\Carbon::parse($report->month)->translatedFormat('F Y') }}</h1>
            @if (auth()->user()->isAdmin())
                <a href="{{ route('attendance.approvals') }}"
                    class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 transition">Назад к согласованию</a>
            @else
                <a href="{{ route('welcome') }}"
                    class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 transition">Назад</a>
            @endif

        </div>

        <div class="bg-white rounded shadow p-6 space-y-6">
            <div class="grid grid-cols-2 gap-4">
                <div class="border rounded p-4">
                    <div class="text-sm text-gray-500">Статус</div>
                    <div class="mt-2 font-medium">{{ $report->status_label }}</div>
                </div>

                @php
                    $wage_days = ($report->base_salary / 22) * $report->ordinary_days;
                    $wage_remote = ($report->base_salary / 22) * ($report->remote_days / 2);
                    $audits = $report->audits_count * 300;
                    $individual = $report->individual_bonus;
                    $custom = $report->custom_bonus;
                    $fees = abs($report->fees ?? 0);
                    $penalties = abs($report->penalties ?? 0);
                    // Приоритет: явно сохранённый advance_amount, иначе сумма найденных расходов
                    $advance = abs($report->advance_amount ?? ($advanceTotal ?? 0));
                    $computedTotal =
                        $wage_days + $wage_remote + $audits + $individual + $custom - $fees - $penalties - $advance;
                @endphp

                <div class="border rounded p-4">
                    <div class="text-sm text-gray-500">Итоговая ЗП</div>
                    <div class="mt-2 text-indigo-600 font-semibold">
                        {{ number_format($wage_days, 0, '', ' ') }} +
                        {{ number_format($wage_remote, 0, '', ' ') }} +
                        {{ number_format($audits, 0, '', ' ') }} +
                        {{ number_format($individual, 0, '', ' ') }} +
                        {{ number_format($custom, 0, '', ' ') }} -
                        {{ number_format($fees, 0, '', ' ') }} -
                        {{ number_format($penalties, 0, '', ' ') }} -
                        {{ number_format($advance, 0, '', ' ') }}
                        =
                        {{ number_format($computedTotal, 0, '', ' ') }} ₽
                    </div>

                    @if (round($computedTotal) != round($report->total_salary))
                        <div class="mt-2 text-sm text-yellow-600">Внимание: сохранённая итоговая ЗП
                            {{ number_format($report->total_salary, 0, '', ' ') }} ₽ отличается от рассчитанной
                            ({{ number_format($computedTotal, 0, '', ' ') }} ₽).</div>
                    @endif
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
                    <div class="text-sm text-gray-500">Сборы</div>
                    <div class="mt-2 font-medium">{{ number_format($report->fees, 0, '.', ' ') }} ₽</div>
                </div>

                <div class="border rounded p-4">
                    <div class="text-sm text-gray-500">Штрафы</div>
                    <div class="mt-2 font-medium">{{ number_format($report->penalties ?? 0, 0, '.', ' ') }} ₽</div>
                </div>

                <div class="border rounded p-4">
                    <div class="text-sm text-gray-500">Аванс</div>
                    <div class="mt-2 font-medium">
                        {{ number_format(abs($report->advance_amount ?? ($advanceTotal ?? 0)), 2, '.', ' ') }} ₽</div>

                    @if (!empty($salaryExpenses) && $salaryExpenses->count() > 0)
                        <div class="mt-3 text-sm text-gray-600">
                            <div class="font-medium mb-2">Подробно:</div>
                            <ul class="space-y-1">
                                @foreach ($salaryExpenses as $exp)
                                    <li>
                                        {{ $exp->expense_date->translatedFormat('d.m.Y') }} — <span
                                            class="font-medium">{{ number_format($exp->amount, 2, '.', ' ') }} ₽</span>
                                        @if ($exp->document_number)
                                            <span class="text-gray-500">(№{{ $exp->document_number }})</span>
                                        @endif
                                        @if ($exp->description)
                                            <div class="text-gray-500">
                                                {{ \Illuminate\Support\Str::limit($exp->description, 120) }}</div>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <div class="mt-2 text-sm text-gray-400">Авансы не зарегистрированы</div>
                    @endif
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

            <!-- Детализация премии по проектам -->
            @if ($report->projectBonuses->count() > 0)
                <div class="border rounded p-4">
                    <div class="text-sm text-gray-500 mb-3">Детализация индивидуальной премии по проектам</div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b text-left text-gray-500">
                                    <th class="pb-2">Проект</th>
                                    <th class="pb-2 text-center">%</th>
                                    <th class="pb-2 text-right">Макс. премия</th>
                                    <th class="pb-2 text-center">Дней</th>
                                    <th class="pb-2 text-right">Премия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($report->projectBonuses as $bonus)
                                    <tr class="border-b border-gray-100">
                                        <td class="py-2">{{ $bonus->project->title ?? 'Проект удалён' }}</td>
                                        <td class="py-2 text-center">{{ $bonus->bonus_percent }}%</td>
                                        <td class="py-2 text-right text-gray-400">
                                            {{ number_format($bonus->max_bonus, 0, '', ' ') }} ₽</td>
                                        <td
                                            class="py-2 text-center {{ $bonus->days_worked > 0 ? 'text-green-600 font-medium' : 'text-red-400' }}">
                                            {{ $bonus->days_worked == intval($bonus->days_worked) ? intval($bonus->days_worked) : number_format($bonus->days_worked, 1, ',', '') }}
                                        </td>
                                        <td class="py-2 text-right font-medium">
                                            {{ number_format($bonus->bonus_amount, 0, '', ' ') }} ₽</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="border-t-2 border-gray-300 bg-gray-50">
                                    <td colspan="4" class="py-2 font-medium text-right">
                                        Итого премия:
                                    </td>
                                    <td class="py-2 text-right font-semibold text-indigo-600">
                                        {{ number_format($report->projectBonuses->sum('bonus_amount'), 0, '', ' ') }} ₽
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endif

            <div class="border rounded p-4 bg-gray-50">
                <div class="text-sm text-gray-500">Комментарий</div>

                <form action="{{ route('attendance.comment', $report->id) }}" method="POST" class="mt-2">
                    @csrf
                    <textarea name="comment" rows="3" class="w-full border rounded p-2 text-sm" placeholder="Комментарий">{{ old('comment', $report->comment) }}</textarea>

                    <div class="flex items-center justify-end mt-2 gap-2">
                        <button type="submit"
                            class="px-3 py-1 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition">Сохранить</button>
                    </div>
                </form>
            </div>

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
