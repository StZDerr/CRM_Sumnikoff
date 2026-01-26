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
    public function __construct()
    {
        // Все методы требуют авторизации
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        // 👉 Выбранный год или текущий
        $year = (int) $request->get('year', now()->year);

        $currentUser = auth()->user();

        // Admin видит всех сотрудников, остальные — только себя
        if ($currentUser->isAdmin()) {
            $users = User::orderBy('name')->get();
        } else {
            $users = User::where('id', $currentUser->id)->get();
        }

        // Убираем отчество из ФИО
        $users = $users->map(function ($user) {
            $parts = explode(' ', $user->name);

            if (count($parts) === 3) {
                // Имя + Фамилия, без отчества
                $user->name_without_middle = $parts[0].' '.$parts[1];
            } else {
                $user->name_without_middle = $user->name;
            }

            return $user;
        });

        // Генерация всех дней выбранного года
        $days = collect();
        $date = Carbon::create($year, 1, 1);
        while ($date->year === $year) {
            $days->push($date->copy());
            $date->addDay();
        }

        // Табель за выбранный год (для видимых пользователей)
        $userIds = $users->pluck('id');
        $attendance = AttendanceDay::with('status')
            ->whereYear('date', $year)
            ->whereIn('user_id', $userIds)
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
        // Только admin может вносить изменения в табель
        if (! auth()->user()->isAdmin()) {
            return response()->json(['success' => false, 'error' => 'Доступ запрещён'], 403);
        }

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
            // Если статус пустой — очищаем статус и обрабатываем комментарий:
            // - если есть комментарий — сохраняем запись с пустым статусом (status_id = null)
            // - если комментария нет — удаляем запись
            $attendance = AttendanceDay::where('user_id', $request->user_id)
                ->where('date', $request->date)
                ->first();

            if ($attendance) {
                if ($request->comment) {
                    $attendance->status_id = null;
                    $attendance->comment = $request->comment;
                    $attendance->save();
                } else {
                    // Если нет комментария и нет статуса — удаляем запись
                    $attendance->delete();
                }
            } else {
                if ($request->comment) {
                    // Создаём запись только с комментарием и без статуса
                    AttendanceDay::create([
                        'user_id' => $request->user_id,
                        'date' => $request->date,
                        'status_id' => null,
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
        if (! auth()->user()->isAdmin()) {
            return redirect()->route('attendance.index')->with('error', 'Доступ запрещён');
        }

        // Получаем все табели со статусом 'submitted'
        $reports = SalaryReport::with('user')
            ->where('status', 'submitted')
            ->orderByDesc('month')
            ->get();

        return view('admin.attendance.approvals', compact('reports'));
    }

    public function advance()
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->route('attendance.index')->with('error', 'Доступ запрещён');
        }

        // Получаем все табели со статусом 'payable'
        $reports = SalaryReport::with('user')
            ->where('status', 'advance_paid')
            ->orderByDesc('month')
            ->get();

        // Данные для модального окна аванса
        $salaryCategories = \App\Models\ExpenseCategory::where('is_salary', true)
            ->where('is_office', false)
            ->orderBy('sort_order')
            ->get();
        $paymentMethods = \App\Models\PaymentMethod::orderBy('sort_order')->get();
        $bankAccounts = \App\Models\BankAccount::orderBy('title')->get();

        return view('admin.attendance.advance', compact('reports', 'salaryCategories', 'paymentMethods', 'bankAccounts'));
    }

    public function payable()
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->route('attendance.index')->with('error', 'Доступ запрещён');
        }

        // Получаем все табели со статусом 'payable'
        $reports = SalaryReport::with('user')
            ->where('status', 'approved')
            ->orderByDesc('month')
            ->get();

        // Данные для модального окна аванса
        $salaryCategories = \App\Models\ExpenseCategory::where('is_salary', true)
            ->where('is_office', false)
            ->orderBy('sort_order')
            ->get();
        $paymentMethods = \App\Models\PaymentMethod::orderBy('sort_order')->get();
        $bankAccounts = \App\Models\BankAccount::orderBy('title')->get();

        return view('admin.attendance.payable', compact('reports', 'salaryCategories', 'paymentMethods', 'bankAccounts'));
    }

    public function paid()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            // Админ видит все оплаченные табеля
            $reports = SalaryReport::with(['user', 'projectBonuses.project'])
                ->where('status', 'paid')
                ->orderByDesc('month')
                ->get();
        } elseif ($user->isMarketer()) {
            // Маркетолог видит только свои оплаченные табеля
            $reports = SalaryReport::with(['user', 'projectBonuses.project'])
                ->where('status', 'paid')
                ->where('user_id', $user->id)
                ->orderByDesc('month')
                ->get();
        } else {
            // Остальные роли — доступ запрещён
            return redirect()->route('attendance.index')->with('error', 'Доступ запрещён');
        }

        return view('admin.attendance.paid', compact('reports'));
    }

    public function rejected()
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->route('attendance.index')->with('error', 'Доступ запрещён');
        }

        // Получаем все табели со статусом 'payable'
        $reports = SalaryReport::with('user')
            ->where('status', 'rejected')
            ->orderByDesc('month')
            ->get();

        return view('admin.attendance.rejected', compact('reports'));
    }

    public function update(Request $request, SalaryReport $report)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->route('attendance.index')->with('error', 'Доступ запрещён');
        }
        $request->validate([
            'ordinary_days' => 'required|numeric|min:0',
            'remote_days' => 'required|numeric|min:0',
            'audits_count' => 'nullable|numeric|min:0',
            'individual_bonus' => 'nullable|numeric|min:0',
            'custom_bonus' => 'nullable|numeric|min:0',
            'fees' => 'nullable|numeric',
            'penalties' => 'nullable|numeric',
            'total_salary' => 'required|numeric|min:0',
            'comment' => 'nullable|string|max:255',
            'project_bonuses' => 'nullable|array',
        ]);
        $data = [
            'ordinary_days' => $request->input('ordinary_days'),
            'remote_days' => $request->input('remote_days'),
            'audits_count' => $request->input('audits_count') ?? 0,
            'individual_bonus' => $request->input('individual_bonus') ?? 0,
            'custom_bonus' => $request->input('custom_bonus') ?? 0,
            'fees' => $request->input('fees') ?? 0,
            'penalties' => $request->input('penalties') ?? 0,
            'total_salary' => $request->input('total_salary'),
            'comment' => $request->input('comment') ?? $report->comment,
            'updated_by' => auth()->id(),
        ];

        // Если табель был отклонён — переводим в статус "submitted" при повторной отправке
        $wasRejected = $report->status === 'rejected';
        if ($wasRejected) {
            $data['status'] = 'submitted';
            $data['submitted_at'] = now();
        }

        $report->fill($data)->save();

        // Обновляем детализацию по проектам
        if ($request->has('project_bonuses')) {
            $report->projectBonuses()->delete();

            foreach ($request->project_bonuses as $projectId => $data) {
                $report->projectBonuses()->create([
                    'project_id' => $projectId,
                    'contract_amount' => $data['contract_amount'] ?? 0,
                    'bonus_percent' => $data['bonus_percent'] ?? 0,
                    'max_bonus' => $data['max_bonus'] ?? 0,
                    'days_worked' => $data['days_worked'] ?? 0,
                    'bonus_amount' => $data['bonus_amount'] ?? 0,
                ]);
            }
        }

        // Перенаправляем на список отклонённых, если табель был повторно отправлен
        if ($wasRejected) {
            return redirect()->route('attendance.rejected')->with('success', 'Табель успешно отправлен на повторное согласование');
        }

        return redirect()->back()->with('success', 'Табель успешно обновлен');
    }

    public function show(SalaryReport $report)
    {
        $report->load('projectBonuses.project');

        return view('admin.attendance.show', compact('report'));
    }

    // Сохраняем/обновляем только комментарий табеля
    public function updateComment(Request $request, SalaryReport $report)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->route('attendance.index')->with('error', 'Доступ запрещён');
        }

        $request->validate([
            'comment' => 'nullable|string|max:255',
        ]);

        $report->update([
            'comment' => $request->comment,
            'updated_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Комментарий сохранён');
    }

    public function approve(SalaryReport $report)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->route('attendance.index')->with('error', 'Доступ запрещён');
        }

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
        if (! auth()->user()->isAdmin()) {
            return redirect()->route('attendance.index')->with('error', 'Доступ запрещён');
        }

        $report->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Табель отклонён');
    }

    public function paidUpdate(SalaryReport $report)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->route('attendance.index')->with('error', 'Доступ запрещён');
        }

        $report->update([
            'status' => 'paid',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Табель успешно оплачен');
    }

    /**
     * Страница редактирования отклонённого табеля
     * Загружает данные из БД без лишних расчётов
     */
    public function rejectedUserShow(SalaryReport $report)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->route('attendance.index')->with('error', 'Доступ запрещён');
        }
        // Загружаем связанные данные
        $report->load(['user.specialty', 'projectBonuses.project']);

        $user = $report->user;
        $month = Carbon::parse($report->month);

        return view('admin.attendance.rejectedUserShow', compact('report', 'user', 'month'));
    }

    public function userShow(User $user)
    {
        // Только admin может просматривать табели других пользователей
        if (! auth()->user()->isAdmin() && auth()->id() !== $user->id) {
            return redirect()->route('attendance.index')->with('error', 'Доступ запрещён');
        }

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
        $projects = $user->projects()
            ->where(function ($q) use ($monthStart) {
                $q->whereNull('closed_at')
                    ->orWhere('closed_at', '>=', $monthStart);
            })
            ->get();
        $totalContractAmount = $projects->sum('contract_amount');
        $projectsCount = $projects->count();
        $auditsCount = 0;
        $auditPrice = 300;
        $baseSalary = $user->salary_override ?? ($user->specialty->salary ?? 0);
        $individualBonusPercent = $user->individual_bonus_percent ?? 5;

        // Получаем историю работы маркетолога над проектами за прошлый месяц
        // Учитываем только дни, когда сотрудник реально работал (по attendance_days)
        $projectDaysData = [];

        // Создаём словарь: дата => коэффициент дня (1 для work, 0.5 для remote/short, 0 для остальных)
        $workDayCoefficients = [];
        foreach ($attendanceDays as $day) {
            $dateKey = $day->date->format('Y-m-d');
            $code = $day->status->code ?? null;

            if ($code === 'work') {
                $workDayCoefficients[$dateKey] = 1;
            } elseif (in_array($code, ['remote', 'short'])) {
                $workDayCoefficients[$dateKey] = 0.5;
            }
            // absent или null — не добавляем (коэффициент = 0)
        }

        foreach ($projects as $project) {
            // Получаем все записи истории по этому проекту и пользователю
            $historyRecords = \App\Models\ProjectMarketerHistory::where('project_id', $project->id)
                ->where('user_id', $user->id)
                ->where(function ($q) use ($monthStart, $monthEnd) {
                    // Записи, которые пересекаются с нашим месяцем
                    $q->where('assigned_at', '<=', $monthEnd)
                        ->where(function ($q2) use ($monthStart) {
                            $q2->whereNull('unassigned_at')
                                ->orWhere('unassigned_at', '>=', $monthStart);
                        });
                })
                ->get();

            // Считаем суммарное количество рабочих дней с учётом статусов посещаемости
            $totalDays = 0;
            foreach ($historyRecords as $record) {
                // Период назначения на проект (ограниченный текущим месяцем)
                $recordStart = $record->assigned_at->max($monthStart);
                $recordEnd = ($record->unassigned_at ?? $monthEnd)->min($monthEnd);

                // Проходим по каждому дню периода и проверяем, работал ли сотрудник
                $currentDate = $recordStart->copy();
                while ($currentDate->lte($recordEnd)) {
                    $dateKey = $currentDate->format('Y-m-d');
                    // Добавляем коэффициент дня (если есть в словаре)
                    $totalDays += $workDayCoefficients[$dateKey] ?? 0;
                    $currentDate->addDay();
                }
            }

            $projectDaysData[$project->id] = $totalDays;
        }

        // Рассчитываем бонусы по проектам для передачи во view и сохранения
        $projectBonusesData = [];
        $avgWorkDays = 22;
        $calculatedTotalBonus = 0;

        foreach ($projects as $project) {
            $contractAmount = $project->contract_amount ?? 0;
            $maxBonus = $contractAmount * ($individualBonusPercent / 100);
            $bonusPerDay = $avgWorkDays > 0 ? $maxBonus / $avgWorkDays : 0;
            $daysWorked = $projectDaysData[$project->id] ?? 0;
            $bonusAmount = $bonusPerDay * $daysWorked;
            $calculatedTotalBonus += $bonusAmount;

            $projectBonusesData[$project->id] = [
                'contract_amount' => $contractAmount,
                'bonus_percent' => $individualBonusPercent,
                'max_bonus' => $maxBonus,
                'days_worked' => $daysWorked,
                'bonus_amount' => $bonusAmount,
            ];
        }

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
            'projects',
            'projectDaysData',
            'projectBonusesData',
            'calculatedTotalBonus',
            'individualBonusPercent',
            'auditsCount',
            'auditPrice',
            'baseSalary',
            'existingReport'
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
            'fees' => 'nullable|numeric',
            'penalties' => 'nullable|numeric',
            'total_salary' => 'required|numeric|min:0',
            'status' => 'nullable|in:draft,submitted,approved,rejected',
            'comment' => 'nullable|string|max:255',
            'project_bonuses' => 'nullable|array',
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
                'fees' => $request->fees ?? 0,
                'penalties' => $request->penalties ?? 0,
                'total_salary' => $request->total_salary,
                'status' => $request->status ?? 'submitted',
                'comment' => $request->comment,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]
        );

        // Сохраняем детализацию по проектам
        if ($request->has('project_bonuses')) {
            // Удаляем старые записи
            $salaryReport->projectBonuses()->delete();

            // Создаём новые
            foreach ($request->project_bonuses as $projectId => $data) {
                $salaryReport->projectBonuses()->create([
                    'project_id' => $projectId,
                    'contract_amount' => $data['contract_amount'] ?? 0,
                    'bonus_percent' => $data['bonus_percent'] ?? 0,
                    'max_bonus' => $data['max_bonus'] ?? 0,
                    'days_worked' => $data['days_worked'] ?? 0,
                    'bonus_amount' => $data['bonus_amount'] ?? 0,
                ]);
            }
        }

        // Перенаправляем на страницу просмотра созданного/обновлённого табеля
        return redirect()->route('attendance.show', $salaryReport->id)->with('success', 'Табель сохранён!');
    }
}
