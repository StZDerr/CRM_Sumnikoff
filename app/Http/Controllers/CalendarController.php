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
            $start = Carbon::make($project->contract_date)->startOfMonth();
            $end = Carbon::now()->startOfMonth();

            $monthsRus = ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'];

            $cur = $start->copy();
            while ($cur->lte($end)) {
                $months[] = [
                    'ym' => $cur->format('Y-m'),
                    'label' => $monthsRus[$cur->month - 1].' '.$cur->year,
                ];
                $cur->addMonth();
            }

            // суммы оплат по месяцам
            $paymentsByMonth = Payment::selectRaw("DATE_FORMAT(payment_date, '%Y-%m') as ym, SUM(amount) as total")
                ->where('project_id', $project->id)
                ->whereNotNull('payment_date')
                ->whereBetween('payment_date', [$start->copy()->startOfMonth(), $end->copy()->endOfMonth()])
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
