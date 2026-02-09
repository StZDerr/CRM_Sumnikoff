@extends('layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto px-4 py-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-semibold">Калькулятор ЗП — {{ $user->name }} за {{ $lastMonth->translatedFormat('F') }}
            </h1>
            <a href="{{ route('users.index') }}"
                class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 transition">
                Назад к пользователям
            </a>
        </div>

        <div class="bg-white rounded shadow p-6 space-y-6">

            <!-- Базовый оклад -->
            <div class="flex justify-between items-center border-b pb-3">
                <div>Специальность: <span class="font-medium">{{ $user->specialty->name ?? 'Индивидуальная' }}</span></div>
                <div>Оклад: <span class="font-medium">{{ number_format($user->specialty->salary ?? $user->salary_override) }}
                        ₽</span></div>
            </div>
            <!-- Для создания -->
            <input type="hidden" name="base_salary" value="{{ $user->salary_override ?? ($user->specialty->salary ?? 0) }}">

            <!-- Для обновления -->
            <input type="hidden" name="base_salary"
                value="{{ $existingReport->base_salary ?? ($user->salary_override ?? ($user->specialty->salary ?? 0)) }}">

            <!-- Отработанные дни -->
            <div class="flex justify-between items-center border-b pb-3">
                <div>Отработано обычных дней:</div>
                <div>
                    <input type="number" name="ordinary_days" value="{{ $existingReport->ordinary_days ?? $ordinaryDays }}"
                        class="w-20 border rounded p-1 text-center" />
                    дней
                </div>
            </div>

            <div class="flex justify-between items-center border-b pb-3">
                <div>Отработано удаленных дней:</div>
                <div>
                    <input type="number" name="remote_days" value="{{ $existingReport->remote_days ?? $remoteDays }}"
                        class="w-20 border rounded p-1 text-center" />
                    дней (1 день = 0.5)
                </div>
            </div>

            <!-- Аудиты -->
            <div class="flex justify-between items-center border-b pb-3">
                <div>Количество аудитов:</div>
                <div>
                    <input type="number" name="audits_count" value="{{ $existingReport->audits_count ?? 0 }}"
                        class="w-20 border rounded p-1 text-center" />
                    x 300 ₽ = <span id="audits-pay" class="font-medium">1 500 ₽</span>
                </div>
            </div>

            <!-- Индивидуальная премия -->
            <div class="border-b pb-3">
                @php
                    function declension($number, $one, $two, $five)
                    {
                        $number = abs($number) % 100;
                        $n1 = $number % 10;
                        if ($number > 10 && $number < 20) {
                            return $five;
                        }
                        if ($n1 > 1 && $n1 < 5) {
                            return $two;
                        }
                        if ($n1 == 1) {
                            return $one;
                        }
                        return $five;
                    }
                    $bonusPercent = $user->individual_bonus_percent ?? 5;
                @endphp

                <div class="flex justify-between items-center mb-2">
                    <div class="font-medium">Индивидуальная премия {{ $bonusPercent }}% от {{ $projectsCount }}
                        {{ declension($projectsCount, 'проекта', 'проектов', 'проектов') }}:</div>
                </div>

                @php
                    $avgWorkDays = 22; // среднее количество рабочих дней в месяце
                    $calculatedTotalBonus = 0;
                @endphp

                @if ($projects->count() > 0)
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
                        @foreach ($projects as $project)
                            @php
                                $bonusData = $projectBonusesData[$project->id] ?? null;
                                $contractAmount = $bonusData['contract_amount'] ?? ($project->contract_amount ?? 0);
                                $maxProjectBonus = $bonusData['max_bonus'] ?? $contractAmount * ($bonusPercent / 100);
                                $bonusPerDay = $avgWorkDays > 0 ? $maxProjectBonus / $avgWorkDays : 0;
                                $daysWorked = $bonusData['days_worked'] ?? ($projectDaysData[$project->id] ?? 0);
                                $projectBonus = $bonusData['bonus_amount'] ?? $bonusPerDay * $daysWorked;
                                $calculatedTotalBonus += $projectBonus;
                            @endphp

                            <div class="grid grid-cols-12 gap-2 px-4 py-3 items-center text-sm border-b last:border-b-0
                    hover:bg-gray-50 transition"
                                data-project-id="{{ $project->id }}" data-max-bonus="{{ $maxProjectBonus }}"
                                data-bonus-per-day="{{ $bonusPerDay }}">

                                {{-- Project --}}
                                <div class="col-span-5 truncate font-medium text-gray-800" title="{{ $project->title }}">
                                    {{ $project->title }}
                                </div>

                                {{-- Max bonus --}}
                                <div class="col-span-2 text-center text-gray-500">
                                    {{ number_format($maxProjectBonus, 0, '', ' ') }} ₽
                                </div>

                                {{-- Days (read-only) --}}
                                <div
                                    class="col-span-2 text-center px-2 py-1 border rounded text-xs font-semibold
                        {{ $daysWorked > 0 ? 'bg-green-50 text-green-700 border-green-300' : 'bg-red-50 text-red-600 border-red-300' }}">
                                    {{ $daysWorked == intval($daysWorked) ? intval($daysWorked) : number_format($daysWorked, 1, '.', '') }}
                                </div>

                                {{-- Bonus (editable) --}}
                                <div class="col-span-3 text-right">
                                    <input type="number" step="1" min="0"
                                        class="project-bonus-input w-24 px-2 py-1 text-right border rounded font-semibold text-gray-800"
                                        data-project-id="{{ $project->id }}" value="{{ round($projectBonus) }}">
                                    <span class="text-gray-500 ml-1">₽</span>
                                </div>
                            </div>
                        @endforeach

                        {{-- Footer / Total --}}
                        <div class="flex justify-between items-center px-4 py-3 bg-gray-50 text-sm font-semibold">
                            <span class="text-gray-600">Итого премия</span>
                            <span class="text-green-700">
                                {{ number_format($calculatedTotalBonus, 0, '', ' ') }} ₽
                            </span>
                        </div>
                    </div>
                @else
                    <div class="ml-4 text-sm text-gray-400">Нет проектов</div>
                @endif

                <div class="flex justify-between items-center mt-3 pt-2 border-t border-gray-300">
                    <div class="font-medium">Итого премия ({{ $avgWorkDays }} раб. дней/мес.):</div>
                    <div class="font-semibold text-indigo-600">
                        <span id="individual-bonus">{{ number_format($calculatedTotalBonus, 0, '', ' ') }}</span>
                        ₽
                    </div>
                </div>
            </div>

            <!-- Произвольная премия -->
            <div class="flex justify-between items-center border-b pb-3">
                <div>Произвольная премия:</div>
                <div>
                    <input type="number" name="custom_bonus" value="{{ $existingReport->custom_bonus ?? 0 }}"
                        class="w-28 border rounded p-1 text-center" />
                    ₽
                </div>
            </div>

            <!-- Сборы -->
            <div class="flex justify-between items-center border-b pb-3">
                <div>Сборы (вводите число без знака — оно автоматически будет вычитаться):</div>
                <div>
                    <input type="number" name="fees" step="0.01"
                        value="{{ isset($existingReport->fees) ? abs($existingReport->fees) : 0 }}"
                        class="w-28 border rounded p-1 text-center" />
                    ₽
                </div>
            </div>

            <!-- Штрафы -->
            <div class="flex justify-between items-center border-b pb-3">
                <div>Штрафы (вводите число без знака — оно автоматически будет вычитаться):</div>
                <div>
                    <input id="penalties-input" type="number" name="penalties" step="0.01"
                        value="{{ isset($existingReport->penalties) ? abs($existingReport->penalties) : 0 }}"
                        class="w-28 border rounded p-1 text-center" />
                    ₽
                </div>
            </div>

            <!-- Аванс -->
            <div class="flex justify-between items-center border-b pb-3">
                <div>Аванс (вводите число без знака — оно автоматически будет вычитаться):</div>
                <div>
                    <input id="advance-input" type="number" name="advance_amount" step="0.01"
                        value="{{ isset($existingReport->advance_amount) ? abs($existingReport->advance_amount) : $advanceTotal ?? 0 }}"
                        class="w-28 border rounded p-1 text-center" />
                    ₽
                    <div class="text-sm text-gray-500 mt-1">
                        @if (!empty($salaryExpenses) && $salaryExpenses->count() > 0)
                            <ul class="mt-2 text-sm text-gray-600 space-y-1">
                                @foreach ($salaryExpenses as $exp)
                                    <li>
                                        {{ $exp->expense_date->translatedFormat('d.m.Y') }} — <span
                                            class="font-medium">{{ number_format($exp->amount, 2, '.', ' ') }} ₽</span>
                                        @if ($exp->document_number)
                                            <span class="text-gray-500">(№{{ $exp->document_number }})</span>
                                        @endif
                                        @if ($exp->description)
                                            <div class="text-gray-500">
                                                {{ \Illuminate\Support\Str::limit($exp->description, 80) }}</div>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="mt-2 text-sm text-gray-400">Авансы не зарегистрированы</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Итоговая ЗП -->
            <div class="flex justify-between items-center text-lg font-semibold pt-3">
                <div>Итоговая ЗП:</div>
                <div class="text-indigo-600">≈ <span
                        id="total-salary">{{ number_format($user->salary_override ?? ($user->specialty->salary ?? 0), 0, '', ' ') }}
                        ₽</span></div>
            </div>

            <div class="flex justify-end">
                {{-- <button type="button" id="calculate-salary"
                    class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition mx-4">
                    Рассчитать
                </button> --}}

                @if (!$existingReport || $existingReport->status === 'save')
                    <form id="submit-for-approval-form" method="POST"
                        action="{{ route('attendance.submit', $user->id) }}">
                        @csrf
                        <!-- Скрытые поля, синхронизируются JS перед отправкой -->
                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                        <input type="hidden" name="month" value="{{ $lastMonth->format('Y-m-01') }}">
                        <input type="hidden" name="base_salary" id="base-salary-input"
                            value="{{ $user->salary_override ?? ($user->specialty->salary ?? 0) }}">
                        <input type="hidden" name="ordinary_days" id="ordinary-days-input"
                            value="{{ $existingReport->ordinary_days ?? $ordinaryDays }}">
                        <input type="hidden" name="remote_days" id="remote-days-input"
                            value="{{ $existingReport->remote_days ?? $remoteDays }}">
                        <input type="hidden" name="audits_count" id="audits-count-hidden"
                            value="{{ $existingReport->audits_count ?? 0 }}">
                        <input type="hidden" name="individual_bonus" id="individual-bonus-input"
                            value="{{ $existingReport->individual_bonus ?? ($calculatedTotalBonus ?? 0) }}">
                        <input type="hidden" name="fees" id="fees-hidden" value="{{ $existingReport->fees ?? 0 }}">
                        <input type="hidden" name="penalties" id="penalties-hidden"
                            value="{{ $existingReport->penalties ?? 0 }}">
                        <input type="hidden" name="advance_amount" id="advance-hidden"
                            value="{{ $existingReport->advance_amount ?? ($advanceTotal ?? 0) }}">
                        <input type="hidden" name="custom_bonus" id="custom-bonus-hidden"
                            value="{{ $existingReport->custom_bonus ?? 0 }}">
                        <input type="hidden" name="total_salary" id="total-salary-input"
                            value="{{ $existingReport->total_salary ?? ($user->salary_override ?? ($user->specialty->salary ?? 0)) }}">

                        <!-- Детализация премии по проектам -->
                        @foreach ($projects as $project)
                            <input type="hidden" name="project_bonuses[{{ $project->id }}][contract_amount]"
                                class="pb-contract" data-project-id="{{ $project->id }}"
                                value="{{ $projectBonusesData[$project->id]['contract_amount'] ?? 0 }}">
                            <input type="hidden" name="project_bonuses[{{ $project->id }}][bonus_percent]"
                                class="pb-percent" data-project-id="{{ $project->id }}"
                                value="{{ $projectBonusesData[$project->id]['bonus_percent'] ?? 0 }}">
                            <input type="hidden" name="project_bonuses[{{ $project->id }}][max_bonus]" class="pb-max"
                                data-project-id="{{ $project->id }}"
                                value="{{ $projectBonusesData[$project->id]['max_bonus'] ?? 0 }}">
                            <input type="hidden" name="project_bonuses[{{ $project->id }}][days_worked]"
                                class="pb-days" data-project-id="{{ $project->id }}"
                                value="{{ $projectBonusesData[$project->id]['days_worked'] ?? 0 }}">
                            <input type="hidden" name="project_bonuses[{{ $project->id }}][bonus_amount]"
                                class="pb-bonus" data-project-id="{{ $project->id }}"
                                value="{{ $projectBonusesData[$project->id]['bonus_amount'] ?? 0 }}">
                        @endforeach

                        <div class="flex items-center gap-2">
                            <button type="submit" name="status" value="save"
                                class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 transition">
                                Сохранить
                            </button>
                            <button type="submit" id="submit-for-approval-button" name="status" value="submitted"
                                class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                                Отправить на согласование
                            </button>
                        </div>
                    </form>
                @else
                    <p class="text-gray-500 italic mr-4">Табель за этот месяц уже создан и отправлен на согласование.</p>
                @endif
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const calcButton = document.querySelector('button.bg-indigo-600');

            const ordinaryDaysInput = document.querySelector('input[name="ordinary_days"]');
            const remoteDaysInput = document.querySelector('input[name="remote_days"]');
            const auditsInput = document.querySelector('input[name="audits_count"]');
            const customBonusInput = document.querySelector('input[name="custom_bonus"]'); // новое поле
            const feesInput = document.querySelector('input[name="fees"]');
            const penaltiesInput = document.getElementById('penalties-input');
            const advanceInput = document.getElementById('advance-input');


            const auditsPrice = 300;
            const individualPercent = {{ $user->individual_bonus_percent }};
            const projectsCount = {{ $projectsCount }};
            const baseSalary = {{ $user->salary_override ?? ($user->specialty->salary ?? 0) }};
            const totalContractAmount = {{ $totalContractAmount }};
            // Рассчитанная премия с учётом дней работы (может изменяться админом)
            let calculatedIndividualBonus = {{ $calculatedTotalBonus ?? 0 }};

            const individualBonusSpan = document.getElementById('individual-bonus');
            const totalSalarySpan = document.getElementById('total-salary');
            const auditsPaySpan = document.getElementById('audits-pay');

            function calculateSalary() {
                const ordinaryDays = parseFloat(ordinaryDaysInput.value) || 0;
                const remoteDays = parseFloat(remoteDaysInput.value) || 0;
                const audits = parseInt(auditsInput.value) || 0;
                const customBonus = parseFloat(customBonusInput.value) || 0; // новое значение
                const feesRaw = parseFloat(feesInput.value) || 0;
                const fees = feesRaw > 0 ? -Math.abs(feesRaw) :
                    feesRaw; // положительные вводы автоматически считаем удержанием
                const penaltiesRaw = parseFloat(penaltiesInput?.value) || 0;
                const penalties = penaltiesRaw > 0 ? -Math.abs(penaltiesRaw) : penaltiesRaw;
                const advanceRaw = parseFloat(advanceInput?.value) || 0; // вводимый аванс (будет вычитаться)

                const salaryPerDay = baseSalary / 22; // стандартный месяц = 22 дня
                const ordinaryPay = ordinaryDays * salaryPerDay;
                const remotePay = remoteDays * (salaryPerDay * 0.5);
                const auditsPay = audits * auditsPrice;
                // Используем рассчитанную премию с учётом дней
                const individualBonusPay = calculatedIndividualBonus;

                // Аванс вводится без знака и вычитается из итоговой суммы
                const totalSalary = ordinaryPay + remotePay + auditsPay + individualBonusPay + customBonus + fees +
                    penalties - advanceRaw;

                individualBonusSpan.textContent = Math.round(individualBonusPay).toLocaleString('ru-RU');
                totalSalarySpan.textContent = Math.round(totalSalary).toLocaleString('ru-RU');
                auditsPaySpan.textContent = auditsPay.toLocaleString('ru-RU');
            }

            // Рассчитываем при клике на кнопку
            calcButton.addEventListener('click', (e) => {
                e.preventDefault();
                calculateSalary();
            });

            // Авто-пересчет при изменении полей
            ([ordinaryDaysInput, remoteDaysInput, auditsInput, customBonusInput, feesInput, penaltiesInput,
                advanceInput
            ].filter(
                Boolean)).forEach(
                input => {
                    input.addEventListener('input', calculateSalary);
                });

            // Также синхронизируем скрытые поля при изменении
            ([ordinaryDaysInput, remoteDaysInput, auditsInput, customBonusInput, feesInput, penaltiesInput,
                advanceInput
            ].filter(
                Boolean)).forEach(
                input => {
                    input.addEventListener('input', () => {
                        calculateSalary();
                        syncHiddenFields();
                    });
                });

            // Рассчитываем сразу при загрузке страницы
            calculateSalary();

            // Синхронизируем скрытые поля (для отправки форм)
            function syncHiddenFields() {
                const ordinary = parseFloat(ordinaryDaysInput.value) || 0;
                const remote = parseFloat(remoteDaysInput.value) || 0;
                const audits = parseInt(auditsInput.value) || 0;
                const customBonus = parseFloat(customBonusInput.value) || 0;
                const feesRaw = parseFloat(feesInput.value) || 0;
                const fees = feesRaw > 0 ? -Math.abs(feesRaw) : feesRaw;
                const penaltiesRaw = parseFloat(penaltiesInput?.value) || 0;
                const penalties = penaltiesRaw > 0 ? -Math.abs(penaltiesRaw) : penaltiesRaw;
                const advanceRaw = parseFloat(advanceInput?.value) || 0;

                // Основная форма отправки
                const auditsHidden = document.getElementById('audits-count-hidden');
                const customHidden = document.getElementById('custom-bonus-hidden');
                const feesHidden = document.getElementById('fees-hidden');
                const penaltiesHidden = document.getElementById('penalties-hidden');
                const advanceHidden = document.getElementById('advance-hidden');
                const indivHidden = document.getElementById('individual-bonus-input');
                const totalHidden = document.getElementById('total-salary-input');
                const ordinaryHidden = document.getElementById('ordinary-days-input');
                const remoteHidden = document.getElementById('remote-days-input');

                if (auditsHidden) auditsHidden.value = audits;
                if (customHidden) customHidden.value = customBonus;
                if (feesHidden) feesHidden.value = fees;
                if (penaltiesHidden) penaltiesHidden.value = penalties;
                if (advanceHidden) advanceHidden.value = Math.abs(advanceRaw);
                if (indivHidden) indivHidden.value = calculatedIndividualBonus;
                if (totalHidden) totalHidden.value = Math.round((ordinary * (baseSalary / 22)) + (
                        remote * (baseSalary / 22) * 0.5) + (audits * auditsPrice) + calculatedIndividualBonus +
                    customBonus + fees + penalties - advanceRaw);
                if (ordinaryHidden) ordinaryHidden.value = ordinary;
                if (remoteHidden) remoteHidden.value = remote;





                // Синхронизируем скрытые поля проектов
                syncProjectBonusFields();
            }

            // Функция для пересчёта итога по проектам
            function recalculateTotalProjectBonus() {
                let total = 0;
                document.querySelectorAll('.project-bonus-input').forEach(input => {
                    total += parseFloat(input.value) || 0;
                });

                // Обновляем отображение итога
                const totalSpan = document.getElementById('total-project-bonus');
                if (totalSpan) {
                    totalSpan.textContent = Math.round(total).toLocaleString('ru-RU');
                }

                // Обновляем calculatedIndividualBonus для расчёта общей ЗП
                calculatedIndividualBonus = total;

                // Обновляем скрытые поля индивидуальной премии
                const indivHidden = document.getElementById('individual-bonus-input');
                if (indivHidden) indivHidden.value = total;

                // Обновляем отображение в блоке "Итого премия"
                const individualBonusSpan = document.getElementById('individual-bonus');
                if (individualBonusSpan) {
                    individualBonusSpan.textContent = Math.round(total).toLocaleString('ru-RU');
                }

                return total;
            }

            // Синхронизируем скрытые поля проектов с видимыми input
            function syncProjectBonusFields() {
                document.querySelectorAll('.project-days-input').forEach(input => {
                    const projectId = input.dataset.projectId;
                    const days = parseFloat(input.value) || 0;

                    // Обновляем скрытые поля days_worked во всех формах
                    document.querySelectorAll(`.pb-days[data-project-id="${projectId}"]`).forEach(
                        hidden => {
                            hidden.value = days;
                        });
                });

                document.querySelectorAll('.project-bonus-input').forEach(input => {
                    const projectId = input.dataset.projectId;
                    const bonus = parseFloat(input.value) || 0;

                    // Обновляем скрытые поля bonus_amount во всех формах
                    document.querySelectorAll(`.pb-bonus[data-project-id="${projectId}"]`).forEach(
                        hidden => {
                            hidden.value = bonus;
                        });
                });
            }

            // Обработчик изменения дней работы над проектом
            document.querySelectorAll('.project-days-input').forEach(input => {
                input.addEventListener('input', function() {
                    const row = this.closest('[data-project-id]');
                    const bonusPerDay = parseFloat(row.dataset.bonusPerDay) || 0;
                    const days = parseFloat(this.value) || 0;
                    const newBonus = Math.round(bonusPerDay * days);

                    // Обновляем поле премии
                    const bonusInput = row.querySelector('.project-bonus-input');
                    if (bonusInput) {
                        bonusInput.value = newBonus;
                    }

                    // Обновляем стиль поля дней
                    if (days > 0) {
                        this.classList.remove('bg-red-50', 'text-red-600', 'border-red-300');
                        this.classList.add('bg-green-50', 'text-green-700', 'border-green-300');
                    } else {
                        this.classList.remove('bg-green-50', 'text-green-700', 'border-green-300');
                        this.classList.add('bg-red-50', 'text-red-600', 'border-red-300');
                    }

                    // Пересчитываем итого
                    recalculateTotalProjectBonus();
                    calculateSalary();
                    syncHiddenFields();
                });
            });

            // Обработчик прямого изменения премии
            document.querySelectorAll('.project-bonus-input').forEach(input => {
                input.addEventListener('input', function() {
                    recalculateTotalProjectBonus();
                    calculateSalary();
                    syncHiddenFields();
                });
            });

            // Вызываем синхронизацию при изменении полей и после расчёта
            ([ordinaryDaysInput, remoteDaysInput, auditsInput, customBonusInput, feesInput, penaltiesInput,
                advanceInput
            ].filter(
                Boolean)).forEach(input => {
                input.addEventListener('input', () => {
                    calculateSalary();
                    syncHiddenFields();
                });
            });

            // При сабмите форм — синхронизируем и отправляем
            const submitForm = document.getElementById('submit-for-approval-form');
            const submitButton = document.getElementById('submit-for-approval-button');
            if (submitForm) {
                submitForm.addEventListener('submit', (e) => {
                    calculateSalary();
                    // Гарантированно копируем видимое поле штрафов в скрытое перед отправкой
                    const visiblePen = document.getElementById('penalties-input');
                    if (visiblePen) {
                        const pr = parseFloat(visiblePen.value) || 0;
                        const p = pr > 0 ? -Math.abs(pr) : pr;
                        const penHidden = document.getElementById('penalties-hidden');
                        if (penHidden) penHidden.value = p;

                    }

                    // Гарантированно копируем видимое поле аванса в скрытое перед отправкой
                    const visibleAdv = document.getElementById('advance-input');
                    if (visibleAdv) {
                        const ar = parseFloat(visibleAdv.value) || 0;
                        const advHidden = document.getElementById('advance-hidden');
                        if (advHidden) advHidden.value = Math.abs(ar);
                    }

                    syncHiddenFields();
                });
            }


        });
    </script>
@endsection
