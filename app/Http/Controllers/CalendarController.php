<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Project;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function index(Project $project)
    {
        $months = [];
        $paymentsByMonth = [];
        $periodTotal = 0;
        $owedTotal = 0;
        $paidTotal = 0;
        $difference = 0;

        if (! empty($project->contract_date)) {
            $start = Carbon::make($project->contract_date);

            // end = min(now, closed_at if set)
            $end = Carbon::now();
            if (! empty($project->closed_at)) {
                $closed = Carbon::make($project->closed_at);
                if ($closed->lt($end)) {
                    $end = $closed;
                }
            }

            // вычисляем месяцы (только полностью завершённые месяцы)
            $monthsRus = ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'];

            $cur = $start->copy()->addMonthNoOverflow();
            while ($cur->lte($end)) {
                $months[] = [
                    'ym' => $cur->format('Y-m'),
                    'label' => $monthsRus[$cur->month - 1].' '.$cur->year,
                ];
                $cur->addMonthNoOverflow();
            }

            // суммы оплат по месяцам (в рамках периода)
            // Суммы оплат по месяцам — используем payment_date или fallback на created_at
            $paymentsByMonth = Payment::selectRaw("DATE_FORMAT(COALESCE(payment_date, created_at), '%Y-%m') as ym, SUM(amount) as total")
                ->where('project_id', $project->id)
                ->where(function ($q) use ($start, $end) {
                    $q->whereNotNull('payment_date')
                        ->where('payment_date', '>=', $start->copy()->startOfMonth())
                        ->where('payment_date', '<=', $end->copy()->endOfDay())
                        ->orWhere(function ($q2) use ($start, $end) {
                            $q2->whereNull('payment_date')
                                ->where('created_at', '>=', $start->copy()->startOfMonth())
                                ->where('created_at', '<=', $end->copy()->endOfDay());
                        });
                })
                ->groupBy('ym')
                ->pluck('total', 'ym')
                ->all();

            $periodTotal = array_sum($paymentsByMonth);

            // расчёт задолженности: контрактная сумма считается за месяц
            $monthsCount = count($months);
            $monthlyExpected = (float) ($project->contract_amount ?? 0);
            if ($monthlyExpected > 0) {
                $owedTotal = $monthlyExpected * $monthsCount;
            } else {
                $owedTotal = 0;
            }

            $paidTotal = $periodTotal;
            $difference = $paidTotal - $owedTotal;
        }

        return view('admin.calendar.index', compact(
            'project',
            'months',
            'paymentsByMonth',
            'periodTotal',
            'owedTotal',
            'paidTotal',
            'difference'
        ));
    }
}
