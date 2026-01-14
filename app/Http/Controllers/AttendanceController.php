<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Carbon;
use Yasumi\Yasumi;

class AttendanceController extends Controller
{
    public function userShow(User $user)
    {
        $projects = $user->projects()->get();
        $totalContractAmount = $projects->sum('contract_amount');
        $projectsCount = $projects->count();
        $lastMonth = Carbon::now()->subMonth();

        // Количество рабочих дней прошлого месяца
        $workingDaysLastMonth = $this->getWorkingDaysLastMonth();

        // По умолчанию: 2 удаленных дня
        $remoteDays = 2;

        // По умолчанию: 5 аудитов
        $auditsCount = 5;
        $auditPrice = 300;

        // Оклад
        $baseSalary = $user->salary_override ?? ($user->specialty->salary ?? 0);

        // Расчет зарплаты
        $salaryPerDay = $baseSalary / 22; // стандартный месяц = 22 дня
        $ordinaryPay = $workingDaysLastMonth * $salaryPerDay;
        $remotePay = $remoteDays * ($salaryPerDay * 0.5);
        $auditsPay = $auditsCount * $auditPrice;
        $individualBonusPay = $totalContractAmount * ($user->individual_bonus_percent / 100);

        $totalSalary = $ordinaryPay + $remotePay + $auditsPay + $individualBonusPay;

        return view('admin.attendance.userShow', compact(
            'user',
            'totalContractAmount',
            'projectsCount',
            'lastMonth',
            'workingDaysLastMonth',
            'remoteDays',
            'auditsCount',
            'auditPrice',
            'baseSalary',
            'ordinaryPay',
            'remotePay',
            'auditsPay',
            'individualBonusPay',
            'totalSalary'
        ));
    }

    private function getWorkingDaysLastMonth(): int
    {
        // Получаем прошлый месяц
        $lastMonth = Carbon::now()->subMonth();
        $year = $lastMonth->year;
        $month = $lastMonth->month;

        // Получаем все праздники РФ на год
        $holidays = Yasumi::create('Russia', $year);

        // Фильтруем праздники, которые попадают на прошлый месяц и не на выходные
        $holidayDates = collect($holidays)
            ->map(fn ($holiday) => $holiday->format('Y-m-d'))
            ->filter(fn ($date) => Carbon::parse($date)->month == $month && ! Carbon::parse($date)->isWeekend())
            ->toArray();

        // Получаем все дни месяца
        $allDays = collect(range(1, $lastMonth->daysInMonth))
            ->map(fn ($day) => $lastMonth->copy()->day($day));

        // Отбрасываем выходные и праздники
        $workingDays = $allDays->reject(fn ($date) => $date->isWeekend() || in_array($date->format('Y-m-d'), $holidayDates)
        );

        return $workingDays->count();
    }
}
