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
                    x 300 ₽ = <span id="audits-pay" class="font-medium">0 ₽</span>
                </div>
            </div>

            <div class="flex justify-between items-center border-b pb-3">
                <div>Успешные аудиты:</div>
                <div>
                    <input type="number" name="audits_count_success"
                        value="{{ $existingReport->audits_count_success ?? 0 }}"
                        class="w-20 border rounded p-1 text-center" />
                    x 1 000 ₽ = <span id="audits-success-pay" class="font-medium">0 ₽</span>
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

            @php
                $feeItems = $existingReport
                    ? $existingReport->adjustments
                        ->where('type', 'fee')
                        ->map(fn($item) => ['amount' => (float) $item->amount, 'comment' => $item->comment])
                        ->values()
                        ->all()
                    : [];
                if (empty($feeItems) && isset($existingReport->fees) && abs($existingReport->fees) > 0) {
                    $feeItems[] = ['amount' => abs($existingReport->fees), 'comment' => ''];
                }
                if (empty($feeItems)) {
                    $feeItems[] = ['amount' => 0, 'comment' => ''];
                }

                $penaltyItems = $existingReport
                    ? $existingReport->adjustments
                        ->where('type', 'penalty')
                        ->map(fn($item) => ['amount' => (float) $item->amount, 'comment' => $item->comment])
                        ->values()
                        ->all()
                    : [];
                if (empty($penaltyItems) && isset($existingReport->penalties) && abs($existingReport->penalties) > 0) {
                    $penaltyItems[] = ['amount' => abs($existingReport->penalties), 'comment' => ''];
                }
                if (empty($penaltyItems)) {
                    $penaltyItems[] = ['amount' => 0, 'comment' => ''];
                }
            @endphp

            <div class="grid gap-4 sm:grid-cols-2 bg-white border border-gray-200 rounded-lg shadow-sm p-4">
                <!-- Fees column -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-semibold text-gray-700">Сборы</h3>
                            <button type="button" id="add-fee-item"
                                class="text-sm px-2 py-1 bg-indigo-50 text-indigo-700 rounded hover:bg-indigo-100 transition">+
                                Добавить</button>
                        </div>
                        <div class="text-sm font-semibold text-red-600">Итого: <span
                                id="fees-total-display">{{ number_format(collect($feeItems)->sum('amount') ?? 0, 0, '', ' ') }}
                                ₽</span></div>
                    </div>

                    <div id="fees-items" class="space-y-2">
                        @foreach ($feeItems as $item)
                            <div
                                class="fee-item-row flex items-center gap-3 bg-gray-50 border border-gray-100 rounded px-3 py-2">
                                <input type="number" step="0.01" min="0" value="{{ $item['amount'] ?? 0 }}"
                                    class="fee-item-amount w-28 text-right font-semibold bg-transparent border-0"
                                    placeholder="Сумма" />
                                <input type="text" value="{{ $item['comment'] ?? '' }}"
                                    class="fee-item-comment flex-1 border rounded p-1 text-sm" placeholder="Комментарий" />
                                <button type="button"
                                    class="remove-adjustment ml-2 inline-flex items-center justify-center h-8 w-8 rounded-full text-red-600 hover:bg-red-50 transition">✕</button>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-3 text-xs text-gray-500">Введите сумму без знака; добавьте комментарий для пояснения.
                    </div>
                </div>

                <!-- Penalties column -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-semibold text-gray-700">Штрафы</h3>
                            <button type="button" id="add-penalty-item"
                                class="text-sm px-2 py-1 bg-red-50 text-red-700 rounded hover:bg-red-100 transition">+
                                Добавить</button>
                        </div>
                        <div class="text-sm font-semibold text-red-600">Итого: <span
                                id="penalties-total-display">{{ number_format(collect($penaltyItems)->sum('amount') ?? 0, 0, '', ' ') }}
                                ₽</span></div>
                    </div>

                    <div id="penalties-items" class="space-y-2">
                        @foreach ($penaltyItems as $item)
                            <div
                                class="penalty-item-row flex items-center gap-3 bg-red-50/40 border border-red-100 rounded px-3 py-2">
                                <input type="number" step="0.01" min="0" value="{{ $item['amount'] ?? 0 }}"
                                    class="penalty-item-amount w-28 text-right font-semibold bg-transparent border-0"
                                    placeholder="Сумма" />
                                <input type="text" value="{{ $item['comment'] ?? '' }}"
                                    class="penalty-item-comment flex-1 border rounded p-1 text-sm"
                                    placeholder="Комментарий" />
                                <button type="button"
                                    class="remove-adjustment ml-2 inline-flex items-center justify-center h-8 w-8 rounded-full text-red-600 hover:bg-red-50 transition">✕</button>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-3 text-xs text-gray-500">Штрафы уменьшают итоговую ЗП — указывайте причину в
                        комментарии.</div>
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
                        <input type="hidden" name="audits_count_success" id="audits-count-success-hidden"
                            value="{{ $existingReport->audits_count_success ?? 0 }}">
                        <input type="hidden" name="individual_bonus" id="individual-bonus-input"
                            value="{{ $existingReport->individual_bonus ?? ($calculatedTotalBonus ?? 0) }}">
                        <input type="hidden" name="fees" id="fees-hidden" value="{{ $existingReport->fees ?? 0 }}">
                        <input type="hidden" name="penalties" id="penalties-hidden"
                            value="{{ $existingReport->penalties ?? 0 }}">
                        <div id="adjustments-hidden-container"></div>
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

                            @if (isset($canSubmitNow) && $canSubmitNow)
                                <button type="submit" id="submit-for-approval-button" name="status" value="submitted"
                                    class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                                    Отправить на согласование
                                </button>
                            @else
                                <span class="text-sm text-gray-500">Отправка на согласование доступна с
                                    {{ $lastMonth->copy()->addMonth()->startOfMonth()->translatedFormat('d.m.Y') }}.</span>
                            @endif
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
            const ordinaryDaysInput = document.querySelector('input[name="ordinary_days"]');
            const remoteDaysInput = document.querySelector('input[name="remote_days"]');
            const auditsInput = document.querySelector('input[name="audits_count"]');
            const auditsSuccessInput = document.querySelector('input[name="audits_count_success"]');
            const customBonusInput = document.querySelector('input[name="custom_bonus"]');
            const advanceInput = document.getElementById('advance-input');

            const auditsPrice = 300;
            const auditsSuccessPrice = 1000;
            const baseSalary = {{ $user->salary_override ?? ($user->specialty->salary ?? 0) }};
            let calculatedIndividualBonus = {{ $calculatedTotalBonus ?? 0 }};

            const individualBonusSpan = document.getElementById('individual-bonus');
            const totalSalarySpan = document.getElementById('total-salary');
            const auditsPaySpan = document.getElementById('audits-pay');
            const auditsSuccessPaySpan = document.getElementById('audits-success-pay');

            function buildAdjustmentRow(type, amount = 0, comment = '') {
                const row = document.createElement('div');
                row.className = `${type}-item-row flex items-center gap-2`;
                row.innerHTML = `
                    <input type="number" step="0.01" min="0" value="${amount}" class="${type}-item-amount w-32 border rounded p-1 text-center" placeholder="Сумма" />
                    <input type="text" value="${comment}" class="${type}-item-comment flex-1 border rounded p-1" placeholder="Комментарий" />
                    <button type="button" class="remove-adjustment px-2 py-1 text-sm bg-red-100 text-red-700 rounded hover:bg-red-200 transition">✕</button>
                `;

                row.querySelectorAll('input').forEach(input => {
                    input.addEventListener('input', () => {
                        calculateSalary();
                        syncHiddenFields();
                    });
                });

                row.querySelector('.remove-adjustment')?.addEventListener('click', () => {
                    row.remove();
                    calculateSalary();
                    syncHiddenFields();
                });

                return row;
            }

            function getAdjustmentItems(type) {
                const amounts = document.querySelectorAll(`.${type}-item-amount`);
                const comments = document.querySelectorAll(`.${type}-item-comment`);
                const items = [];

                amounts.forEach((amountInput, index) => {
                    const amount = parseFloat(amountInput.value) || 0;
                    const comment = (comments[index]?.value || '').trim();
                    if (amount > 0 || comment !== '') {
                        items.push({
                            amount: amount > 0 ? amount : 0,
                            comment,
                        });
                    }
                });

                return items;
            }

            function getAdjustmentTotal(type) {
                return getAdjustmentItems(type).reduce((sum, item) => sum + (item.amount || 0), 0);
            }

            function syncAdjustmentHiddenInputs() {
                const container = document.getElementById('adjustments-hidden-container');
                if (!container) return;

                container.innerHTML = '';

                const feeItems = getAdjustmentItems('fee');
                const penaltyItems = getAdjustmentItems('penalty');

                feeItems.forEach((item, index) => {
                    const amountInput = document.createElement('input');
                    amountInput.type = 'hidden';
                    amountInput.name = `fee_items[${index}][amount]`;
                    amountInput.value = item.amount;
                    container.appendChild(amountInput);

                    const commentInput = document.createElement('input');
                    commentInput.type = 'hidden';
                    commentInput.name = `fee_items[${index}][comment]`;
                    commentInput.value = item.comment;
                    container.appendChild(commentInput);
                });

                penaltyItems.forEach((item, index) => {
                    const amountInput = document.createElement('input');
                    amountInput.type = 'hidden';
                    amountInput.name = `penalty_items[${index}][amount]`;
                    amountInput.value = item.amount;
                    container.appendChild(amountInput);

                    const commentInput = document.createElement('input');
                    commentInput.type = 'hidden';
                    commentInput.name = `penalty_items[${index}][comment]`;
                    commentInput.value = item.comment;
                    container.appendChild(commentInput);
                });
            }

            function calculateSalary() {
                const ordinaryDays = parseFloat(ordinaryDaysInput.value) || 0;
                const remoteDays = parseFloat(remoteDaysInput.value) || 0;
                const audits = parseInt(auditsInput.value) || 0;
                const auditsSuccess = parseInt(auditsSuccessInput?.value) || 0;
                const customBonus = parseFloat(customBonusInput.value) || 0;
                const fees = -Math.abs(getAdjustmentTotal('fee'));
                const penalties = -Math.abs(getAdjustmentTotal('penalty'));
                const advanceRaw = parseFloat(advanceInput?.value) || 0;

                const salaryPerDay = baseSalary / 22;
                const ordinaryPay = ordinaryDays * salaryPerDay;
                const remotePay = remoteDays * (salaryPerDay * 0.5);
                const auditsPay = audits * auditsPrice;
                const auditsSuccessPay = auditsSuccess * auditsSuccessPrice;
                const individualBonusPay = calculatedIndividualBonus;

                const totalSalary = ordinaryPay + remotePay + auditsPay + auditsSuccessPay + individualBonusPay +
                    customBonus + fees + penalties - advanceRaw;

                individualBonusSpan.textContent = Math.round(individualBonusPay).toLocaleString('ru-RU');
                totalSalarySpan.textContent = Math.round(totalSalary).toLocaleString('ru-RU');
                auditsPaySpan.textContent = auditsPay.toLocaleString('ru-RU') + ' ₽';
                if (auditsSuccessPaySpan) {
                    auditsSuccessPaySpan.textContent = auditsSuccessPay.toLocaleString('ru-RU') + ' ₽';
                }

                // Отображаем итоги для сборов/штрафов
                const feesAbs = Math.abs(fees);
                const penaltiesAbs = Math.abs(penalties);
                const feesTotalEl = document.getElementById('fees-total-display');
                const penaltiesTotalEl = document.getElementById('penalties-total-display');
                if (feesTotalEl) feesTotalEl.textContent = feesAbs.toLocaleString('ru-RU') + ' ₽';
                if (penaltiesTotalEl) penaltiesTotalEl.textContent = penaltiesAbs.toLocaleString('ru-RU') + ' ₽';
            }

            function syncHiddenFields() {
                const ordinary = parseFloat(ordinaryDaysInput.value) || 0;
                const remote = parseFloat(remoteDaysInput.value) || 0;
                const audits = parseInt(auditsInput.value) || 0;
                const auditsSuccess = parseInt(auditsSuccessInput?.value) || 0;
                const customBonus = parseFloat(customBonusInput.value) || 0;
                const fees = -Math.abs(getAdjustmentTotal('fee'));
                const penalties = -Math.abs(getAdjustmentTotal('penalty'));
                const advanceRaw = parseFloat(advanceInput?.value) || 0;

                const auditsHidden = document.getElementById('audits-count-hidden');
                const auditsSuccessHidden = document.getElementById('audits-count-success-hidden');
                const customHidden = document.getElementById('custom-bonus-hidden');
                const feesHidden = document.getElementById('fees-hidden');
                const penaltiesHidden = document.getElementById('penalties-hidden');
                const advanceHidden = document.getElementById('advance-hidden');
                const indivHidden = document.getElementById('individual-bonus-input');
                const totalHidden = document.getElementById('total-salary-input');
                const ordinaryHidden = document.getElementById('ordinary-days-input');
                const remoteHidden = document.getElementById('remote-days-input');

                if (auditsHidden) auditsHidden.value = audits;
                if (auditsSuccessHidden) auditsSuccessHidden.value = auditsSuccess;
                if (customHidden) customHidden.value = customBonus;
                if (feesHidden) feesHidden.value = fees;
                if (penaltiesHidden) penaltiesHidden.value = penalties;
                if (advanceHidden) advanceHidden.value = Math.abs(advanceRaw);
                if (indivHidden) indivHidden.value = calculatedIndividualBonus;
                if (totalHidden) totalHidden.value = Math.round((ordinary * (baseSalary / 22)) + (
                        remote * (baseSalary / 22) * 0.5) + (audits * auditsPrice) + (auditsSuccess *
                        auditsSuccessPrice) +
                    calculatedIndividualBonus + customBonus + fees + penalties - advanceRaw);
                if (ordinaryHidden) ordinaryHidden.value = ordinary;
                if (remoteHidden) remoteHidden.value = remote;
                syncAdjustmentHiddenInputs();

                // Синхронизируем скрытые поля проектов
                syncProjectBonusFields();
            }

            // Функция для пересчёта итога по проектам
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

                const indivHidden = document.getElementById('individual-bonus-input');
                if (indivHidden) indivHidden.value = total;

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
                    syncHiddenFields();
                });
            });

            document.querySelectorAll('.project-bonus-input').forEach(input => {
                input.addEventListener('input', function() {
                    recalculateTotalProjectBonus();
                    calculateSalary();
                    syncHiddenFields();
                });
            });

            ([ordinaryDaysInput, remoteDaysInput, auditsInput, auditsSuccessInput, customBonusInput,
                advanceInput
            ].filter(
                Boolean)).forEach(input => {
                input.addEventListener('input', () => {
                    calculateSalary();
                    syncHiddenFields();
                });
            });

            document.getElementById('add-fee-item')?.addEventListener('click', () => {
                const container = document.getElementById('fees-items');
                if (!container) return;
                container.appendChild(buildAdjustmentRow('fee'));
            });

            document.getElementById('add-penalty-item')?.addEventListener('click', () => {
                const container = document.getElementById('penalties-items');
                if (!container) return;
                container.appendChild(buildAdjustmentRow('penalty'));
            });

            document.querySelectorAll('.fee-item-row, .penalty-item-row').forEach(row => {
                row.querySelectorAll('input').forEach(input => {
                    input.addEventListener('input', () => {
                        calculateSalary();
                        syncHiddenFields();
                    });
                });

                row.querySelector('.remove-adjustment')?.addEventListener('click', () => {
                    row.remove();
                    calculateSalary();
                    syncHiddenFields();
                });
            });

            const submitForm = document.getElementById('submit-for-approval-form');
            if (submitForm) {
                submitForm.addEventListener('submit', () => {
                    calculateSalary();
                    syncHiddenFields();
                });
            }

            calculateSalary();
            syncHiddenFields();
        });
    </script>
@endsection
