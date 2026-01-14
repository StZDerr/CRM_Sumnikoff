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

            <!-- Отработанные дни -->
            <div class="flex justify-between items-center border-b pb-3">
                <div>Отработано обычных дней:</div>
                <div>
                    <input type="number" name="ordinary_days" value="{{ $workingDaysLastMonth }}"
                        class="w-20 border rounded p-1 text-center" />
                    дней
                </div>
            </div>

            <div class="flex justify-between items-center border-b pb-3">
                <div>Отработано удаленных дней:</div>
                <div>
                    <input type="number" name="remote_days" value="2" class="w-20 border rounded p-1 text-center" />
                    дней (1 день = 0.5)
                </div>
            </div>

            <!-- Аудиты -->
            <div class="flex justify-between items-center border-b pb-3">
                <div>Количество аудитов:</div>
                <div>
                    <input type="number" name="audits_count" value="5" class="w-20 border rounded p-1 text-center" />
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
                        class="font-medium">{{ number_format($user->individual_bonus_amount, 0, '', ' ') }} ₽</span></div>
            </div>

            <!-- Итоговая ЗП -->
            <div class="flex justify-between items-center text-lg font-semibold pt-3">
                <div>Итоговая ЗП:</div>
                <div class="text-indigo-600">≈ <span
                        id="total-salary">{{ number_format($user->salary_override ?? ($user->specialty->salary ?? 0), 0, '', ' ') }}
                        ₽</span></div>
            </div>

            <div class="flex justify-end">
                <button class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition">
                    Рассчитать
                </button>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const calcButton = document.querySelector('button.bg-indigo-600');

            const ordinaryDaysInput = document.querySelector('input[name="ordinary_days"]');
            const remoteDaysInput = document.querySelector('input[name="remote_days"]');
            const auditsInput = document.querySelector('input[name="audits_count"]');

            const auditsPrice = 300;
            const individualPercent = {{ $user->individual_bonus_percent }};
            const projectsCount = {{ $projectsCount }};
            const baseSalary = {{ $user->salary_override ?? ($user->specialty->salary ?? 0) }};
            const totalContractAmount = {{ $totalContractAmount }};

            const individualBonusSpan = document.getElementById('individual-bonus');
            const totalSalarySpan = document.getElementById('total-salary');
            const auditsPaySpan = document.getElementById('audits-pay');

            calcButton.addEventListener('click', (e) => {
                e.preventDefault();

                const ordinaryDays = parseFloat(ordinaryDaysInput.value) || 0;
                const remoteDays = parseFloat(remoteDaysInput.value) || 0;
                const audits = parseInt(auditsInput.value) || 0;

                const salaryPerDay = baseSalary / 22; // стандартный месяц = 22 дня
                const ordinaryPay = ordinaryDays * salaryPerDay;
                const remotePay = remoteDays * (salaryPerDay * 0.5);
                const auditsPay = audits * auditsPrice;
                const individualBonusPay = totalContractAmount * (individualPercent / 100);
                const totalSalary = ordinaryPay + remotePay + auditsPay + individualBonusPay;

                // Обновляем значения
                individualBonusSpan.textContent = individualBonusPay.toLocaleString('ru-RU');
                totalSalarySpan.textContent = Math.round(totalSalary).toLocaleString('ru-RU');
                auditsPaySpan.textContent = auditsPay.toLocaleString('ru-RU');
            });
        });
    </script>
@endsection
