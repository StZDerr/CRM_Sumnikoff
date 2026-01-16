<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {

        $period = $request->query('period');
        $monthParam = $request->query('month');
        $isAll = ($period === 'all');

        if ($isAll) {
            // For 'all time' view aggregate by month from the earliest record to now
            $firstPayment = Payment::selectRaw('MIN(COALESCE(payment_date, payments.created_at)) as first')->value('first');
            $firstExpense = Expense::selectRaw('MIN(COALESCE(expense_date, expenses.created_at)) as first')->value('first');

            $firstDates = array_filter([$firstPayment, $firstExpense]);
            if (! empty($firstDates)) {
                $first = collect($firstDates)->map(function ($d) {
                    return Carbon::parse($d);
                })->min();
                $start = $first->startOfMonth();
            } else {
                $start = Carbon::now()->startOfMonth();
            }

            $end = Carbon::now()->endOfMonth();

            // Income per month (YYYY-MM) — исключаем платежи по бартерным и "своим" проектам
            $incRows = Payment::leftJoin('projects', 'projects.id', '=', 'payments.project_id')
                ->selectRaw("DATE_FORMAT(COALESCE(payment_date, payments.created_at), '%Y-%m') as ym, SUM(payments.amount) as total")
                ->where(function ($q) {
                    $q->whereNull('projects.payment_type')->orWhereNotIn('projects.payment_type', ['barter', 'own']);
                })
                ->groupBy('ym')->orderBy('ym')->get()->pluck('total', 'ym')->toArray();

            // Expense per month — исключаем расходы, привязанные к бартерным и "своим" проектам
            $expRows = Expense::leftJoin('projects', 'projects.id', '=', 'expenses.project_id')
                ->selectRaw("DATE_FORMAT(COALESCE(expense_date, expenses.created_at), '%Y-%m') as ym, SUM(expenses.amount) as total")
                ->where(function ($q) {
                    $q->whereNull('projects.payment_type')->orWhereNotIn('projects.payment_type', ['barter', 'own']);
                })
                ->groupBy('ym')->orderBy('ym')->get()->pluck('total', 'ym')->toArray();

            // Build per-month arrays
            $labels = [];
            $incomeData = [];
            $expenseData = [];
            $netData = [];
            $current = $start->copy();
            $maxPositive = 0;
            $minNet = 0;

            while ($current->lte($end)) {
                $key = $current->format('Y-m');
                $inc = isset($incRows[$key]) ? (float) $incRows[$key] : 0.0;
                $exp = isset($expRows[$key]) ? (float) $expRows[$key] : 0.0;
                $net = $inc - $exp;

                $labels[] = $current->locale('ru')->isoFormat('MMM YYYY');
                $incomeData[] = $inc;
                $expenseData[] = $exp;
                $netData[] = $net;

                $maxPositive = max($maxPositive, $inc, $exp);
                $minNet = min($minNet, $net);

                $current->addMonth();
            }
        } else {
            $start = $monthParam ? Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth() : Carbon::now()->startOfMonth();
            $end = $start->copy()->endOfMonth();

            // Income per day — исключаем платежи по бартерным и "своим" проектам
            $incRows = Payment::leftJoin('projects', 'projects.id', '=', 'payments.project_id')
                ->selectRaw('DATE(COALESCE(payment_date, payments.created_at)) as day, SUM(payments.amount) as total')
                ->where(function ($q) {
                    $q->whereNull('projects.payment_type')->orWhereNotIn('projects.payment_type', ['barter', 'own']);
                })
                ->whereRaw('DATE(COALESCE(payment_date, payments.created_at)) between ? and ?', [$start->toDateString(), $end->toDateString()])
                ->groupBy('day')->orderBy('day')->get()->pluck('total', 'day')->toArray();

            // Expense per day — исключаем расходы, привязанные к бартерным и "своим" проектам
            $expRows = Expense::leftJoin('projects', 'projects.id', '=', 'expenses.project_id')
                ->selectRaw('DATE(COALESCE(expense_date, expenses.created_at)) as day, SUM(expenses.amount) as total')
                ->where(function ($q) {
                    $q->whereNull('projects.payment_type')->orWhereNotIn('projects.payment_type', ['barter', 'own']);
                })
                ->whereRaw('DATE(COALESCE(expense_date, expenses.created_at)) between ? and ?', [$start->toDateString(), $end->toDateString()])
                ->groupBy('day')->orderBy('day')->get()->pluck('total', 'day')->toArray();

            // Build per-day arrays
            $labels = [];
            $incomeData = [];
            $expenseData = [];
            $netData = [];
            $current = $start->copy();
            $maxPositive = 0;
            $minNet = 0;

            while ($current->lte($end)) {
                $dayKey = $current->toDateString();
                $inc = isset($incRows[$dayKey]) ? (float) $incRows[$dayKey] : 0.0;
                $exp = isset($expRows[$dayKey]) ? (float) $expRows[$dayKey] : 0.0;
                $net = $inc - $exp;

                $labels[] = $current->format('d');
                $incomeData[] = $inc;
                $expenseData[] = $exp;
                $netData[] = $net;

                $maxPositive = max($maxPositive, $inc, $exp);
                $minNet = min($minNet, $net);

                $current->addDay();
            }
        }

        $monthTotalIncome = array_sum($incomeData);
        $monthTotalExpense = array_sum($expenseData);
        $monthTotalNet = $monthTotalIncome - $monthTotalExpense;

        // compatibility aliases for the view
        $monthTotal = $monthTotalIncome;
        $data = $incomeData;

        // nice scale
        $step = $this->niceStep($maxPositive);
        // базовый верхний шаг (округлённый вверх до кратного step)
        $yMax = (int) max($step, ceil($maxPositive / $step) * $step);

        // добавляем дополнительный запас сверху (чтобы верхняя точка была видна)
        $yMax += $step;
        $yMin = 0;
        if ($minNet < 0) {
            // ensure negative side present (symmetry optional)
            $neg = (int) (ceil(abs($minNet) / $step) * $step);
            $yMin = -$neg;
            // ensure yMax at least step
            $yMax = max($yMax, $neg);
        }

        $monthLabel = $start->locale('ru')->isoFormat('MMMM YYYY');

        // Top 5 projects by income for the selected month
        $topProjectsRows = Payment::selectRaw('projects.id, COALESCE(projects.title, CONCAT("Проект #", projects.id)) as title, SUM(payments.amount) as total')
            ->leftJoin('projects', 'projects.id', '=', 'payments.project_id')
            ->where(function ($q) {
                $q->whereNull('projects.payment_type')->orWhereNotIn('projects.payment_type', ['barter', 'own']);
            })
            ->whereRaw('DATE(COALESCE(payment_date, payments.created_at)) between ? and ?', [$start->toDateString(), $end->toDateString()])
            ->groupBy('projects.id', 'projects.title')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $topProjectsLabels = $topProjectsRows->pluck('title')->all();
        $topProjectsData = $topProjectsRows->pluck('total')->map(function ($v) {
            return (float) $v;
        })->all();

        $topMax = count($topProjectsData) ? max($topProjectsData) : 0;
        $topStep = $this->niceStep($topMax);
        $topMaxChart = (int) max($topStep, ceil($topMax / $topStep) * $topStep) + $topStep; // add padding

        // === Active projects over time ===
        // Fetch projects created on or before end
        $projects = \App\Models\Project::select('id', 'created_at', 'closed_at')->whereDate('created_at', '<=', $end->toDateString())->get();

        // Build period keys (same as $labels but in full key form)
        $periodKeys = [];
        $current = $start->copy();
        if ($isAll) {
            while ($current->lte($end)) {
                $periodKeys[] = $current->format('Y-m');
                $current->addMonth();
            }
        } else {
            while ($current->lte($end)) {
                $periodKeys[] = $current->toDateString();
                $current->addDay();
            }
        }

        // Initialize diff map
        $diff = array_fill_keys($periodKeys, 0);

        foreach ($projects as $p) {
            $pStart = \Carbon\Carbon::parse($p->created_at)->startOfDay();
            if ($pStart->gt($end)) {
                continue;
            }

            if ($isAll) {
                $startKey = max($pStart, $start)->format('Y-m');
                // project not active if closed on or before start
                if ($p->closed_at) {
                    $pClosed = \Carbon\Carbon::parse($p->closed_at)->startOfDay();
                    if ($pClosed->lte($start)) {
                        continue;
                    }
                    $endKey = min($pClosed, $end)->format('Y-m');
                } else {
                    $endKey = null;
                }

                if (isset($diff[$startKey])) {
                    $diff[$startKey]++;
                }
                if ($endKey && isset($diff[$endKey])) {
                    $diff[$endKey]--;
                }
            } else {
                $startKey = max($pStart, $start)->toDateString();
                if ($p->closed_at) {
                    $pClosed = \Carbon\Carbon::parse($p->closed_at)->startOfDay();
                    if ($pClosed->lte($start)) {
                        continue;
                    }
                    $endKey = min($pClosed, $end)->toDateString();
                } else {
                    $endKey = null;
                }

                if (isset($diff[$startKey])) {
                    $diff[$startKey]++;
                }
                if ($endKey && isset($diff[$endKey])) {
                    $diff[$endKey]--;
                }
            }
        }

        // Prefix sum to get counts
        $activeData = [];
        $running = 0;
        foreach ($periodKeys as $k) {
            $running += ($diff[$k] ?? 0);
            $activeData[] = (int) $running;
        }

        $activeMax = count($activeData) ? max($activeData) : 0;
        $activeStep = max(1, (int) ceil($activeMax / 4));

        // === Debtors (projects with negative balance) ===
        // Show top projects that owe us (balance < 0). Chart uses absolute values for bar heights.
        $debtorsRows = \App\Models\Project::select('id', 'title', 'balance')
            ->where('balance', '<', 0)
            ->where(function ($q) {
                $q->whereNull('payment_type')->orWhereNotIn('payment_type', ['barter', 'own']);
            })
            ->orderBy('balance') // most negative first
            ->limit(10)
            ->get();

        $debtorLabels = $debtorsRows->pluck('title')->all();
        // Use absolute values for chart bars
        $debtorData = $debtorsRows->pluck('balance')->map(function ($v) {
            return (float) abs($v);
        })->all();
        // Keep raw balances (negative) for tooltips if needed
        $debtorRaw = $debtorsRows->pluck('balance')->map(function ($v) {
            return (float) $v;
        })->all();

        $debtorMax = count($debtorData) ? max($debtorData) : 0;
        $debtorStep = max(10000, $this->niceStep($debtorMax));
        $debtorMaxChart = (int) (max($debtorStep, ceil($debtorMax / $debtorStep) * $debtorStep) + $debtorStep);

        // Taxes totals for the selected period (or full range if period=all)
        $monthVatTotal = (float) Payment::leftJoin('projects', 'projects.id', '=', 'payments.project_id')
            ->where(function ($q) {
                $q->whereNull('projects.payment_type')->orWhereNotIn('projects.payment_type', ['barter', 'own']);
            })
            ->whereRaw('DATE(COALESCE(payment_date, payments.created_at)) between ? and ?', [$start->toDateString(), $end->toDateString()])
            ->sum('vat_amount');
        $monthUsnTotal = (float) Payment::leftJoin('projects', 'projects.id', '=', 'payments.project_id')
            ->where(function ($q) {
                $q->whereNull('projects.payment_type')->orWhereNotIn('projects.payment_type', ['barter', 'own']);
            })
            ->whereRaw('DATE(COALESCE(payment_date, payments.created_at)) between ? and ?', [$start->toDateString(), $end->toDateString()])
            ->sum('usn_amount');

        // Count barter projects and own projects for the selected period (or all time)
        if ($isAll) {
            $barterCount = \App\Models\Project::where('payment_type', 'barter')->count();
            $ownCount = \App\Models\Project::where('payment_type', 'own')->count();
        } else {
            $barterCount = \App\Models\Project::where('payment_type', 'barter')
                ->whereRaw('DATE(created_at) between ? and ?', [$start->toDateString(), $end->toDateString()])->count();
            $ownCount = \App\Models\Project::where('payment_type', 'own')
                ->whereRaw('DATE(created_at) between ? and ?', [$start->toDateString(), $end->toDateString()])->count();
        }

        return view('dashboard', compact(
            'labels', 'incomeData', 'expenseData', 'netData',
            'monthTotalIncome', 'monthTotalExpense', 'monthTotalNet',
            'monthTotal', 'data',
            'monthLabel', 'monthParam', 'yMax', 'yMin', 'step',
            'topProjectsLabels', 'topProjectsData', 'topMaxChart', 'topStep',
            'activeData', 'activeStep',
            'debtorLabels', 'debtorData', 'debtorRaw', 'debtorMaxChart', 'debtorStep',
            'monthVatTotal', 'monthUsnTotal', 'barterCount', 'ownCount'
        ));
    }

    /**
     * Вычислить "красивый" шаг по максимальному значению.
     * Вернёт минимальный шаг >= maxValue/4 вида 1*10^n, 2*10^n или 5*10^n.
     */
    protected function niceStep(float $maxValue): int
    {
        if ($maxValue <= 0) {
            return 1000; // дефолтный шаг
        }

        $raw = $maxValue / 4.0;
        $pow = pow(10, floor(log10($raw)));
        foreach ([1, 2, 5, 10] as $m) {
            $candidate = (int) ($m * $pow);
            if ($candidate >= $raw) {
                return $candidate;
            }
        }

        return (int) ($pow * 10);
    }
}
