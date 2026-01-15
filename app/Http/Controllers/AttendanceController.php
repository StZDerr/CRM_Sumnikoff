<?php

namespace App\Http\Controllers;

use App\Models\AttendanceDay;
use App\Models\AttendanceStatus;
use App\Models\SalaryReport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Yasumi\Yasumi;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        // 👉 Выбранный год или текущий
        $year = (int) $request->get('year', now()->year);

        // Все сотрудники
        $users = User::orderBy('name')->get();

        // Генерация всех дней выбранного года
        $days = collect();
        $date = Carbon::create($year, 1, 1);
        while ($date->year === $year) {
            $days->push($date->copy());
            $date->addDay();
        }

        // Табель за выбранный год
        $attendance = AttendanceDay::with('status')
            ->whereYear('date', $year)
            ->get()
            ->keyBy(fn ($item) => $item->user_id.'_'.$item->date->toDateString());

        // Статусы
        $statuses = AttendanceStatus::all()->keyBy('code');

        return view('admin.attendance.index', compact(
            'users',
            'days',
            'attendance',
            'statuses',
            'year'
        ));
    }

    // Сохраняем/обновляем статус
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'status' => 'nullable|exists:attendance_statuses,code',
            'comment' => 'nullable|string|max:255',
        ]);

        if ($request->status) {
            $status = AttendanceStatus::where('code', $request->status)->firstOrFail();

            AttendanceDay::updateOrCreate(
                [
                    'user_id' => $request->user_id,
                    'date' => $request->date,
                ],
                [
                    'status_id' => $status->id,
                    'comment' => $request->comment,
                ]
            );
        } else {
            // Если статус пустой, оставляем только комментарий или удаляем запись
            $attendance = AttendanceDay::where('user_id', $request->user_id)
                ->where('date', $request->date)
                ->first();

            if ($attendance) {
                if ($request->comment) {
                    $attendance->comment = $request->comment;
                    $attendance->save();
                } else {
                    // Если нет комментария и нет статуса — удаляем запись
                    $attendance->delete();
                }
            } else {
                if ($request->comment) {
                    // Создаём запись только с комментарием
                    AttendanceDay::create([
                        'user_id' => $request->user_id,
                        'date' => $request->date,
                        'comment' => $request->comment,
                    ]);
                }
            }
        }

        // Возвращаем текущие данные статуса
        $attendance = AttendanceDay::with('status')
            ->where('user_id', $request->user_id)
            ->where('date', $request->date)
            ->first();

        return response()->json([
            'success' => true,
            'color' => $attendance?->status?->color ?? '',
            'title' => $attendance?->status?->title ?? '',
            'comment' => $attendance?->comment ?? '',
        ]);
    }

    public function approvals()
    {
        // Получаем все табели со статусом 'submitted'
        $reports = SalaryReport::with('user')
            ->where('status', 'submitted')
            ->orderByDesc('month')
            ->get();

        return view('admin.attendance.approvals', compact('reports'));
    }

    public function update(Request $request, SalaryReport $report)
    {
        $request->validate([
            'ordinary_days' => 'required|numeric|min:0',
            'remote_days' => 'required|numeric|min:0',
            'audits_count' => 'nullable|numeric|min:0',
            'individual_bonus' => 'nullable|numeric|min:0',
            'custom_bonus' => 'nullable|numeric|min:0',
            'total_salary' => 'required|numeric|min:0', // если передаётся с формы
        ]);

        $report->fill([
            'ordinary_days' => $request->input('ordinary_days'),
            'remote_days' => $request->input('remote_days'),
            'audits_count' => $request->input('audits_count') ?? 0,
            'individual_bonus' => $request->input('individual_bonus') ?? 0,
            'custom_bonus' => $request->input('custom_bonus') ?? 0,
            'total_salary' => $request->input('total_salary'),
            'updated_by' => auth()->id(),
        ])->save();

        return redirect()->back()->with('success', 'Табель успешно обновлен');
    }

    public function show(SalaryReport $report)
    {
        return view('admin.attendance.show', compact('report'));
    }

    public function approve(SalaryReport $report)
    {
        $report->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Табель успешно одобрен');
    }

    // Отклонение табеля начальством
    public function reject(SalaryReport $report)
    {
        $report->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Табель отклонён');
    }

    public function userShow(User $user)
    {
        $lastMonth = Carbon::now()->subMonth();
        $monthStart = $lastMonth->copy()->startOfMonth();
        $monthEnd = $lastMonth->copy()->endOfMonth();

        // Получаем дни посещаемости пользователя за прошлый месяц
        $attendanceDays = $user->attendanceDays()
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->with('status')
            ->get();

        // Считаем по статусам
        $ordinaryDays = $attendanceDays->where('status.code', 'work')->count();
        $remoteDays = $attendanceDays
            ->whereIn('status.code', ['remote', 'short'])
            ->count();
        $absentDays = $attendanceDays->where('status.code', 'absent')->count();

        // Базовые данные для зарплаты
        $projects = $user->projects()->get();
        $totalContractAmount = $projects->sum('contract_amount');
        $projectsCount = $projects->count();
        $auditsCount = 0;
        $auditPrice = 300;
        $baseSalary = $user->salary_override ?? ($user->specialty->salary ?? 0);

        // Проверяем, есть ли уже табель за месяц
        $existingReport = SalaryReport::where('user_id', $user->id)
            ->where('month', $monthStart->format('Y-m-01'))
            ->first();

        return view('admin.attendance.userShow', compact(
            'user',
            'lastMonth',
            'ordinaryDays',
            'remoteDays',
            'absentDays',
            'totalContractAmount',
            'projectsCount',
            'auditsCount',
            'auditPrice',
            'baseSalary',
            'existingReport' // <-- передаем во view
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

    public function submitForApproval(Request $request, User $user)
    {
        $request->validate([
            'month' => 'required|date',
            'base_salary' => 'required|numeric|min:0',
            'ordinary_days' => 'nullable|numeric|min:0',
            'remote_days' => 'nullable|numeric|min:0',
            'audits_count' => 'nullable|integer|min:0',
            'individual_bonus' => 'nullable|numeric|min:0',
            'custom_bonus' => 'nullable|numeric|min:0',
            'total_salary' => 'required|numeric|min:0',
            'status' => 'nullable|in:draft,submitted,approved,rejected',
            'comment' => 'nullable|string|max:255',
        ]);

        // Сохраняем переданные значения без дополнительного пересчёта
        $salaryReport = SalaryReport::updateOrCreate(
            [
                'user_id' => $user->id,
                'month' => $request->month,
            ],
            [
                'base_salary' => $request->base_salary,
                'ordinary_days' => $request->ordinary_days ?? 0,
                'remote_days' => $request->remote_days ?? 0,
                'audits_count' => $request->audits_count ?? 0,
                'individual_bonus' => $request->individual_bonus ?? 0,
                'custom_bonus' => $request->custom_bonus ?? 0,
                'total_salary' => $request->total_salary,
                'status' => $request->status ?? 'submitted',
                'comment' => $request->comment,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]
        );

        // Перенаправляем на страницу просмотра созданного/обновлённого табеля
        return redirect()->route('attendance.show', $salaryReport->id)->with('success', 'Табель сохранён!');
    }
}
