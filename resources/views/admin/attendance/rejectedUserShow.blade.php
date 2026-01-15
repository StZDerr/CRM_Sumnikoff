@extends('layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto px-4 py-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-semibold">Редактирование табеля — {{ $report->user->name }}</h1>
                <p class="text-sm text-gray-500 mt-1">
                    За {{ \Carbon\Carbon::parse($report->month)->translatedFormat('F Y') }} •
                    <span class="text-red-600 font-medium">Статус: Отклонён</span>
                </p>
            </div>
            <a href="{{ route('attendance.rejected') }}"
                class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 transition">
                Назад к отклонённым
            </a>
        </div>

        @if ($report->comment)
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <div class="text-sm font-medium text-red-800">Комментарий к отклонению:</div>
                <div class="mt-1 text-red-700">{{ $report->comment }}</div>
            </div>
        @endif

        <div class="bg-white rounded shadow p-6 space-y-6">

            <!-- Базовый оклад -->
            <div class="flex justify-between items-center border-b pb-3">
                <div>Специальность: <span
                        class="font-medium">{{ $report->user->specialty->name ?? 'Индивидуальная' }}</span></div>
                <div>Оклад: <span class="font-medium">{{ number_format($report->base_salary, 0, '', ' ') }} ₽</span></div>
            </div>

            <!-- Отработанные дни -->
            <div class="flex justify-between items-center border-b pb-3">
                <div>Отработано обычных дней:</div>
                <div>
                    <input type="number" name="ordinary_days" value="{{ $report->ordinary_days }}"
                        class="w-20 border rounded p-1 text-center" step="0.5" />
                    дней
                </div>
            </div>

            <div class="flex justify-between items-center border-b pb-3">
                <div>Отработано удаленных дней:</div>
                <div>
                    <input type="number" name="remote_days" value="{{ $report->remote_days }}"
                        class="w-20 border rounded p-1 text-center" step="0.5" />
                    дней (1 день = 0.5)
                </div>
            </div>

            <!-- Аудиты -->
            <div class="flex justify-between items-center border-b pb-3">
                <div>Количество аудитов:</div>
                <div>
                    <input type="number" name="audits_count" value="{{ $report->audits_count }}"
                        class="w-20 border rounded p-1 text-center" />
                    x 300 ₽ = <span id="audits-pay"
                        class="font-medium">{{ number_format($report->audits_count * 300, 0, '', ' ') }} ₽</span>
                </div>
            </div>

            <!-- Индивидуальная премия по проектам -->
            <div class="border-b pb-3">
                <div class="flex justify-between items-center mb-2">
                    <div class="font-medium">Индивидуальная премия по проектам:</div>
                </div>

                @if ($report->projectBonuses->count() > 0)
                    <div class="ml-4 mt-4 rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                        {{-- Header --}}
                        <div
                            class="grid grid-cols-12 gap-2 px-4 py-3 text-xs font-semibold text-gray-500 bg-gray-50 border-b">
                            <div class="col-span-5">Проект</div>
                            <div class="col-span-2 text-center">Макс. премия</div>
                            <div class="col-span-2 text-center">Дней</div>
                            <div class="col-span-3 text-right">Премия</div>
                        </div>

                        {{-- Rows --}}
                        @foreach ($report->projectBonuses as $bonus)
                            <div class="grid grid-cols-12 gap-2 px-4 py-3 items-center text-sm border-b last:border-b-0 hover:bg-gray-50 transition"
                                data-project-id="{{ $bonus->project_id }}" data-max-bonus="{{ $bonus->max_bonus }}"
                                data-bonus-per-day="{{ $bonus->max_bonus / 22 }}">

                                {{-- Project --}}
                                <div class="col-span-5 truncate font-medium text-gray-800"
                                    title="{{ $bonus->project->title ?? 'Проект удалён' }}">
                                    {{ $bonus->project->title ?? 'Проект удалён' }}
                                </div>

                                {{-- Max bonus --}}
                                <div class="col-span-2 text-center text-gray-500">
                                    {{ number_format($bonus->max_bonus, 0, '', ' ') }} ₽
                                </div>

                                {{-- Days (editable) --}}
                                <div class="col-span-2 text-center">
                                    <input type="number" step="0.5" min="0" max="31"
                                        class="project-days-input w-16 px-2 py-1 text-center border rounded text-xs font-semibold
                                            {{ $bonus->days_worked > 0 ? 'bg-green-50 text-green-700 border-green-300' : 'bg-red-50 text-red-600 border-red-300' }}"
                                        data-project-id="{{ $bonus->project_id }}"
                                        value="{{ $bonus->days_worked == intval($bonus->days_worked) ? intval($bonus->days_worked) : number_format($bonus->days_worked, 1, '.', '') }}">
                                </div>

                                {{-- Bonus (editable) --}}
                                <div class="col-span-3 text-right">
                                    <input type="number" step="1" min="0"
                                        class="project-bonus-input w-24 px-2 py-1 text-right border rounded font-semibold text-gray-800"
                                        data-project-id="{{ $bonus->project_id }}"
                                        value="{{ round($bonus->bonus_amount) }}">
                                    <span class="text-gray-500 ml-1">₽</span>
                                </div>
                            </div>
                        @endforeach

                        {{-- Footer / Total --}}
                        <div class="flex justify-between items-center px-4 py-3 bg-gray-50 text-sm font-semibold">
                            <span class="text-gray-600">Итого премия</span>
                            <span class="text-green-700">
                                <span
                                    id="total-project-bonus">{{ number_format($report->projectBonuses->sum('bonus_amount'), 0, '', ' ') }}</span>
                                ₽
                            </span>
                        </div>
                    </div>
                @else
                    <div class="ml-4 text-sm text-gray-400">Нет данных по проектам</div>
                @endif

                <div class="flex justify-between items-center mt-3 pt-2 border-t border-gray-300">
                    <div class="font-medium">Итого премия:</div>
                    <div class="font-semibold text-indigo-600">
                        <span id="individual-bonus">{{ number_format($report->individual_bonus, 0, '', ' ') }}</span> ₽
                    </div>
                </div>
            </div>

            <!-- Произвольная премия -->
            <div class="flex justify-between items-center border-b pb-3">
                <div>Произвольная премия:</div>
                <div>
                    <input type="number" name="custom_bonus" value="{{ $report->custom_bonus }}"
                        class="w-28 border rounded p-1 text-center" />
                    ₽
                </div>
            </div>

            <!-- Сборы -->
            <div class="flex justify-between items-center border-b pb-3">
                <div>Сборы (вводите число без знака — оно автоматически будет вычитаться):</div>
                <div>
                    <input type="number" name="fees" step="0.01" value="{{ abs($report->fees) }}"
                        class="w-28 border rounded p-1 text-center" />
                    ₽
                </div>
            </div>

            <!-- Штрафы -->
            <div class="flex justify-between items-center border-b pb-3">
                <div>Штрафы (вводите число без знака — оно автоматически будет вычитаться):</div>
                <div>
                    <input id="penalties-input" type="number" name="penalties" step="0.01"
                        value="{{ abs($report->penalties) }}" class="w-28 border rounded p-1 text-center" />
                    ₽
                </div>
            </div>

            <!-- Итоговая ЗП -->
            <div class="flex justify-between items-center text-lg font-semibold pt-3">
                <div>Итоговая ЗП:</div>
                <div class="text-indigo-600">≈ <span
                        id="total-salary">{{ number_format($report->total_salary, 0, '', ' ') }}</span> ₽</div>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" id="calculate-salary"
                    class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition">
                    Рассчитать
                </button>

                <!-- Форма обновления и повторной отправки -->
                <form id="resubmit-form" method="POST" action="{{ route('attendance.update', $report->id) }}">
                    @csrf
                    @method('PUT')

                    <!-- Скрытые поля -->
                    <input type="hidden" name="ordinary_days" id="hidden-ordinary-days"
                        value="{{ $report->ordinary_days }}">
                    <input type="hidden" name="remote_days" id="hidden-remote-days"
                        value="{{ $report->remote_days }}">
                    <input type="hidden" name="audits_count" id="hidden-audits-count"
                        value="{{ $report->audits_count }}">
                    <input type="hidden" name="individual_bonus" id="hidden-individual-bonus"
                        value="{{ $report->individual_bonus }}">
                    <input type="hidden" name="custom_bonus" id="hidden-custom-bonus"
                        value="{{ $report->custom_bonus }}">
                    <input type="hidden" name="fees" id="hidden-fees" value="{{ $report->fees }}">
                    <input type="hidden" name="penalties" id="hidden-penalties" value="{{ $report->penalties }}">
                    <input type="hidden" name="total_salary" id="hidden-total-salary"
                        value="{{ $report->total_salary }}">

                    <!-- Детализация премии по проектам -->
                    @foreach ($report->projectBonuses as $bonus)
                        <input type="hidden" name="project_bonuses[{{ $bonus->project_id }}][contract_amount]"
                            class="pb-contract" data-project-id="{{ $bonus->project_id }}"
                            value="{{ $bonus->contract_amount }}">
                        <input type="hidden" name="project_bonuses[{{ $bonus->project_id }}][bonus_percent]"
                            class="pb-percent" data-project-id="{{ $bonus->project_id }}"
                            value="{{ $bonus->bonus_percent }}">
                        <input type="hidden" name="project_bonuses[{{ $bonus->project_id }}][max_bonus]" class="pb-max"
                            data-project-id="{{ $bonus->project_id }}" value="{{ $bonus->max_bonus }}">
                        <input type="hidden" name="project_bonuses[{{ $bonus->project_id }}][days_worked]"
                            class="pb-days" data-project-id="{{ $bonus->project_id }}"
                            value="{{ $bonus->days_worked }}">
                        <input type="hidden" name="project_bonuses[{{ $bonus->project_id }}][bonus_amount]"
                            class="pb-bonus" data-project-id="{{ $bonus->project_id }}"
                            value="{{ $bonus->bonus_amount }}">
                    @endforeach

                    <button type="submit"
                        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                        Сохранить и отправить повторно
                    </button>
                </form>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const calcButton = document.getElementById('calculate-salary');

            const ordinaryDaysInput = document.querySelector('input[name="ordinary_days"]');
            const remoteDaysInput = document.querySelector('input[name="remote_days"]');
            const auditsInput = document.querySelector('input[name="audits_count"]');
            const customBonusInput = document.querySelector('input[name="custom_bonus"]');
            const feesInput = document.querySelector('input[name="fees"]');
            const penaltiesInput = document.getElementById('penalties-input');

            const auditsPrice = 300;
            const baseSalary = {{ $report->base_salary }};

            // Текущее значение индивидуальной премии (из БД, может меняться при редактировании проектов)
            let calculatedIndividualBonus = {{ $report->individual_bonus }};

            const individualBonusSpan = document.getElementById('individual-bonus');
            const totalSalarySpan = document.getElementById('total-salary');
            const auditsPaySpan = document.getElementById('audits-pay');

            function calculateSalary() {
                const ordinaryDays = parseFloat(ordinaryDaysInput.value) || 0;
                const remoteDays = parseFloat(remoteDaysInput.value) || 0;
                const audits = parseInt(auditsInput.value) || 0;
                const customBonus = parseFloat(customBonusInput.value) || 0;
                const feesRaw = parseFloat(feesInput.value) || 0;
                const fees = feesRaw > 0 ? -Math.abs(feesRaw) : feesRaw;
                const penaltiesRaw = parseFloat(penaltiesInput?.value) || 0;
                const penalties = penaltiesRaw > 0 ? -Math.abs(penaltiesRaw) : penaltiesRaw;

                const salaryPerDay = baseSalary / 22;
                const ordinaryPay = ordinaryDays * salaryPerDay;
                const remotePay = remoteDays * (salaryPerDay * 0.5);
                const auditsPay = audits * auditsPrice;
                const individualBonusPay = calculatedIndividualBonus;

                const totalSalary = ordinaryPay + remotePay + auditsPay + individualBonusPay + customBonus + fees +
                    penalties;

                individualBonusSpan.textContent = Math.round(individualBonusPay).toLocaleString('ru-RU');
                totalSalarySpan.textContent = Math.round(totalSalary).toLocaleString('ru-RU');
                auditsPaySpan.textContent = auditsPay.toLocaleString('ru-RU') + ' ₽';

                // Синхронизируем скрытые поля
                syncHiddenFields(ordinaryDays, remoteDays, audits, customBonus, fees, penalties, totalSalary);
            }

            function syncHiddenFields(ordinary, remote, audits, customBonus, fees, penalties, totalSalary) {
                document.getElementById('hidden-ordinary-days').value = ordinary;
                document.getElementById('hidden-remote-days').value = remote;
                document.getElementById('hidden-audits-count').value = audits;
                document.getElementById('hidden-custom-bonus').value = customBonus;
                document.getElementById('hidden-fees').value = fees;
                document.getElementById('hidden-penalties').value = penalties;
                document.getElementById('hidden-individual-bonus').value = calculatedIndividualBonus;
                document.getElementById('hidden-total-salary').value = Math.round(totalSalary);

                // Синхронизируем проектные данные
                syncProjectBonusFields();
            }

            function syncProjectBonusFields() {
                document.querySelectorAll('.project-days-input').forEach(input => {
                    const projectId = input.dataset.projectId;
                    const days = parseFloat(input.value) || 0;
                    document.querySelectorAll(`.pb-days[data-project-id="${projectId}"]`).forEach(
                    hidden => {
                        hidden.value = days;
                    });
                });

                document.querySelectorAll('.project-bonus-input').forEach(input => {
                    const projectId = input.dataset.projectId;
                    const bonus = parseFloat(input.value) || 0;
                    document.querySelectorAll(`.pb-bonus[data-project-id="${projectId}"]`).forEach(
                    hidden => {
                        hidden.value = bonus;
                    });
                });
            }

            function recalculateTotalProjectBonus() {
                let total = 0;
                document.querySelectorAll('.project-bonus-input').forEach(input => {
                    total += parseFloat(input.value) || 0;
                });

                const totalSpan = document.getElementById('total-project-bonus');
                if (totalSpan) {
                    totalSpan.textContent = Math.round(total).toLocaleString('ru-RU');
                }

                calculatedIndividualBonus = total;

                const indivSpan = document.getElementById('individual-bonus');
                if (indivSpan) {
                    indivSpan.textContent = Math.round(total).toLocaleString('ru-RU');
                }

                return total;
            }

            // Обработчик изменения дней работы над проектом
            document.querySelectorAll('.project-days-input').forEach(input => {
                input.addEventListener('input', function() {
                    const row = this.closest('[data-project-id]');
                    const bonusPerDay = parseFloat(row.dataset.bonusPerDay) || 0;
                    const days = parseFloat(this.value) || 0;
                    const newBonus = Math.round(bonusPerDay * days);

                    const bonusInput = row.querySelector('.project-bonus-input');
                    if (bonusInput) {
                        bonusInput.value = newBonus;
                    }

                    if (days > 0) {
                        this.classList.remove('bg-red-50', 'text-red-600', 'border-red-300');
                        this.classList.add('bg-green-50', 'text-green-700', 'border-green-300');
                    } else {
                        this.classList.remove('bg-green-50', 'text-green-700', 'border-green-300');
                        this.classList.add('bg-red-50', 'text-red-600', 'border-red-300');
                    }

                    recalculateTotalProjectBonus();
                    calculateSalary();
                });
            });

            // Обработчик прямого изменения премии
            document.querySelectorAll('.project-bonus-input').forEach(input => {
                input.addEventListener('input', function() {
                    recalculateTotalProjectBonus();
                    calculateSalary();
                });
            });

            // Рассчитываем при клике на кнопку
            calcButton.addEventListener('click', (e) => {
                e.preventDefault();
                calculateSalary();
            });

            // Авто-пересчёт при изменении полей
            [ordinaryDaysInput, remoteDaysInput, auditsInput, customBonusInput, feesInput, penaltiesInput]
            .filter(Boolean)
                .forEach(input => {
                    input.addEventListener('input', calculateSalary);
                });

            // При сабмите формы — синхронизируем
            const resubmitForm = document.getElementById('resubmit-form');
            if (resubmitForm) {
                resubmitForm.addEventListener('submit', (e) => {
                    calculateSalary();
                });
            }

            // Рассчитываем сразу при загрузке страницы
            calculateSalary();
        });
    </script>
@endsection
