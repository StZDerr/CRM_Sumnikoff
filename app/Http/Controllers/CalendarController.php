<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ProjectComment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class CalendarController extends Controller
{
    public function index(Project $project)
    {
        $months = [];
        $invoicesByMonth = [];
        $paymentsByMonth = [];
        $commentsByMonth = [];
        $totalInvoices = 0;
        $totalPayments = 0;

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
            $end = Carbon::now()->endOfMonth();

            // Если проект закрыт — ограничиваем конец
            if (! empty($project->closed_at)) {
                $closed = Carbon::make($project->closed_at)->endOfMonth();
                if ($closed->lt($end)) {
                    $end = $closed;
                }
            }

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

            // Суммы счетов по месяцам (по issued_at, fallback на created_at)
            $invoicesByMonth = Invoice::selectRaw("DATE_FORMAT(COALESCE(issued_at, created_at), '%Y-%m') as ym, SUM(amount) as total")
                ->where('project_id', $project->id)
                ->groupBy('ym')
                ->pluck('total', 'ym')
                ->all();

            // Суммы поступлений по месяцам (по payment_date, fallback на created_at)
            $paymentsByMonth = Payment::selectRaw("DATE_FORMAT(COALESCE(payment_date, created_at), '%Y-%m') as ym, SUM(amount) as total")
                ->where('project_id', $project->id)
                ->groupBy('ym')
                ->pluck('total', 'ym')
                ->all();

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
            'totalPayments'
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

        // найдём самую раннюю дату контракта
        $minContract = Project::whereNotNull('contract_date')->min('contract_date');

        if ($minContract) {
            $start = Carbon::make($minContract)->startOfMonth();
            $end = Carbon::now()->endOfMonth();

            // Построим месяцы ОТ КОНЦА К НАЧАЛУ
            $monthsRus = ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'];
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

            if (! empty($months)) {
                $first = end($months)['start']; // самый старый месяц
                $last = $months[0]['end'];      // самый новый месяц

                // агрегируем все платежи по проектам и месяцам за период
                $cacheKey = "calendar:paymentsMap:{$first->toDateString()}:{$last->toDateString()}";
                $paymentsMap = Cache::remember($cacheKey, 300, function () use ($first, $last) {
                    return $this->aggregatePaymentsByProject($first, $last);
                });

                // получим все проекты
                $projects = Project::orderBy('title')->get();

                // построим строки по проектам
                $projectRows = $this->buildProjectRows($projects, $months, $paymentsMap);

                // посчитаем комментарии по проектам и месяцам (для all-projects view)
                $commentsMap = [];
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

                // суммарные оплаты по месяцам
                $paymentsByMonth = Payment::selectRaw("DATE_FORMAT(COALESCE(payment_date, created_at), '%Y-%m') as ym, SUM(amount) as total")
                    ->where(function ($q) use ($first, $last) {
                        $q->whereNotNull('payment_date')->whereBetween('payment_date', [$first, $last])
                            ->orWhere(function ($q2) use ($first, $last) {
                                $q2->whereNull('payment_date')->whereBetween('created_at', [$first, $last]);
                            });
                    })
                    ->groupBy('ym')
                    ->orderByDesc('ym') // последние месяцы сверху
                    ->pluck('total', 'ym')
                    ->all();

                // ожидаемая сумма по месяцу
                $expectedByMonth = $this->computeExpectedByMonth($months);

                $periodTotal = array_sum($paymentsByMonth);
                $owedTotal = array_sum($expectedByMonth);
                $paidTotal = $periodTotal;
                $difference = $paidTotal - $owedTotal;
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
