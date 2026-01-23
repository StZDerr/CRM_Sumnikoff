<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ProjectComment;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(Project $project)
    {
        $months = [];
        $invoicesByMonth = [];
        $paymentsByMonth = [];
        $commentsByMonth = [];
        $totalInvoices = 0;
        $totalPayments = 0;
        $contractAmount = 0;
        $contractStart = null;
        $expectedByMonth = [];

        // Определяем период: от самой ранней даты (контракт или счёт) до сейчас
        $start = null;

        // Начало от даты контракта
        if (! empty($project->contract_date)) {
            $start = Carbon::make($project->contract_date)->startOfMonth();
        }

        // Расширяем начало, если есть счета раньше даты контракта
        $earliestInvoice = Invoice::where('project_id', $project->id)
            ->selectRaw('MIN(COALESCE(issued_at, created_at)) as earliest')
            ->value('earliest');

        if ($earliestInvoice) {
            $earliestStart = Carbon::make($earliestInvoice)->startOfMonth();
            if (! $start || $earliestStart->lt($start)) {
                $start = $earliestStart;
            }
        }

        // Расширяем начало, если есть платежи раньше
        $earliestPayment = Payment::where('project_id', $project->id)
            ->selectRaw('MIN(COALESCE(payment_date, created_at)) as earliest')
            ->value('earliest');

        if ($earliestPayment) {
            $earliestPaymentStart = Carbon::make($earliestPayment)->startOfMonth();
            if (! $start || $earliestPaymentStart->lt($start)) {
                $start = $earliestPaymentStart;
            }
        }

        if ($start) {
            // Сначала получаем данные о платежах и счетах для расчёта переплаты
            $invoicesByMonth = Invoice::selectRaw("DATE_FORMAT(COALESCE(issued_at, created_at), '%Y-%m') as ym, SUM(amount) as total")
                ->where('project_id', $project->id)
                ->groupBy('ym')
                ->pluck('total', 'ym')
                ->all();

            $paymentsByMonth = Payment::selectRaw("DATE_FORMAT(COALESCE(payment_date, created_at), '%Y-%m') as ym, SUM(amount) as total")
                ->where('project_id', $project->id)
                ->groupBy('ym')
                ->pluck('total', 'ym')
                ->all();

            $contractAmount = (float) ($project->contract_amount ?? 0);
            $contractDate = $project->contract_date ? Carbon::make($project->contract_date) : null;
            $contractStart = $contractDate ? $contractDate->copy()->startOfMonth() : null;

            // Базовый конец — текущий месяц
            $end = Carbon::now()->endOfMonth();

            // Если проект закрыт — ограничиваем конец
            if (! empty($project->closed_at)) {
                $closed = Carbon::make($project->closed_at)->endOfMonth();
                if ($closed->lt($end)) {
                    $end = $closed;
                }
            }

            // Рассчитываем накопительный баланс до текущего месяца
            if ($contractAmount > 0 && $contractStart) {
                $tempBalance = 0;
                $tempCur = $start->copy();
                $currentMonth = Carbon::now()->startOfMonth();
                $expectedSchedule = $this->buildContractExpectedSchedule($project, $currentMonth->copy()->endOfMonth());

                while ($tempCur->lte($currentMonth)) {
                    $key = $tempCur->format('Y-m');
                    $invoiced = (float) ($invoicesByMonth[$key] ?? 0);
                    $paid = (float) ($paymentsByMonth[$key] ?? 0);

                    // Ожидаемая сумма
                    $expected = $invoiced > 0 ? $invoiced : (float) ($expectedSchedule[$key] ?? 0);

                    $tempBalance = $tempBalance + $paid - $expected;
                    $tempCur->addMonthNoOverflow();
                }

                // Если есть переплата — расширяем период вперёд, пока баланс положительный
                if ($tempBalance > 0 && $contractDate) {
                    $futureBalance = $tempBalance;
                    $maxFutureMonths = 24; // Ограничение на 2 года вперёд
                    $count = 0;

                    $futurePeriodStart = $contractDate->copy()->startOfDay();
                    $futurePeriodEnd = $futurePeriodStart->copy()->addMonthNoOverflow()->subDay()->endOfDay();
                    while ($futurePeriodEnd->lte($currentMonth->copy()->endOfMonth())) {
                        $futurePeriodStart->addMonthNoOverflow();
                        $futurePeriodEnd = $futurePeriodStart->copy()->addMonthNoOverflow()->subDay()->endOfDay();
                    }

                    while ($count < $maxFutureMonths) {
                        $end = $futurePeriodEnd->copy()->endOfMonth();
                        $futureBalance -= $contractAmount;
                        $futurePeriodStart->addMonthNoOverflow();
                        $futurePeriodEnd = $futurePeriodStart->copy()->addMonthNoOverflow()->subDay()->endOfDay();
                        $count++;

                        if ($futureBalance <= 0) {
                            break;
                        }
                    }
                }
            }

            // График ожидаемых начислений по периодам договора
            $expectedByMonth = $this->buildContractExpectedSchedule($project, $end->copy()->endOfMonth());

            // Названия месяцев
            $monthsRus = ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'];

            // Формируем месяцы от конца к началу (новые сверху/слева)
            $cur = $end->copy();
            while ($cur->gte($start)) {
                $months[] = [
                    'ym' => $cur->format('Y-m'),
                    'label' => $monthsRus[$cur->month - 1].' '.$cur->year,
                ];
                $cur->subMonthNoOverflow();
            }

            // Итоговые суммы
            $totalInvoices = array_sum($invoicesByMonth);
            $totalPayments = array_sum($paymentsByMonth);

            // Комментарии по месяцам (для уголка)
            $monthKeys = array_column($months, 'ym');
            if (! empty($monthKeys)) {
                $commentsByMonth = ProjectComment::selectRaw('month as ym, COUNT(*) as total')
                    ->where('project_id', $project->id)
                    ->whereIn('month', $monthKeys)
                    ->groupBy('month')
                    ->pluck('total', 'ym')
                    ->all();
            }
        }

        return view('admin.calendar.index', compact(
            'project',
            'months',
            'invoicesByMonth',
            'paymentsByMonth',
            'commentsByMonth',
            'totalInvoices',
            'totalPayments',
            'contractAmount',
            'contractStart',
            'expectedByMonth'
        ));
    }

    public function allProjects()
    {
        $months = [];
        $paymentsByMonth = [];
        $expectedByMonth = [];
        $periodTotal = 0;
        $owedTotal = 0;
        $paidTotal = 0;
        $difference = 0;
        $commentsMap = [];
        $projectRows = [];

        $currentMonth = Carbon::now()->startOfMonth();

        // найдём самую раннюю дату контракта
        $minContract = Project::whereNotNull('contract_date')->min('contract_date');

        if ($minContract) {
            $start = Carbon::make($minContract)->startOfMonth();
            $end = Carbon::now()->endOfMonth();

            // Получаем все платные проекты с контрактами (исключаем barter и own)
            $projects = Project::whereNotNull('contract_date')
                ->where('contract_amount', '>', 0)
                ->where('payment_type', Project::PAYMENT_TYPE_PAID)
                ->orderBy('title')
                ->get();

            // Получаем все счета по проектам
            $invoicesMap = [];
            $invoiceRows = Invoice::selectRaw("project_id, DATE_FORMAT(COALESCE(issued_at, created_at), '%Y-%m') as ym, SUM(amount) as total")
                ->whereIn('project_id', $projects->pluck('id'))
                ->groupBy('project_id', 'ym')
                ->get();
            foreach ($invoiceRows as $r) {
                $invoicesMap[$r->project_id][$r->ym] = (float) $r->total;
            }

            // Получаем все платежи по проектам
            $paymentsMap = [];
            $paymentRows = Payment::selectRaw("project_id, DATE_FORMAT(COALESCE(payment_date, created_at), '%Y-%m') as ym, SUM(amount) as total")
                ->whereIn('project_id', $projects->pluck('id'))
                ->groupBy('project_id', 'ym')
                ->get();
            foreach ($paymentRows as $r) {
                $paymentsMap[$r->project_id][$r->ym] = (float) $r->total;
            }

            // Для каждого проекта определяем конечную дату с учётом переплаты
            $maxEnd = $end->copy();
            $monthsRus = ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'];

            foreach ($projects as $project) {
                $contractDate = $project->contract_date ? Carbon::make($project->contract_date) : null;
                $contractStart = $contractDate ? $contractDate->copy()->startOfMonth() : null;
                $contractAmount = (float) $project->contract_amount;
                $closedAt = $project->closed_at ? Carbon::make($project->closed_at)->endOfMonth() : null;

                if ($contractAmount <= 0) {
                    continue;
                }

                // Определяем самую раннюю дату для расчёта (может быть раньше контракта если есть счета/платежи)
                $calcStart = $contractStart->copy();

                // Проверяем, есть ли счета или платежи раньше даты контракта
                $projectInvoices = $invoicesMap[$project->id] ?? [];
                $projectPayments = $paymentsMap[$project->id] ?? [];
                $allMonths = array_unique(array_merge(array_keys($projectInvoices), array_keys($projectPayments)));

                foreach ($allMonths as $ym) {
                    $monthDate = Carbon::createFromFormat('Y-m', $ym)->startOfMonth();
                    if ($monthDate->lt($calcStart)) {
                        $calcStart = $monthDate->copy();
                    }
                }

                // Рассчитываем баланс до текущего месяца по периодам договора (с 19 по 18 и т.п.)
                $tempBalance = 0;
                $tempCur = $calcStart->copy();
                $expectedSchedule = $this->buildContractExpectedSchedule($project, $currentMonth->copy()->endOfMonth());

                while ($tempCur->lte($currentMonth)) {
                    $key = $tempCur->format('Y-m');
                    $invoiced = (float) ($invoicesMap[$project->id][$key] ?? 0);
                    $paid = (float) ($paymentsMap[$project->id][$key] ?? 0);

                    $expected = $invoiced > 0 ? $invoiced : (float) ($expectedSchedule[$key] ?? 0);
                    $tempBalance = $tempBalance + $paid - $expected;
                    $tempCur->addMonthNoOverflow();
                }

                // Если есть переплата — расширяем период вперёд
                if ($tempBalance > 0 && $contractDate) {
                    $futureBalance = $tempBalance;
                    $maxFutureMonths = 24;
                    $count = 0;

                    $futurePeriodStart = $contractDate->copy()->startOfDay();
                    $futurePeriodEnd = $futurePeriodStart->copy()->addMonthNoOverflow()->subDay()->endOfDay();
                    while ($futurePeriodEnd->lte($currentMonth->copy()->endOfMonth())) {
                        $futurePeriodStart->addMonthNoOverflow();
                        $futurePeriodEnd = $futurePeriodStart->copy()->addMonthNoOverflow()->subDay()->endOfDay();
                    }

                    // Расширяем пока баланс положительный, плюс один период когда станет 0 или отрицательным
                    while ($count < $maxFutureMonths) {
                        $futureEnd = $futurePeriodEnd->copy()->endOfMonth();
                        if ($futureEnd->gt($maxEnd)) {
                            $maxEnd = $futureEnd->copy();
                        }
                        $futureBalance -= $contractAmount;
                        $futurePeriodStart->addMonthNoOverflow();
                        $futurePeriodEnd = $futurePeriodStart->copy()->addMonthNoOverflow()->subDay()->endOfDay();
                        $count++;

                        if ($futureBalance <= 0) {
                            break;
                        }
                    }
                }
            }

            $end = $maxEnd;

            // Построим месяцы ОТ КОНЦА К НАЧАЛУ
            $cur = $end->copy();
            while ($cur->gte($start)) {
                $months[] = [
                    'ym' => $cur->format('Y-m'),
                    'label' => $monthsRus[$cur->month - 1].' '.$cur->year,
                    'start' => $cur->copy()->startOfMonth(),
                    'end' => $cur->copy()->endOfMonth(),
                ];
                $cur->subMonthNoOverflow();
            }

            // Строим данные для каждого проекта с накопительным балансом
            $projectRows = [];

            foreach ($projects as $project) {
                $contractDate = $project->contract_date ? Carbon::make($project->contract_date) : null;
                $contractStart = $contractDate ? $contractDate->copy()->startOfMonth() : null;
                $contractAmount = (float) ($project->contract_amount ?? 0);
                $closedAt = $project->closed_at ? Carbon::make($project->closed_at)->endOfMonth() : null;

                // Определяем последний месяц для отображения этого проекта
                // По умолчанию - текущий месяц
                $lastDisplayMonth = $currentMonth->format('Y-m');

                // Если есть переплата - расширяем до месяца когда баланс станет <= 0
                $projectInvoices = $invoicesMap[$project->id] ?? [];
                $projectPayments = $paymentsMap[$project->id] ?? [];

                // Считаем баланс до текущего месяца
                $calcStart = $contractStart ? $contractStart->copy() : null;
                $allMonths = array_unique(array_merge(array_keys($projectInvoices), array_keys($projectPayments)));
                foreach ($allMonths as $ym) {
                    $monthDate = Carbon::createFromFormat('Y-m', $ym)->startOfMonth();
                    if (! $calcStart || $monthDate->lt($calcStart)) {
                        $calcStart = $monthDate->copy();
                    }
                }

                if ($calcStart && $contractAmount > 0) {
                    $tempBalance = 0;
                    $tempCur = $calcStart->copy();
                    $expectedSchedule = $this->buildContractExpectedSchedule($project, $currentMonth->copy()->endOfMonth());

                    while ($tempCur->lte($currentMonth)) {
                        $key = $tempCur->format('Y-m');
                        $invoiced = (float) ($projectInvoices[$key] ?? 0);
                        $paid = (float) ($projectPayments[$key] ?? 0);
                        $expected = $invoiced > 0 ? $invoiced : (float) ($expectedSchedule[$key] ?? 0);
                        $tempBalance = $tempBalance + $paid - $expected;
                        $tempCur->addMonthNoOverflow();
                    }

                    // Если есть переплата - расширяем
                    if ($tempBalance > 0 && $contractDate) {
                        $futureBalance = $tempBalance;
                        $maxFutureMonths = 24;
                        $count = 0;

                        $futurePeriodStart = $contractDate->copy()->startOfDay();
                        $futurePeriodEnd = $futurePeriodStart->copy()->addMonthNoOverflow()->subDay()->endOfDay();
                        while ($futurePeriodEnd->lte($currentMonth->copy()->endOfMonth())) {
                            $futurePeriodStart->addMonthNoOverflow();
                            $futurePeriodEnd = $futurePeriodStart->copy()->addMonthNoOverflow()->subDay()->endOfDay();
                        }

                        while ($count < $maxFutureMonths) {
                            $lastDisplayMonth = $futurePeriodEnd->format('Y-m');
                            $futureBalance -= $contractAmount;
                            $futurePeriodStart->addMonthNoOverflow();
                            $futurePeriodEnd = $futurePeriodStart->copy()->addMonthNoOverflow()->subDay()->endOfDay();
                            $count++;

                            if ($futureBalance <= 0) {
                                break;
                            }
                        }
                    }
                }

                $row = [
                    'project' => $project,
                    'months' => [],
                    'owed' => 0.0,
                    'paid' => 0.0,
                    'diff' => 0.0,
                    'contractAmount' => $contractAmount,
                    'contractStart' => $contractStart,
                    'lastDisplayMonth' => $lastDisplayMonth,
                ];

                // Сортируем месяцы хронологически для расчёта накопительного баланса
                $monthsChronological = array_reverse($months);
                $runningBalance = 0;
                $expectedSchedule = $this->buildContractExpectedSchedule($project, $end->copy()->endOfMonth());

                foreach ($monthsChronological as $m) {
                    $ym = $m['ym'];
                    $monthDate = Carbon::createFromFormat('Y-m', $ym)->startOfMonth();

                    // Проверяем активность проекта в этом месяце
                    $isActive = false;
                    if ($contractStart && $monthDate->gte($contractStart)) {
                        if (! $closedAt || $monthDate->lte($closedAt)) {
                            $isActive = true;
                        }
                    }

                    $invoiced = (float) ($invoicesMap[$project->id][$ym] ?? 0);
                    $paid = (float) ($paymentsMap[$project->id][$ym] ?? 0);

                    // Ожидаемая сумма: счёт учитывается всегда, иначе — по периоду договора (например, 19->18)
                    $expected = $invoiced > 0 ? $invoiced : (float) ($expectedSchedule[$ym] ?? 0);

                    $runningBalance = $runningBalance + $paid - $expected;

                    $row['months'][$ym] = [
                        'invoiced' => $invoiced,
                        'paid' => $paid,
                        'expected' => $expected,
                        'balance' => $runningBalance,
                        'isActive' => $isActive,
                    ];

                    // Для итогов учитываем только месяцы в пределах lastDisplayMonth
                    // Для будущих месяцев (> текущего) - только если они в пределах lastDisplayMonth
                    $isFutureMonth = $ym > $currentMonth->format('Y-m');
                    $isWithinDisplayLimit = $ym <= $lastDisplayMonth;
                    $shouldCountForTotal = ! $isFutureMonth || $isWithinDisplayLimit;

                    if ($shouldCountForTotal) {
                        $row['owed'] += $expected;
                    }
                    $row['paid'] += $paid;
                }

                $row['diff'] = $row['paid'] - $row['owed'];
                $projectRows[] = $row;
            }

            // Комментарии по проектам и месяцам
            $projectIds = $projects->pluck('id')->all();
            $monthKeys = array_column($months, 'ym');
            if (! empty($projectIds) && ! empty($monthKeys)) {
                $rows = ProjectComment::selectRaw('project_id, month as ym, COUNT(*) as total')
                    ->whereIn('project_id', $projectIds)
                    ->whereIn('month', $monthKeys)
                    ->groupBy('project_id', 'month')
                    ->get();

                foreach ($rows as $r) {
                    $commentsMap[$r->project_id][$r->ym] = (int) $r->total;
                }
            }

            // Суммарные оплаты и ожидания
            $periodTotal = 0;
            $owedTotal = 0;
            foreach ($projectRows as $row) {
                $periodTotal += $row['paid'];
                $owedTotal += $row['owed'];
            }
            $paidTotal = $periodTotal;
            $difference = $paidTotal - $owedTotal;

            // Агрегированные данные по месяцам для сводной строки
            $currentYm = $currentMonth->format('Y-m');
            foreach ($months as $m) {
                $ym = $m['ym'];
                $paymentsByMonth[$ym] = 0;
                $expectedByMonth[$ym] = 0;
                foreach ($projectRows as $row) {
                    $paymentsByMonth[$ym] += $row['months'][$ym]['paid'] ?? 0;

                    // Для expected учитываем lastDisplayMonth
                    $isFutureMonth = $ym > $currentYm;
                    $isWithinDisplayLimit = $ym <= ($row['lastDisplayMonth'] ?? $currentYm);
                    if (! $isFutureMonth || $isWithinDisplayLimit) {
                        $expectedByMonth[$ym] += $row['months'][$ym]['expected'] ?? 0;
                    }
                }
            }
        }

        return view('admin.calendar.all-projects', compact(
            'months',
            'paymentsByMonth',
            'expectedByMonth',
            'periodTotal',
            'owedTotal',
            'paidTotal',
            'difference',
            'projectRows',
            'commentsMap'
        ));
    }

    /**
     * Построить массив месяцев между start и end (включительно).
     * Каждый элемент содержит: start (Carbon), end (Carbon), ym (Y-m), label
     */
    protected function buildMonths(Carbon $start, Carbon $end)
    {
        $months = [];
        $monthsRus = ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'];

        $cur = $start->copy()->startOfMonth()->addMonthNoOverflow();
        while ($cur->lte($end)) {
            $months[] = [
                'start' => $cur->copy()->startOfMonth(),
                'end' => $cur->copy()->endOfMonth(),
                'ym' => $cur->format('Y-m'),
                'label' => $monthsRus[$cur->month - 1].' '.$cur->year,
            ];
            $cur->addMonthNoOverflow();
        }

        // показываем в порядке: текущий месяц (последний) —> предыдущие
        if (! empty($months)) {
            $months = array_reverse($months);
        }

        return $months;
    }

    /**
     * Построить ожидаемые платежи по периодам договора (например, 19->18).
     * Ожидаемое начисляется в месяц даты окончания периода.
     *
     * @return array<string, float> [ym => expectedAmount]
     */
    protected function buildContractExpectedSchedule(Project $project, Carbon $limitEnd): array
    {
        $schedule = [];

        $contractDate = $project->contract_date ? Carbon::make($project->contract_date)->startOfDay() : null;
        $contractAmount = (float) ($project->contract_amount ?? 0);
        if (! $contractDate || $contractAmount <= 0) {
            return $schedule;
        }

        $closedAt = $project->closed_at ? Carbon::make($project->closed_at)->endOfDay() : null;
        $hardEnd = $closedAt ? $closedAt->copy()->min($limitEnd->copy()->endOfDay()) : $limitEnd->copy()->endOfDay();

        $periodStart = $contractDate->copy();
        $periodEnd = $periodStart->copy()->addMonthNoOverflow()->subDay()->endOfDay();
        $guard = 0;

        while ($periodEnd->lte($hardEnd) && $guard < 240) {
            $ym = $periodStart->format('Y-m');
            $schedule[$ym] = ($schedule[$ym] ?? 0) + $contractAmount;

            $periodStart->addMonthNoOverflow();
            $periodEnd = $periodStart->copy()->addMonthNoOverflow()->subDay()->endOfDay();
            $guard++;
        }

        return $schedule;
    }

    /**
     * Аггрегировать оплаты по проектам и месяцам в виде: [projectId => [ym => total, ...], ...]
     */
    protected function aggregatePaymentsByProject(Carbon $first, Carbon $last)
    {
        $rows = Payment::selectRaw("project_id, DATE_FORMAT(COALESCE(payment_date, created_at), '%Y-%m') as ym, SUM(amount) as total")
            ->where(function ($q) use ($first, $last) {
                $q->whereNotNull('payment_date')->where('payment_date', '>=', $first)->where('payment_date', '<=', $last)
                    ->orWhere(function ($q2) use ($first, $last) {
                        $q2->whereNull('payment_date')->where('created_at', '>=', $first)->where('created_at', '<=', $last);
                    });
            })
            ->groupBy('project_id', 'ym')
            ->get();

        $map = [];
        foreach ($rows as $r) {
            $pid = $r->project_id;
            $ym = $r->ym;
            $map[$pid][$ym] = (float) $r->total;
        }

        return $map;
    }

    /**
     * Построить строки для каждого проекта: months-поля и общие totals.
     */
    protected function buildProjectRows($projects, $months, $paymentsMap)
    {
        $rows = [];

        foreach ($projects as $project) {
            $row = [
                'project' => $project,
                'months' => [],
                'owed' => 0.0,
                'paid' => 0.0,
                'diff' => 0.0,
            ];

            foreach ($months as $m) {
                $ym = $m['ym'];
                $paid = isset($paymentsMap[$project->id][$ym]) ? (float) $paymentsMap[$project->id][$ym] : 0.0;

                // ожидаемая сумма для проекта в этом месяце — если проект активен в этом месяце
                $active = false;
                if (! empty($project->contract_date)) {
                    $contract = Carbon::make($project->contract_date);
                    $monthStart = $m['start'];
                    $monthEnd = $m['end'];

                    if ($contract->lte($monthEnd)) {
                        if (empty($project->closed_at) || Carbon::make($project->closed_at)->gte($monthStart)) {
                            $active = true;
                        }
                    }
                }

                $expected = $active ? (float) ($project->contract_amount ?? 0) : 0.0;
                $diff = $paid - $expected;

                $row['months'][$ym] = [
                    'paid' => $paid,
                    'expected' => $expected,
                    'diff' => $diff,
                ];

                $row['owed'] += $expected;
                $row['paid'] += $paid;
            }

            $row['diff'] = $row['paid'] - $row['owed'];

            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Вычислить ожидаемые суммы по каждому месяцу (сумма contract_amount по активным проектам в месяце)
     */
    protected function computeExpectedByMonth(array $months)
    {
        $expected = [];

        foreach ($months as $m) {
            $monthStart = $m['start'];
            $monthEnd = $m['end'];

            $sum = Project::whereNotNull('contract_date')
                ->where('contract_amount', '>', 0)
                ->where('contract_date', '<=', $monthEnd->toDateString())
                ->where(function ($q) use ($monthStart) {
                    $q->whereNull('closed_at')->orWhere('closed_at', '>=', $monthStart->toDateString());
                })
                ->sum('contract_amount');

            $expected[$m['ym']] = (float) $sum;
        }

        return $expected;
    }

    public function index_copy(Project $project)
    {
        $months = [];
        $paymentsByMonth = [];
        $periodTotal = 0;
        $owedTotal = 0;
        $paidTotal = 0;
        $difference = 0;
        $commentsByMonth = [];

        if (! empty($project->contract_date)) {

            $start = Carbon::make($project->contract_date)->startOfMonth();

            // end = min(now, closed_at if set)
            $end = Carbon::now()->endOfMonth();
            if (! empty($project->closed_at)) {
                $closed = Carbon::make($project->closed_at)->endOfMonth();
                if ($closed->lt($end)) {
                    $end = $closed;
                }
            }

            // Названия месяцев
            $monthsRus = ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'];

            /**
             * Формируем месяцы ОТ КОНЦА К НАЧАЛУ
             * (новые месяцы сверху)
             */
            $cur = $end->copy();
            while ($cur->gte($start)) {
                $months[] = [
                    'ym' => $cur->format('Y-m'),
                    'label' => $monthsRus[$cur->month - 1].' '.$cur->year,
                ];
                $cur->subMonthNoOverflow();
            }

            /**
             * Суммы оплат по месяцам
             * используем payment_date или fallback на created_at
             */
            $paymentsByMonth = Payment::selectRaw("
                DATE_FORMAT(COALESCE(payment_date, created_at), '%Y-%m') as ym,
                SUM(amount) as total
            ")
                ->where('project_id', $project->id)
                ->where(function ($q) use ($start, $end) {
                    $q->whereNotNull('payment_date')
                        ->whereBetween('payment_date', [$start, $end])
                        ->orWhere(function ($q2) use ($start, $end) {
                            $q2->whereNull('payment_date')
                                ->whereBetween('created_at', [$start, $end]);
                        });
                })
                ->groupBy('ym')
                ->orderBy('ym', 'desc')
                ->pluck('total', 'ym')
                ->all();

            // Итоги
            $periodTotal = array_sum($paymentsByMonth);

            $monthsCount = count($months);
            $monthlyExpected = (float) ($project->contract_amount ?? 0);

            $owedTotal = $monthlyExpected > 0
                ? $monthlyExpected * $monthsCount
                : 0;

            $paidTotal = $periodTotal;
            $difference = $paidTotal - $owedTotal;

            // Считаем комментарии по месяцам (для отметки уголком)
            $commentsByMonth = [];
            $monthKeys = array_column($months, 'ym');
            if (! empty($monthKeys)) {
                $commentsByMonth = ProjectComment::selectRaw('month as ym, COUNT(*) as total')
                    ->where('project_id', $project->id)
                    ->whereIn('month', $monthKeys)
                    ->groupBy('month')
                    ->pluck('total', 'ym')
                    ->all();
            }
        }

        return view('admin.calendar.index', compact(
            'project',
            'months',
            'paymentsByMonth',
            'periodTotal',
            'owedTotal',
            'paidTotal',
            'difference',
            'commentsByMonth'
        ));
    }
}
