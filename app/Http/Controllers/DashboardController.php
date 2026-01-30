<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\LinkCard;
use App\Models\MonthlyExpense;
use App\Models\MonthlyExpenseStatus;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ProjectMarketerHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {

        // Если текущий пользователь — не admin, редиректим на отдельный экшен welcome
        $user = auth()->user();
        if (! $user || ! $user->isAdmin()) {
            return redirect()->route('welcome');
        }

        $period = $request->query('period');
        $monthParam = $request->query('month');
        $isAll = ($period === 'all');

        $linkCards = LinkCard::where('user_id', $user->id)
            ->orderBy('position')
            ->get();

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

        // Expected profit (sum of contract_amount for in-progress projects with non-negative balance)
        $expectedBaseQuery = Project::expectedProfitForMonth($start)
            ->where('status', Project::STATUS_IN_PROGRESS)
            ->where('balance', '>=', 0);

        $expectedProfit = (float) $expectedBaseQuery->sum('contract_amount');
        $expectedProjects = (clone $expectedBaseQuery)
            ->where('contract_amount', '>', 0)
            ->select(['id', 'title', 'contract_amount', 'closed_at', 'payment_type', 'balance', 'status'])
            ->orderBy('title')
            ->get();

        $expectedProjectIdsQuery = (clone $expectedBaseQuery)->select('id');
        $expectedReceivedMonth = (float) Payment::whereIn('project_id', $expectedProjectIdsQuery)
            ->whereRaw('DATE(COALESCE(payment_date, payments.created_at)) between ? and ?', [$start->toDateString(), $end->toDateString()])
            ->sum('payments.amount');

        $expectedRemaining = (float) ($expectedProfit - $expectedReceivedMonth);

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
            $commercialCount = \App\Models\Project::where('payment_type', 'paid')->count();

            $barterProjects = \App\Models\Project::where('payment_type', 'barter')
                ->select(['id', 'title', 'contract_amount', 'created_at', 'closed_at'])
                ->orderBy('title')
                ->get();

            $ownProjects = \App\Models\Project::where('payment_type', 'own')
                ->select(['id', 'title', 'contract_amount', 'created_at', 'closed_at'])
                ->orderBy('title')
                ->get();

            $commercialProjects = \App\Models\Project::where('payment_type', 'paid')
                ->select(['id', 'title', 'contract_amount', 'created_at', 'closed_at'])
                ->orderBy('title')
                ->get();
        } else {
            $barterCount = \App\Models\Project::where('payment_type', 'barter')
                ->whereRaw('DATE(created_at) between ? and ?', [$start->toDateString(), $end->toDateString()])->count();
            $ownCount = \App\Models\Project::where('payment_type', 'own')
                ->whereRaw('DATE(created_at) between ? and ?', [$start->toDateString(), $end->toDateString()])->count();
            $commercialCount = \App\Models\Project::where('payment_type', 'paid')
                ->whereRaw('DATE(created_at) between ? and ?', [$start->toDateString(), $end->toDateString()])->count();

            $barterProjects = \App\Models\Project::where('payment_type', 'barter')
                ->whereRaw('DATE(created_at) between ? and ?', [$start->toDateString(), $end->toDateString()])
                ->select(['id', 'title', 'contract_amount', 'created_at', 'closed_at'])
                ->orderBy('title')
                ->get();

            $ownProjects = \App\Models\Project::where('payment_type', 'own')
                ->whereRaw('DATE(created_at) between ? and ?', [$start->toDateString(), $end->toDateString()])
                ->select(['id', 'title', 'contract_amount', 'created_at', 'closed_at'])
                ->orderBy('title')
                ->get();

            $commercialProjects = \App\Models\Project::where('payment_type', 'paid')
                ->whereRaw('DATE(created_at) between ? and ?', [$start->toDateString(), $end->toDateString()])
                ->select(['id', 'title', 'contract_amount', 'created_at', 'closed_at'])
                ->orderBy('title')
                ->get();
        }

        $monthlyExpenses = collect();
        $monthlyExpensesMonth = null;
        $showWeeklyExpenses = false;

        if (! $isAll) {
            $monthlyExpensesMonth = $start->format('Y-m');
            $today = Carbon::today();
            $showWeeklyExpenses = empty($monthParam);
            $weekStart = $today->copy()->startOfWeek();
            $weekEnd = $today->copy()->endOfWeek();

            $monthlyExpenses = MonthlyExpense::query()
                // ->where('user_id', $user->id)
                ->where('is_active', true)
                ->orderBy('day_of_month')
                ->get();

            $statusMap = MonthlyExpenseStatus::query()
                ->whereIn('monthly_expense_id', $monthlyExpenses->pluck('id'))
                ->where('month', $monthlyExpensesMonth)
                ->get()
                ->keyBy('monthly_expense_id');

            $monthlyExpenses = $monthlyExpenses->map(function ($expense) use ($statusMap, $monthlyExpensesMonth, $today) {
                $status = $statusMap->get($expense->id);
                $monthStart = Carbon::createFromFormat('Y-m', $monthlyExpensesMonth)->startOfMonth();
                $day = min(max((int) $expense->day_of_month, 1), $monthStart->daysInMonth);
                $dueDate = $monthStart->copy()->day($day)->startOfDay();

                $state = 'awaiting';
                $label = 'Ожидает оплаты';
                $class = 'bg-yellow-100 text-yellow-800';

                if ($status && $status->paid_at) {
                    $state = 'paid';
                    $label = 'Оплачено';
                    $class = 'bg-green-100 text-green-800';
                } elseif ($dueDate->lt($today)) {
                    $state = 'overdue';
                    $label = 'Просрочено';
                    $class = 'bg-red-100 text-red-800';
                }

                $expense->status_state = $state;
                $expense->status_label = $label;
                $expense->status_class = $class;
                $expense->status_paid_at = $status?->paid_at;
                $expense->status_expense_id = $status?->expense_id;
                $expense->due_date = $dueDate;

                return $expense;
            });

            if ($showWeeklyExpenses) {
                $monthlyExpenses = $monthlyExpenses
                    ->filter(fn ($expense) => $expense->due_date->between($weekStart, $weekEnd))
                    ->values();
            }
        }

        $officeExpenseCategories = \App\Models\ExpenseCategory::office()->where('is_salary', false)->ordered()->get();

        $salaryFundExpenses = Expense::salary()
            ->with('category')
            ->whereRaw('DATE(COALESCE(expense_date, expenses.created_at)) between ? and ?', [$start->toDateString(), $end->toDateString()])
            ->orderByDesc('expense_date')
            ->get();
        $totalAmount = $salaryFundExpenses->sum('amount');
        // dd($totalAmount);

        // ===== Прогноз ФОТ по пользователям =====
        $forecastStart = $isAll ? Carbon::now()->startOfMonth() : $start->copy();
        $forecastEnd = $forecastStart->copy()->endOfMonth();
        $forecastMonthLabel = $forecastStart->locale('ru')->isoFormat('MMMM YYYY');

        $salaryForecastUsers = User::with('specialty')
            ->orderBy('name')
            ->get()
            ->map(function (User $u) use ($forecastStart, $forecastEnd) {
                $base = (float) ($u->salary_override ?? ($u->specialty->salary ?? 0));
                $bonusTotal = 0.0;
                $projectBreakdown = [];
                $source = 'auto';

                if (! is_null($u->forecast_amount)) {
                    $total = (float) $u->forecast_amount;
                    $source = 'manual';
                } elseif ($u->role === User::ROLE_MARKETER) {
                    $percent = $u->individual_bonus_percent ?? 5;

                    // Табель посещаемости за месяц (учитываем work=1, remote/short=0.5)
                    $attendanceDays = $u->attendanceDays()
                        ->whereBetween('date', [$forecastStart, $forecastEnd])
                        ->with('status')
                        ->get();

                    $workDayCoefficients = [];
                    foreach ($attendanceDays as $day) {
                        $dateKey = $day->date->format('Y-m-d');
                        $code = $day->status->code ?? null;
                        if ($code === 'work') {
                            $workDayCoefficients[$dateKey] = 1;
                        } elseif (in_array($code, ['remote', 'short'])) {
                            $workDayCoefficients[$dateKey] = 0.5;
                        }
                    }

                    $histories = ProjectMarketerHistory::where('user_id', $u->id)
                        ->where('assigned_at', '<=', $forecastEnd)
                        ->where(function ($q) use ($forecastStart) {
                            $q->whereNull('unassigned_at')
                                ->orWhere('unassigned_at', '>=', $forecastStart);
                        })
                        ->with('project:id,title,contract_amount')
                        ->get();

                    $projects = $histories
                        ->map(fn ($h) => $h->project)
                        ->filter()
                        ->unique('id');

                    $avgWorkDays = 22;

                    foreach ($projects as $p) {
                        // Считаем дни работы на проекте по табелю
                        $totalDays = 0;
                        $projectHistory = $histories->where('project_id', $p->id);

                        foreach ($projectHistory as $record) {
                            $recordStart = $record->assigned_at->max($forecastStart);
                            $recordEnd = ($record->unassigned_at ?? $forecastEnd)->min($forecastEnd);

                            $currentDate = $recordStart->copy();
                            while ($currentDate->lte($recordEnd)) {
                                $dateKey = $currentDate->format('Y-m-d');
                                $totalDays += $workDayCoefficients[$dateKey] ?? 0;
                                $currentDate->addDay();
                            }
                        }

                        $contract = (float) ($p->contract_amount ?? 0);
                        $maxBonus = $contract * ($percent / 100);
                        $bonusPerDay = $avgWorkDays > 0 ? $maxBonus / $avgWorkDays : 0;
                        $bonusAmount = $bonusPerDay * $totalDays;

                        $bonusTotal += $bonusAmount;
                        $projectBreakdown[] = [
                            'title' => $p->title,
                            'contract_amount' => $contract,
                            'bonus_percent' => $percent,
                            'max_bonus' => $maxBonus,
                            'days_worked' => $totalDays,
                            'bonus_amount' => $bonusAmount,
                        ];
                    }

                    $total = $base + $bonusTotal;
                } else {
                    $total = $base;
                }

                return [
                    'user' => $u,
                    'base' => $base,
                    'bonus_total' => $bonusTotal,
                    'total' => $total,
                    'source' => $source,
                    'project_breakdown' => $projectBreakdown,
                ];
            });

        $salaryForecastTotal = (float) $salaryForecastUsers->sum('total');

        $incomeOperations = Payment::leftJoin('projects', 'projects.id', '=', 'payments.project_id')
            ->select(
                'payments.id',
                'payments.amount',
                'payments.payment_date',
                'payments.created_at',
                'payments.note',
                'projects.title as project_title',
                'payments.project_id'
            )
            ->where(function ($q) {
                $q->whereNull('projects.payment_type')->orWhereNotIn('projects.payment_type', ['barter', 'own']);
            })
            ->whereRaw('DATE(COALESCE(payment_date, payments.created_at)) between ? and ?', [$start->toDateString(), $end->toDateString()])
            ->orderByDesc('payments.payment_date')
            ->orderByDesc('payments.created_at')
            ->get();

        $expenseOperations = Expense::leftJoin('projects', 'projects.id', '=', 'expenses.project_id')
            ->select(
                'expenses.id',
                'expenses.amount',
                'expenses.expense_date',
                'expenses.created_at',
                'expenses.description',
                'projects.title as project_title',
                'expenses.project_id'
            )
            ->where(function ($q) {
                $q->whereNull('projects.payment_type')->orWhereNotIn('projects.payment_type', ['barter', 'own']);
            })
            ->whereRaw('DATE(COALESCE(expense_date, expenses.created_at)) between ? and ?', [$start->toDateString(), $end->toDateString()])
            ->orderByDesc('expenses.expense_date')
            ->orderByDesc('expenses.created_at')
            ->get();

        return view('dashboard', compact(
            'labels', 'incomeData', 'expenseData', 'netData',
            'monthTotalIncome', 'monthTotalExpense', 'monthTotalNet',
            'monthTotal', 'data',
            'monthLabel', 'monthParam', 'yMax', 'yMin', 'step',
            'topProjectsLabels', 'topProjectsData', 'topMaxChart', 'topStep',
            'activeData', 'activeStep',
            'debtorLabels', 'debtorData', 'debtorRaw', 'debtorMaxChart', 'debtorStep',
            'monthVatTotal', 'monthUsnTotal', 'barterCount', 'ownCount', 'commercialCount', 'expectedProfit', 'expectedReceivedMonth', 'expectedRemaining',
            'monthlyExpenses', 'monthlyExpensesMonth', 'expectedProjects', 'showWeeklyExpenses',
            'barterProjects', 'ownProjects', 'commercialProjects', 'linkCards', 'officeExpenseCategories', 'totalAmount', 'salaryFundExpenses',
            'salaryForecastTotal', 'salaryForecastUsers', 'forecastMonthLabel',
            'incomeOperations', 'expenseOperations'
        ));
    }

    /**
     * Показывает welcome-страницу для не-admin пользователей (маркетологов/PM).
     */
    public function welcome(Request $request)
    {
        $user = auth()->user();
        if (! $user) {
            abort(403);
        }

        // Делаем данные для графика выплат по табелям (последние 12 месяцев)
        $end = Carbon::now()->startOfMonth();
        $start = $end->copy()->subMonths(11);

        // Инициализируем пустой период (Y-m keys)
        $periodKeys = collect();
        $labels = [];
        $current = $start->copy();
        while ($current->lte($end)) {
            $periodKeys->push($current->format('Y-m'));
            $labels[] = $current->locale('ru')->isoFormat('MMM YYYY');
            $current->addMonth();
        }

        // Получаем оплаченные табеля пользователя за период
        $reports = \App\Models\SalaryReport::where('status', 'paid')
            ->where('user_id', $user->id)
            ->whereBetween('month', [$start->toDateString(), $end->copy()->endOfMonth()->toDateString()])
            ->get()
            ->groupBy(function ($r) {
                return \Carbon\Carbon::parse($r->month)->format('Y-m');
            });

        $data = [];
        foreach ($periodKeys as $key) {
            $data[] = isset($reports[$key]) ? $reports[$key]->sum('total_salary') : 0;
        }

        // Последний оплаченный табель
        $lastPaid = \App\Models\SalaryReport::with('projectBonuses.project')
            ->where('user_id', $user->id)
            ->where('status', 'paid')
            ->orderByDesc('month')
            ->first();

        // Ожидаемая зарплата за текущий месяц (фиксированные значения: 22 рабочих дня, 0 удалённых, 0 аудитов, произвольные премии = 0)
        $currentMonthStart = Carbon::now()->startOfMonth();
        $projects = $user->projects()
            ->where(function ($q) use ($currentMonthStart) {
                $q->whereNull('closed_at')
                    ->orWhere('closed_at', '>=', $currentMonthStart);
            })
            ->get();
        $individualBonusPercent = $user->individual_bonus_percent ?? 5;
        $expectedProjectBonuses = [];
        $calculatedTotalBonus = 0;

        foreach ($projects as $project) {
            $contractAmount = $project->contract_amount ?? 0;
            $maxBonus = $contractAmount * ($individualBonusPercent / 100);
            $daysWorked = 22; // фиксированное требование
            $bonusAmount = $maxBonus; // при 22 рабочих днях = максимальная премия

            $expectedProjectBonuses[] = [
                'project' => $project,
                'contract_amount' => $contractAmount,
                'bonus_percent' => $individualBonusPercent,
                'max_bonus' => $maxBonus,
                'days_worked' => $daysWorked,
                'bonus_amount' => $bonusAmount,
            ];

            $calculatedTotalBonus += $bonusAmount;
        }

        $baseSalary = $user->salary_override ?? ($user->specialty->salary ?? 0);
        $expectedTotal = $baseSalary + $calculatedTotalBonus;

        $linkCards = LinkCard::where('user_id', $user->id)
            ->orderBy('position')
            ->get();

        return view('welcome', [
            'salaryLabels' => $labels,
            'salaryData' => $data,
            'lastPaid' => $lastPaid,
            'expected' => [
                'base_salary' => $baseSalary,
                'ordinary_days' => 22,
                'remote_days' => 0,
                'audits_count' => 0,
                'individual_bonus_percent' => $individualBonusPercent,
                'projectBonuses' => $expectedProjectBonuses,
                'total_expected' => $expectedTotal,
            ],
            'linkCards' => $linkCards,
        ]);
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
