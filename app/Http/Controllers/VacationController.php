<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Models\Vacation;
use App\Models\VacationProject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VacationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']); // или другой подходящий middleware
    }

    public function index(): View
    {
        $vacations = Vacation::with(['user', 'tempMarketer'])->orderByDesc('start_date')->paginate(20);

        return view('admin.vacations.index', compact('vacations'));
    }

    public function create(): View
    {
        $users = User::orderBy('name')->pluck('name', 'id');
        $managers = User::where('role', 'manager')->orderBy('name')->pluck('name', 'id');

        return view('admin.vacations.create', compact('users', 'managers'));
    }

    public function store(Request $request): RedirectResponse
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

        // Если временный маркетолог не указан — рандомный доступный менеджер
        $tempMarketerId = $data['temp_marketer_id'] ?? null;
        if (empty($tempMarketerId)) {
            $temp = User::where('role', 'manager')
                ->where('id', '<>', $user->id)
                ->whereDoesntHave('vacations', function ($q) use ($start, $end) {
                    $q->where('active', true)
                        ->where('start_date', '<=', $end)
                        ->where('end_date', '>=', $start);
                })
                ->inRandomOrder()
                ->first();
            $tempMarketerId = $temp?->id ?? null;
        }

        $reassignedCount = 0;
        $note = null;

        DB::transaction(function () use ($user, $start, $end, $tempMarketerId, &$reassignedCount, &$note, $data) {
            $vac = Vacation::create([
                'user_id' => $user->id,
                'start_date' => $start,
                'end_date' => $end,
                'temp_marketer_id' => $tempMarketerId,
                'active' => true,
                'notes' => $data['notes'] ?? null,
            ]);

            // Найдём проекты текущего маркетолога, активные в период отпуска
            $projects = Project::where('marketer_id', $user->id)
                ->where(function ($q) use ($start) {
                    $q->whereNull('closed_at')->orWhere('closed_at', '>=', $start);
                })
                ->where('contract_date', '<=', $end)
                ->get();

            if ($projects->isNotEmpty()) {
                foreach ($projects as $p) {
                    VacationProject::create([
                        'vacation_id' => $vac->id,
                        'project_id' => $p->id,
                        'original_marketer_id' => $p->marketer_id,
                        'reassigned_to_id' => $tempMarketerId,
                    ]);
                    // назначаем временного маркетолога (или NULL, если не найден)
                    $p->update(['marketer_id' => $tempMarketerId]);
                    $reassignedCount++;
                }
                $note = $tempMarketerId ? "Назначен временный маркетолог (ID: {$tempMarketerId})." : 'Проекты оставлены без маркетолога.';
            }
        });

        $msg = 'Отпуск создан.';
        if ($reassignedCount) {
            $msg .= " Переназначено проектов: {$reassignedCount}.";
        } elseif ($note) {
            $msg .= " {$note}";
        }

        return redirect()->route('vacations.index')->with('success', $msg);
    }

    public function show(Vacation $vacation): View
    {
        $vacation->load('user', 'tempMarketer', 'projects.project');

        return view('admin.vacations.show', compact('vacation'));
    }

    // Завершить отпуск — восстановить оригинальных маркетологов
    public function end(Vacation $vacation): RedirectResponse
    {
        DB::transaction(function () use ($vacation) {
            foreach ($vacation->projects as $vp) {
                $project = Project::find($vp->project_id);
                if ($project) {
                    $project->update(['marketer_id' => $vp->original_marketer_id]);
                }
            }
            $vacation->update(['active' => false]);
        });

        return redirect()->route('vacations.index')->with('success', 'Отпуск завершён и проекты восстановлены.');
    }

    // Доп. методы edit/update/destroy по необходимости...
}
