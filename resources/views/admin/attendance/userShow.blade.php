@extends('layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto px-4 py-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-semibold">Калькулятор ЗП — {{ $user->name }}</h1>
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

            <div class="flex justify-between items-center border-b pb-3">
                <div>Отработано обычных дней:</div>
                <div>
                    <input type="number" value="18" class="w-20 border rounded p-1 text-center" />
                    дней
                </div>
            </div>

            <div class="flex justify-between items-center border-b pb-3">
                <div>Отработано удаленных дней:</div>
                <div>
                    <input type="number" value="2" class="w-20 border rounded p-1 text-center" />
                    дней (1 день = 0.5)
                </div>
            </div>

            <!-- Аудиты -->
            <div class="flex justify-between items-center border-b pb-3">
                <div>Количество аудитов:</div>
                <div>
                    <input type="number" value="5" class="w-20 border rounded p-1 text-center" />
                    x 300 ₽ = <span class="font-medium">1 500 ₽</span>
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

                Индивидуальная премия {{ $user->individual_bonus_percent }}% от {{ $projectsCount }}
                {{ declension($projectsCount, 'проекта', 'проекта', 'проектов') }}:

                <div>
                    сумма: <span class="font-medium">{{ number_format($user->individual_bonus_amount, 0, '', ' ') }}
                        ₽</span>
                </div>
            </div>

            <!-- Итоговая ЗП -->
            <div class="flex justify-between items-center text-lg font-semibold pt-3">
                <div>Итоговая ЗП:</div>
                <div class="text-indigo-600">≈ <span>44 500 ₽</span></div>
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
            const ordinaryDaysInput = document.querySelector('input[value="18"]'); // обычные дни
            const remoteDaysInput = document.querySelector('input[value="2"]'); // удаленные дни
            const auditsInput = document.querySelector('input[value="5"]'); // количество аудитов
            const auditsPrice = 300; // цена за один аудит
            const individualPercent = {{ $user->individual_bonus_percent }};
            const projectsCount = {{ $projectsCount }};
            const baseSalary = {{ $user->salary }}; // фактический оклад (учитывает salary_override)

            const individualBonusAmount = {{ $totalContractAmount }} * (individualPercent / 100);

            calcButton.addEventListener('click', (e) => {
                e.preventDefault();

                const ordinaryDays = parseFloat(ordinaryDaysInput.value) || 0;
                const remoteDays = parseFloat(remoteDaysInput.value) || 0;
                const audits = parseInt(auditsInput.value) || 0;

                // Расчет зарплаты
                const salaryPerDay = baseSalary / 22; // допустим, стандартный месяц = 22 дня
                const ordinaryPay = ordinaryDays * salaryPerDay;
                const remotePay = remoteDays * (salaryPerDay * 0.5); // удаленные дни = 50%
                const auditsPay = audits * auditsPrice;
                const individualBonusPay = individualBonusAmount;

                const totalSalary = ordinaryPay + remotePay + auditsPay + individualBonusPay;

                // Обновляем сумму индивидуальной премии
                document.querySelector(
                        '.flex.justify-between.items-center.border-b.pb-3 div span.font-medium')
                    .textContent =
                    individualBonusPay.toLocaleString('ru-RU');

                // Обновляем итоговую ЗП
                document.querySelector('.flex.justify-between.items-center.text-lg.font-semibold.pt-3 span')
                    .textContent =
                    Math.round(totalSalary).toLocaleString('ru-RU');
            });
        });
    </script>
@endsection
