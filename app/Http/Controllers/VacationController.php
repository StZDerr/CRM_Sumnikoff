<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Models\Vacation;
use App\Models\VacationProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VacationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'temp_marketer_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string|max:2000',
        ]);

        $user = User::findOrFail($data['user_id']);
        $start = $data['start_date'];
        $end = $data['end_date'];

        // Проверка на перекрытие активного отпуска
        $overlap = Vacation::where('user_id', $user->id)
            ->where('active', true)
            ->where('start_date', '<=', $end)
            ->where('end_date', '>=', $start)
            ->exists();

        if ($overlap) {
            $errors = ['user_id' => ['У пользователя уже есть активный отпуск в указанный период.']];
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['errors' => $errors], 422);
            }

            return redirect()->back()->withErrors($errors)->withInput();
        }

        // Если временный маркетолог не указан — выбираем случайного менеджера (кроме самого уезжающего)
        $tempMarketerId = $data['temp_marketer_id'] ?? null;
        if (empty($tempMarketerId)) {
            $temp = User::where('role', 'manager')
                ->where('id', '<>', $user->id)
                ->inRandomOrder()
                ->first();
            $tempMarketerId = $temp?->id ?? null;
        }

        $reassignedCount = 0;
        $note = null;
        $vac = null;

        DB::transaction(function () use ($user, $start, $end, $tempMarketerId, &$reassignedCount, &$note, $data, &$vac) {
            $vac = Vacation::create([
                'user_id' => $user->id,
                'start_date' => $start,
                'end_date' => $end,
                'temp_marketer_id' => $tempMarketerId,
                'active' => true,
                'notes' => $data['notes'] ?? null,
            ]);

            // Найдём проекты маркетолога, активные в период отпуска
            $projects = Project::where('marketer_id', $user->id)
                ->where(function ($q) use ($start) {
                    $q->whereNull('closed_at')->orWhere('closed_at', '>=', $start);
                })
                ->where('contract_date', '<=', $end)
                ->get();

            if ($projects->isNotEmpty()) {
                foreach ($projects as $p) {
                    // Не создаём повторные активные VacationProject для одного проекта
                    $exists = VacationProject::where('project_id', $p->id)->whereNull('restored_at')->exists();
                    if ($exists) {
                        continue;
                    }

                    // Выбираем случайного маркетолога для этого проекта (исключая уезжающего)
                    // Сначала пробуем найти менеджера, связанного с той же организацией
                    $assignee = null;
                    if (! empty($p->organization_id)) {
                        $assignee = User::where('role', 'manager')
                            ->where('id', '<>', $user->id)
                            ->whereHas('projects', function ($q) use ($p) {
                                $q->where('organization_id', $p->organization_id);
                            })
                            ->inRandomOrder()
                            ->first();
                    }

                    // Фоллбек: любой менеджер (except the user)
                    if (! $assignee) {
                        $assignee = User::where('role', 'manager')
                            ->where('id', '<>', $user->id)
                            ->inRandomOrder()
                            ->first();
                    }

                    $assigneeId = $assignee?->id ?? null;

                    VacationProject::create([
                        'vacation_id' => $vac->id,
                        'project_id' => $p->id,
                        'original_marketer_id' => $p->marketer_id,
                        'reassigned_to_id' => $assigneeId,
                        'reassigned_at' => now(),
                    ]);

                    // назначаем временного маркетолога (или NULL, если не найден)
                    $p->update(['marketer_id' => $assigneeId]);
                    $reassignedCount++;
                }
                $note = $tempMarketerId ? 'Назначен(ы) временные маркетолог(и).' : 'Проекты оставлены без маркетолога (если кандидаты не найдены).';
            }
        });

        $msg = 'Отпуск создан.';
        if ($reassignedCount) {
            $msg .= " Переназначено проектов: {$reassignedCount}.";
        } elseif ($note) {
            $msg .= " {$note}";
        }

        if ($request->ajax() || $request->wantsJson()) {
            // Обновлённый список отпусков пользователя
            $vacations = $user->vacations()->orderByDesc('start_date')->get();
            $html = view('admin.users._vacations_list', compact('vacations', 'user'))->render();

            return response()->json([
                'message' => $msg,
                'html' => $html,
            ]);
        }

        return redirect()->route('users.index')->with('success', $msg);
    }

    /**
     * Возвращает HTML partial со списком отпусков пользователя (для offcanvas)
     */
    public function userVacations(User $user)
    {
        $vacations = $user->vacations()->orderByDesc('start_date')->get();

        return view('admin.users._vacations_list', compact('vacations', 'user'));
    }

    // Завершить отпуск — восстановить оригинальных маркетологов
    public function end(Request $request, Vacation $vacation)
    {
        DB::transaction(function () use ($vacation) {
            foreach ($vacation->projects as $vp) {
                // пропускаем уже восстановленные
                if ($vp->restored_at) {
                    continue;
                }

                $project = Project::find($vp->project_id);
                if (! $project) {
                    $vp->update(['restored_at' => now(), 'restored_by' => auth()->id()]);

                    continue;
                }

                // восстанавливаем только если текущий маркетолог — тот, кого мы назначили ранее
                if ($project->marketer_id == $vp->reassigned_to_id) {
                    $project->update(['marketer_id' => $vp->original_marketer_id]);
                }

                $vp->update([
                    'restored_at' => now(),
                    'restored_by' => auth()->id(),
                ]);
            }

            $vacation->update([
                'active' => false,
                'ended_at' => now(),
            ]);
        });

        if ($request->ajax() || $request->wantsJson()) {
            // вернуть обновлённый список отпусков для владельца
            $user = $vacation->user()->first();
            $vacations = $user->vacations()->orderByDesc('start_date')->get();
            $html = view('admin.users._vacations_list', compact('vacations', 'user'))->render();

            return response()->json(['message' => 'Отпуск завершён и проекты восстановлены.', 'html' => $html]);
        }

        return redirect()->route('users.index')->with('success', 'Отпуск завершён и проекты восстановлены.');
    }
}
