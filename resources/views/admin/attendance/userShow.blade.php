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
                    <input type="number" name="ordinary_days" value="{{ $ordinaryDays }}"
                        class="w-20 border rounded p-1 text-center" />
                    дней
                </div>
            </div>

            <div class="flex justify-between items-center border-b pb-3">
                <div>Отработано удаленных дней:</div>
                <div>
                    <input type="number" name="remote_days" value="{{ $remoteDays }}"
                        class="w-20 border rounded p-1 text-center" />
                    дней (1 день = 0.5)
                </div>
            </div>

            <!-- Аудиты -->
            <div class="flex justify-between items-center border-b pb-3">
                <div>Количество аудитов:</div>
                <div>
                    <input type="number" name="audits_count" value="0" class="w-20 border rounded p-1 text-center" />
                    x 300 ₽ = <span id="audits-pay" class="font-medium">1 500 ₽</span>
                </div>
            </div>

            <!-- Индивидуальная премия -->
            <div class="flex justify-between items-center border-b pb-3">
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
                @endphp
                <div>Индивидуальная премия {{ $user->individual_bonus_percent }}% от {{ $projectsCount }}
                    {{ declension($projectsCount, 'проекта', 'проекта', 'проектов') }}:</div>
                <div>сумма: <span id="individual-bonus"
                        class="font-medium">{{ number_format($existingReport->individual_bonus ?? ($user->individual_bonus_amount ?? 0), 0, '', ' ') }}
                        ₽</span></div>
            </div>

            <!-- Произвольная премия -->
            <div class="flex justify-between items-center border-b pb-3">
                <div>Произвольная премия:</div>
                <div>
                    <input type="number" name="custom_bonus" value="0" class="w-28 border rounded p-1 text-center" />
                    ₽
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
                <button type="button" id="calculate-salary"
                    class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition mx-4">
                    Рассчитать
                </button>

                @if (!$existingReport)
                    <form id="submit-for-approval-form" method="POST"
                        action="{{ route('attendance.submit', $user->id) }}">
                        @csrf
                        <!-- Скрытые поля, синхронизируются JS перед отправкой -->
                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                        <input type="hidden" name="month" value="{{ $lastMonth->format('Y-m-01') }}">
                        <input type="hidden" name="base_salary" id="base-salary-input"
                            value="{{ $user->salary_override ?? ($user->specialty->salary ?? 0) }}">
                        <input type="hidden" name="ordinary_days" id="ordinary-days-input" value="{{ $ordinaryDays }}">
                        <input type="hidden" name="remote_days" id="remote-days-input" value="{{ $remoteDays }}">
                        <input type="hidden" name="audits_count" id="audits-count-hidden" value="0">
                        <input type="hidden" name="individual_bonus" id="individual-bonus-input"
                            value="{{ $existingReport->individual_bonus ?? ($user->individual_bonus_amount ?? 0) }}">
                        <input type="hidden" name="custom_bonus" id="custom-bonus-hidden" value="0">
                        <input type="hidden" name="status" value="submitted">
                        <input type="hidden" name="total_salary" id="total-salary-input"
                            value="{{ $user->salary_override ?? ($user->specialty->salary ?? 0) }}">
                        <button type="submit" id="submit-for-approval-button"
                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                            Отправить на согласование
                        </button>
                    </form>
                @else
                    <p class="text-gray-500 italic mr-4">Табель за этот месяц уже создан.</p>

                    <!-- Кнопка обновления -->
                    <form id="update-report-form" method="POST"
                        action="{{ route('attendance.update', $existingReport->id) }}">
                        @csrf
                        @method('PUT')
                        <!-- Скрытые поля, синхронизируются JS перед отправкой -->
                        <input type="hidden" name="ordinary_days" id="update-ordinary-days" value="{{ $ordinaryDays }}">
                        <input type="hidden" name="remote_days" id="update-remote-days" value="{{ $remoteDays }}">
                        <input type="hidden" name="audits_count" id="update-audits-count" value="0">
                        <input type="hidden" name="custom_bonus" id="update-custom-bonus" value="0">
                        <input type="hidden" name="individual_bonus" id="update-individual-bonus"
                            value="{{ $existingReport->individual_bonus ?? ($user->individual_bonus_amount ?? 0) }}">
                        <input type="hidden" name="total_salary" id="update-total-salary"
                            value="{{ $user->salary_override ?? ($user->specialty->salary ?? 0) }}">
                        <button type="submit" id="update-report-button"
                            class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition">
                            Обновить
                        </button>
                    </form>
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

            const auditsPrice = 300;
            const individualPercent = {{ $user->individual_bonus_percent }};
            const projectsCount = {{ $projectsCount }};
            const baseSalary = {{ $user->salary_override ?? ($user->specialty->salary ?? 0) }};
            const totalContractAmount = {{ $totalContractAmount }};

            const individualBonusSpan = document.getElementById('individual-bonus');
            const totalSalarySpan = document.getElementById('total-salary');
            const auditsPaySpan = document.getElementById('audits-pay');

            function calculateSalary() {
                const ordinaryDays = parseFloat(ordinaryDaysInput.value) || 0;
                const remoteDays = parseFloat(remoteDaysInput.value) || 0;
                const audits = parseInt(auditsInput.value) || 0;
                const customBonus = parseFloat(customBonusInput.value) || 0; // новое значение

                const salaryPerDay = baseSalary / 22; // стандартный месяц = 22 дня
                const ordinaryPay = ordinaryDays * salaryPerDay;
                const remotePay = remoteDays * (salaryPerDay * 0.5);
                const auditsPay = audits * auditsPrice;
                const individualBonusPay = totalContractAmount * (individualPercent / 100);

                const totalSalary = ordinaryPay + remotePay + auditsPay + individualBonusPay + customBonus;

                individualBonusSpan.textContent = individualBonusPay.toLocaleString('ru-RU');
                totalSalarySpan.textContent = Math.round(totalSalary).toLocaleString('ru-RU');
                auditsPaySpan.textContent = auditsPay.toLocaleString('ru-RU');
            }

            // Рассчитываем при клике на кнопку
            calcButton.addEventListener('click', (e) => {
                e.preventDefault();
                calculateSalary();
            });

            // Авто-пересчет при изменении полей
            [ordinaryDaysInput, remoteDaysInput, auditsInput, customBonusInput].forEach(input => {
                input.addEventListener('input', calculateSalary);
            });

            // Рассчитываем сразу при загрузке страницы
            calculateSalary();

            // Синхронизируем скрытые поля (для отправки форм)
            function syncHiddenFields() {
                const ordinary = parseFloat(ordinaryDaysInput.value) || 0;
                const remote = parseFloat(remoteDaysInput.value) || 0;
                const audits = parseInt(auditsInput.value) || 0;
                const customBonus = parseFloat(customBonusInput.value) || 0;

                // Основная форма отправки
                const auditsHidden = document.getElementById('audits-count-hidden');
                const customHidden = document.getElementById('custom-bonus-hidden');
                const indivHidden = document.getElementById('individual-bonus-input');
                const totalHidden = document.getElementById('total-salary-input');
                const ordinaryHidden = document.getElementById('ordinary-days-input');
                const remoteHidden = document.getElementById('remote-days-input');

                if (auditsHidden) auditsHidden.value = audits;
                if (customHidden) customHidden.value = customBonus;
                if (indivHidden) indivHidden.value = (totalContractAmount * (individualPercent / 100));
                if (totalHidden) totalHidden.value = Math.round((ordinaryPay = (ordinary * (baseSalary / 22))) + (
                    remote * (baseSalary / 22) * 0.5) + (audits * auditsPrice) + (totalContractAmount * (
                    individualPercent / 100)) + customBonus);
                if (ordinaryHidden) ordinaryHidden.value = ordinary;
                if (remoteHidden) remoteHidden.value = remote;

                // Форма обновления
                const updateAuditsHidden = document.getElementById('update-audits-count');
                const updateCustomHidden = document.getElementById('update-custom-bonus');
                const updateTotalHidden = document.getElementById('update-total-salary');
                const updateOrdHidden = document.getElementById('update-ordinary-days');
                const updateRemHidden = document.getElementById('update-remote-days');

                if (updateAuditsHidden) updateAuditsHidden.value = audits;
                if (updateCustomHidden) updateCustomHidden.value = customBonus;
                if (updateTotalHidden) updateTotalHidden.value = Math.round((ordinary * (baseSalary / 22)) + (
                    remote * (baseSalary / 22) * 0.5) + (audits * auditsPrice) + (totalContractAmount * (
                    individualPercent / 100)) + customBonus);
                if (updateOrdHidden) updateOrdHidden.value = ordinary;
                if (updateRemHidden) updateRemHidden.value = remote;

                // Синхронизируем индивидуальную премию для формы обновления
                const updateIndivHidden = document.getElementById('update-individual-bonus');
                if (updateIndivHidden) updateIndivHidden.value = (totalContractAmount * (individualPercent / 100));
            }

            // Вызываем синхронизацию при изменении полей и после расчёта
            [ordinaryDaysInput, remoteDaysInput, auditsInput, customBonusInput].forEach(input => {
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
                    syncHiddenFields();
                });
            }

            const updateForm = document.getElementById('update-report-form');
            if (updateForm) {
                updateForm.addEventListener('submit', (e) => {
                    calculateSalary();
                    syncHiddenFields();
                });
            }
        });
    </script>
@endsection
