@extends('layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto px-4 py-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-semibold">Табель — {{ $report->user->name }} за
                {{ \Carbon\Carbon::parse($report->month)->locale('ru')->translatedFormat('F Y') }}</h1>

            <div class="flex items-center gap-4">
                <div class="text-sm text-gray-500">
                    <div class="text-sm text-gray-500">Статус</div>
                    <div class="mt-1 font-medium">{{ $report->status_label }}</div>
                </div>

                @if (auth()->user()->isAdmin())
                    <a href="{{ route('attendance.approvals') }}"
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 transition">Назад к
                        согласованию</a>
                @else
                    <a href="{{ route('welcome') }}"
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 transition">Назад</a>
                @endif
            </div>

        </div>

        <div class="bg-white rounded shadow p-6 space-y-6">
            <div class="grid grid-cols-2 gap-4">


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



                <div class="col-span-2 border rounded p-4 bg-green-50">
                    <div class="text-sm text-green-600 font-medium mb-3">Обычные дни — расчёт</div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <div class="text-sm text-gray-500">Обычные дни</div>
                            <div class="mt-2 font-medium">{{ number_format($report->ordinary_days, 0, '', ' ') }} </div>
                        </div>

                        <div>
                            <div class="text-sm text-gray-500">Стоимость 1 дня (делим на 22):</div>
                            <div class="mt-2 font-medium">{{ number_format($report->base_salary / 22, 0, '', ' ') }} ₽
                            </div>
                        </div>

                        <div>
                            <div class="text-sm text-gray-500">ЗП за
                                {{ number_format($report->ordinary_days, 0, '.', '') }} обычных дней</div>
                            <div class="mt-2 font-medium">
                                {{ number_format(($report->base_salary / 22) * $report->ordinary_days, 0, '', ' ') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-span-2 border rounded p-4 bg-blue-50">
                    <div class="text-sm text-blue-600 font-medium mb-3">Удалённые дни — расчёт</div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <div class="text-sm text-gray-500">Удалённые дни (половина)</div>
                            <div class="mt-2 font-medium">{{ number_format($report->remote_days, 0) }}</div>
                        </div>

                        <div>
                            <div class="text-sm text-gray-500">ЗП за удалённые дни</div>
                            <div class="mt-2 font-medium">
                                {{ number_format(($report->base_salary / 22) * ($report->remote_days / 2), 0, '', ' ') }} ₽
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-span-2 border rounded p-4 bg-indigo-50">
                    <div class="text-sm text-indigo-600 font-medium mb-3">Премии и аудиты</div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <div class="text-sm text-gray-500">Аудиты </div>
                            <div class="mt-2 font-medium">{{ number_format($report->audits_count * 300, 0, '', ' ') }} ₽
                            </div>
                        </div>

                        <div>
                            <div class="text-sm text-gray-500">Инд. премия за проекты</div>
                            <div class="mt-2 font-medium">{{ number_format($report->individual_bonus, 0, '', ' ') }} ₽
                            </div>
                        </div>

                        <div>
                            <div class="text-sm text-gray-500">Произвольная премия</div>
                            <div class="mt-2 font-medium">{{ number_format($report->custom_bonus, 0, '', ' ') }} ₽</div>
                        </div>
                    </div>
                </div>

                <div class="col-span-2 border rounded p-4 bg-red-50">
                    <div class="text-sm text-red-600 font-medium mb-3">Минус ЗП</div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <div class="text-sm text-gray-500">Сборы</div>
                            <div class="mt-2 font-medium text-red-600">-{{ number_format($fees, 0, '', ' ') }} ₽</div>
                        </div>

                        <div>
                            <div class="text-sm text-gray-500">Штрафы</div>
                            <div class="mt-2 font-medium text-red-600">-{{ number_format($penalties ?? 0, 0, '', ' ') }} ₽
                            </div>
                        </div>

                        <div>
                            <div class="text-sm text-gray-500">Аванс</div>
                            <div class="mt-2 font-medium text-red-600">-{{ number_format($advance, 0, '', ' ') }} ₽</div>
                        </div>
                    </div>
                </div>

                <div class="col-span-1 border rounded p-4 bg-gray-50">
                    <div class="text-sm text-gray-500">Итоговая ЗП</div>
                    <div class="mt-2 text-indigo-600 font-semibold">
                        <span data-tippy
                            data-tippy-content="ЗП за {{ number_format($report->ordinary_days, 0, '.', '') }} обычных дней"
                            class="cursor-help">{{ number_format($wage_days, 0, '', ' ') }}</span> +
                        <span data-tippy data-tippy-content="ЗП за удалённые дни"
                            class="cursor-help">{{ number_format($wage_remote, 0, '', ' ') }}</span> +
                        <span data-tippy data-tippy-content="Аудиты"
                            class="cursor-help">{{ number_format($audits, 0, '', ' ') }}</span> +
                        <span data-tippy data-tippy-content="Инд. премия за проекты"
                            class="cursor-help">{{ number_format($individual, 0, '', ' ') }}</span> +
                        <span data-tippy data-tippy-content="Произвольная премия"
                            class="cursor-help">{{ number_format($custom, 0, '', ' ') }}</span> -
                        <span data-tippy data-tippy-content="Сборы"
                            class="cursor-help">{{ number_format($fees, 0, '', ' ') }}</span> -
                        <span data-tippy data-tippy-content="Штрафы"
                            class="cursor-help">{{ number_format($penalties, 0, '', ' ') }}</span>
                        =
                        <span data-tippy data-tippy-content="Общая сумма к выплате (без учёта удержания аванса)"
                            class="cursor-help">{{ number_format($computedTotal + $advance, 0, '', ' ') }}</span> ₽
                    </div>

                </div>
                <div class="col-span-1 border rounded p-4 bg-gray-50">
                    <div class="text-sm text-gray-500">Итоговая ЗП без аванса</div>
                    <div class="mt-2 text-indigo-600 font-semibold">
                        -<span data-tippy data-tippy-content="Аванс"
                            class="cursor-help">{{ number_format($advance, 0, '', ' ') }}</span>
                        =
                        <span data-tippy data-tippy-content="Итоговая ЗП без аванса"
                            class="cursor-help">{{ number_format($computedTotal, 0, '', ' ') }}</span> ₽
                    </div>

                    @if (round($computedTotal) != round($report->total_salary))
                        <div class="mt-2 text-sm text-yellow-600">Внимание: сохранённая итоговая ЗП
                            {{ number_format($report->total_salary, 0, '', ' ') }} ₽ отличается от рассчитанной
                            ({{ number_format($computedTotal, 0, '', ' ') }} ₽).</div>
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
