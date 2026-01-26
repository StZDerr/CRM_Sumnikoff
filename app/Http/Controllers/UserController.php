<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Project;
use App\Models\ProjectMarketerHistory;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        // только admin
        $this->middleware('admin')->except(['index', 'show']);

        // index + show: admin + PM
        $this->middleware('role:admin,project_manager')->only(['index', 'show']);
    }

    public function index(Request $request): View
    {
        // Группируем список: сначала по роли, затем по алфавиту внутри роли
        $usersAll = User::with('activeVacation')
            ->orderBy('role')
            ->orderBy('name')
            ->get();

        // groupedUsers: ['admin' => Collection, 'marketer' => Collection, ...]
        $groupedUsers = $usersAll->groupBy('role');

        // Для формы отпусков и других нужд
        $marketers = User::where('role', 'manager')->orderBy('name')->pluck('name', 'id');

        // keep paginated collection for backwards compatibility (if needed)
        $users = User::with('activeVacation')->orderBy('id', 'desc')->paginate(15);

        return view('admin.users.index', compact('groupedUsers', 'marketers', 'users'));
    }

    public function create(): View
    {
        $specialties = Specialty::where('active', true)
            ->orderBy('salary')
            ->get();

        return view('admin.users.create', compact('specialties'));
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $data['password'] = Hash::make($data['password']);

        // если не начальник — обнуляем индивидуальный оклад
        if (empty($data['is_department_head'])) {
            $data['salary_override'] = null;
            $data['is_department_head'] = false;
        }

        // Индивидуальная премия по умолчанию 5%
        $data['individual_bonus_percent'] = $data['individual_bonus_percent'] ?? 5;

        // Создаем пользователя
        $user = User::create($data);

        // ==== СОЦСЕТИ ====
        // Если пришли соцсети
        if ($request->has('socials')) {
            foreach ($request->input('socials') as $social) {
                // Проверяем, что есть хотя бы платформа и ссылка
                if (! empty($social['platform']) && ! empty($social['url'])) {
                    // Для Telegram автоматически формируем ссылку, поддерживаем ввод в формате @nick, nick, t.me/nick или полного URL
                    $raw = trim($social['url']);
                    if ($social['platform'] === 'telegram') {
                        // если ввели ссылку вида t.me/..., добавим схему для корректного парсинга
                        if (str_contains($raw, 't.me')) {
                            if (! str_starts_with($raw, 'http')) {
                                $raw = 'https://'.$raw;
                            }
                            $parts = parse_url($raw);
                            $path = $parts['path'] ?? '';
                            $username = trim($path, "/@ \t\n\r\0\x0B");
                        } else {
                            $username = trim($raw, "/@ \t\n\r\0\x0B");
                        }

                        if ($username === '') {
                            // некорректный ввод — пропустим
                            continue;
                        }

                        $url = 'https://t.me/'.$username;
                    } else {
                        $url = $raw;
                    }

                    $user->socials()->create([
                        'platform' => $social['platform'],
                        'url' => $url,
                    ]);
                }
            }
        }

        return redirect()->route('users.index')->with('success', 'Пользователь создан');
    }

    public function show(User $user): View
    {
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        $specialties = Specialty::where('active', true)
            ->orderBy('name')
            ->get();

        return view('admin.users.edit', compact('user', 'specialties'));
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        // Хэшируем пароль, если он введён
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        // Если не начальник — обнуляем индивидуальный оклад
        if (empty($data['is_department_head'])) {
            $data['salary_override'] = null;
            $data['is_department_head'] = false;
        }

        // Индивидуальная премия по умолчанию 5%
        $data['individual_bonus_percent'] = $data['individual_bonus_percent'] ?? 5;

        // Обновляем данные пользователя
        $user->update($data);

        // ==== СОЦСЕТИ ====
        // Удаляем старые соцсети и создаем новые
        $user->socials()->delete();

        if ($request->has('socials')) {
            foreach ($request->input('socials') as $social) {
                if (! empty($social['platform']) && ! empty($social['url'])) {
                    // Автоматическая ссылка для Telegram (с поддержкой полного URL / @nick / nick)
                    $raw = trim($social['url']);
                    if ($social['platform'] === 'telegram') {
                        if (str_contains($raw, 't.me')) {
                            if (! str_starts_with($raw, 'http')) {
                                $raw = 'https://'.$raw;
                            }
                            $parts = parse_url($raw);
                            $path = $parts['path'] ?? '';
                            $username = trim($path, "/@ \t\n\r\0\x0B");
                        } else {
                            $username = trim($raw, "/@ \t\n\r\0\x0B");
                        }

                        if ($username === '') {
                            continue;
                        }

                        $url = 'https://t.me/'.$username;
                    } else {
                        $url = $raw;
                    }

                    $user->socials()->create([
                        'platform' => $social['platform'],
                        'url' => $url,
                    ]);
                }
            }
        }

        return redirect()->route('users.index')->with('success', 'Пользователь обновлён');
    }

    public function destroy(User $user): RedirectResponse
    {
        // Запретить удаление самого себя
        if (auth()->id() === $user->id) {
            return redirect()->route('users.index')
                ->with('error', 'Вы не можете удалить свой аккаунт');
        }

        DB::transaction(function () use ($user, &$reassignedCount, &$note) {
            // Найдём проекты, привязанные к удаляемому пользователю
            $projects = Project::where('marketer_id', $user->id)->get();

            $reassignedCount = 0;
            $note = null;

            if ($projects->isNotEmpty()) {
                // Получим доступных маркетологов (role = manager) кроме удаляемого
                $marketers = User::where('role', 'manager')
                    ->where('id', '<>', $user->id)
                    ->withCount('projects') // для балансировки
                    ->orderBy('projects_count', 'asc')
                    ->get()
                    ->values();

                if ($marketers->isEmpty()) {
                    // Нет маркетологов — обнулим поле marketer_id
                    foreach ($projects as $p) {
                        $p->update(['marketer_id' => null]);
                    }
                    $note = 'Проекты оставлены без маркетолога (нет доступных маркетологов).';
                } else {
                    // Распределим проекты по маркетологам в порядке минимальной загруженности
                    $idx = 0;
                    $mCount = $marketers->count();
                    // локальная карта загрузки (чтобы не ре-запрашивать каждый раз)
                    $loads = $marketers->pluck('projects_count')->toArray();

                    foreach ($projects as $p) {
                        // найдём индекс маркетолога с минимальной загрузкой
                        $minIdx = array_keys($loads, min($loads))[0];
                        $assignee = $marketers[$minIdx];
                        $p->update(['marketer_id' => $assignee->id]);
                        // увеличим локальную загрузку
                        $loads[$minIdx]++;
                        $reassignedCount++;
                    }

                    $note = "Проекты перераспределены между {$mCount} маркетологами.";
                }
            }

            // Удаляем пользователя (soft или hard в зависимости от модели)
            $user->delete();
        });

        $msg = 'Пользователь удалён.';
        if (! empty($reassignedCount)) {
            $msg .= " Переназначено проектов: {$reassignedCount}.";
        } elseif (! empty($note)) {
            $msg .= ' '.$note;
        }

        return redirect()->route('users.index')->with('success', $msg);
    }

    public function userDashboard(User $user, Request $request): View
    {
        // month in format YYYY-MM (optional)
        $monthParam = $request->query('month');
        $month = $monthParam ? \Carbon\Carbon::createFromFormat('Y-m', $monthParam) : \Carbon\Carbon::now();
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        // Count distinct projects where user was marketer during the month (overlapping assignments)
        $projectsCount = ProjectMarketerHistory::where('user_id', $user->id)
            ->where('assigned_at', '<=', $end)
            ->where(function ($q) use ($start) {
                $q->whereNull('unassigned_at')->orWhere('unassigned_at', '>=', $start);
            })
            ->distinct('project_id')
            ->count('project_id');

        // Count paid projects (payment_type = 'paid') within same period using history
        $paidProjectsCount = ProjectMarketerHistory::where('user_id', $user->id)
            ->where('assigned_at', '<=', $end)
            ->where(function ($q) use ($start) {
                $q->whereNull('unassigned_at')->orWhere('unassigned_at', '>=', $start);
            })
            ->whereHas('project', function ($q) {
                $q->where('payment_type', 'paid');
            })
            ->distinct('project_id')
            ->count('project_id');

        $ownProjectsCount = ProjectMarketerHistory::where('user_id', $user->id)
            ->where('assigned_at', '<=', $end)
            ->where(function ($q) use ($start) {
                $q->whereNull('unassigned_at')->orWhere('unassigned_at', '>=', $start);
            })
            ->whereHas('project', function ($q) {
                $q->where('payment_type', 'own');
            })
            ->distinct('project_id')
            ->count('project_id');

        $barterProjectsCount = ProjectMarketerHistory::where('user_id', $user->id)
            ->where('assigned_at', '<=', $end)
            ->where(function ($q) use ($start) {
                $q->whereNull('unassigned_at')->orWhere('unassigned_at', '>=', $start);
            })
            ->whereHas('project', function ($q) {
                $q->where('payment_type', 'barter');
            })
            ->distinct('project_id')
            ->count('project_id');

        // Считаем Ожидаемую прибыль за месяц
        $expectedProfit = ProjectMarketerHistory::where('user_id', $user->id)
            ->where('assigned_at', '<=', $end)
            ->where(function ($q) use ($start) {
                $q->whereNull('unassigned_at')
                    ->orWhere('unassigned_at', '>=', $start);
            })
            ->whereHas('project', function ($q) use ($month) {
                $q->expectedProfitForMonth($month)
                    ->whereNotIn('payment_type', ['barter', 'own']);
            })
            ->with(['project' => function ($q) use ($month) {
                $q->expectedProfitForMonth($month)
                    ->whereNotIn('payment_type', ['barter', 'own']);
            }])
            ->get()
            ->sum(fn ($history) => (float) ($history->project->contract_amount ?? 0));

        // Сумма поступлений по проектам, но только в период, когда маркетолог был на проекте
        $histories = ProjectMarketerHistory::where('user_id', $user->id)
            ->where('assigned_at', '<=', $end)
            ->where(function ($q) use ($start) {
                $q->whereNull('unassigned_at')->orWhere('unassigned_at', '>=', $start);
            })
            // исключаем бартерные и "свои" проекты
            ->whereHas('project', function ($q) {
                $q->whereNull('payment_type')->orWhereNotIn('payment_type', ['barter', 'own']);
            })
            ->with('project')
            ->get();

        $moneyReceived = 0;
        foreach ($histories as $h) {
            // период присутствия маркетолога на проекте внутри выбранного месяца
            $from = $h->assigned_at->copy()->max($start);
            $to = ($h->unassigned_at ? $h->unassigned_at->copy() : $end)->min($end);

            if ($from->gt($to)) {
                continue;
            }

            $sum = \App\Models\Payment::where('project_id', $h->project_id)
                ->whereRaw('DATE(COALESCE(payment_date, payments.created_at)) between ? and ?', [$from->toDateString(), $to->toDateString()])
                ->sum('amount');

            $moneyReceived += $sum;
        }

        // 1. Активные проекты на конец месяца (вариант Б):
        // - проект не закрыт (closed_at IS NULL)
        // - пользователь назначен на конец месяца (assigned_at <= end AND (unassigned_at IS NULL OR unassigned_at > end))
        $activeProjectsCount = ProjectMarketerHistory::where('user_id', $user->id)
            ->where('assigned_at', '<=', $end)
            ->where(function ($q) use ($end) {
                $q->whereNull('unassigned_at')
                    ->orWhere('unassigned_at', '>', $end);
            })
            ->whereHas('project', function ($q) {
                $q->whereNull('closed_at');
            })
            ->distinct('project_id')
            ->count('project_id');

        // 2. Закрыто за месяц (вариант Б):
        // - проект закрыт в выбранном месяце (closed_at between start and end)
        // - пользователь был назначен в момент закрытия (assigned_at <= closed_at AND (unassigned_at IS NULL OR unassigned_at >= closed_at))
        $closedProjectsCount = ProjectMarketerHistory::where('user_id', $user->id)
            ->whereHas('project', function ($q) use ($start, $end) {
                $q->whereBetween('closed_at', [$start, $end]);
            })
            ->whereHas('project', function ($q) {
                // дополнительная фильтрация: пользователь был назначен в момент закрытия
            })
            ->with('project')
            ->get()
            ->filter(function ($history) {
                $closedAt = $history->project->closed_at;
                if (! $closedAt) {
                    return false;
                }
                // assigned_at <= closed_at
                if ($history->assigned_at->gt($closedAt)) {
                    return false;
                }
                // unassigned_at IS NULL OR unassigned_at >= closed_at
                if ($history->unassigned_at && $history->unassigned_at->lt($closedAt)) {
                    return false;
                }

                return true;
            })
            ->unique('project_id')
            ->count();

        // 3. Средний чек по поступлениям (amount без налогов)
        // Считаем все Payment, где payment_date попадает в период назначения пользователя на проект
        $paymentsData = collect();
        foreach ($histories as $h) {
            $from = $h->assigned_at->copy()->max($start);
            $to = ($h->unassigned_at ? $h->unassigned_at->copy() : $end)->min($end);

            if ($from->gt($to)) {
                continue;
            }

            $payments = \App\Models\Payment::where('project_id', $h->project_id)
                ->whereRaw('DATE(COALESCE(payment_date, payments.created_at)) between ? and ?', [$from->toDateString(), $to->toDateString()])
                ->get();

            foreach ($payments as $payment) {
                $paymentsData->push($payment);
            }
        }

        $averageCheck = null;
        $paymentsCount = $paymentsData->count();
        if ($paymentsCount > 0) {
            // amount - vat_amount - usn_amount (без налогов)
            $totalNet = $paymentsData->sum(fn ($p) => (float) $p->amount - (float) $p->vat_amount - (float) $p->usn_amount);
            $averageCheck = round($totalNet / $paymentsCount, 2);
        }

        // 4. Накопительный долг клиентов на конец месяца
        // Долг = contract_amount - сумма всех payments до конца месяца
        // Только платные проекты (исключаем barter и own)
        // Пользователь должен был быть назначен на проект до конца месяца
        $debtHistories = ProjectMarketerHistory::where('user_id', $user->id)
            ->where('assigned_at', '<=', $end)
            ->whereHas('project', function ($q) {
                $q->where('payment_type', 'paid');
            })
            ->with('project')
            ->get();

        $clientDebt = 0;
        $processedProjectIds = [];
        foreach ($debtHistories as $h) {
            // Избегаем двойного подсчёта одного проекта
            if (in_array($h->project_id, $processedProjectIds)) {
                continue;
            }
            $processedProjectIds[] = $h->project_id;

            $project = $h->project;
            if (! $project) {
                continue;
            }

            $contractAmount = (float) ($project->contract_amount ?? 0);

            // Сумма всех payments по проекту до конца выбранного месяца (накопительно)
            $totalPaid = \App\Models\Payment::where('project_id', $h->project_id)
                ->whereRaw('DATE(COALESCE(payment_date, payments.created_at)) <= ?', [$end->toDateString()])
                ->sum('amount');

            $projectDebt = $contractAmount - (float) $totalPaid;

            // Если переплата — долг = 0
            if ($projectDebt > 0) {
                $clientDebt += $projectDebt;
            }
        }

        return view('admin.users.userDashboard', compact(
            'user',
            'projectsCount',
            'paidProjectsCount',
            'ownProjectsCount',
            'barterProjectsCount',
            'monthParam',
            'expectedProfit',
            'moneyReceived',
            'activeProjectsCount',
            'closedProjectsCount',
            'averageCheck',
            'paymentsCount',
            'clientDebt'
        ));
    }
}
