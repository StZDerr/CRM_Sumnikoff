<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectLawyer;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectLawyerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Отправка проекта — только admin / project_manager
        $this->middleware('role:admin,project_manager')->only(['store']);
        // Список — admin и lawyer
        $this->middleware('role:admin,lawyer')->only(['index']);
    }

    // Список проектов для юриста (админ видит все проекты всех юристов)
    public function index()
    {
        // Админ видит все назначения, юрист — только свои pending
        if (auth()->user()->isAdmin()) {
            $projects = ProjectLawyer::with('project', 'lawyer', 'sender')
                ->orderByDesc('sent_at')
                ->get();
        } else {
            // Показываем ВСЕ назначения текущего юриста — закрытые проекты будут в конце
            $projects = ProjectLawyer::with('project', 'sender')
                ->where('user_id', auth()->id())
                ->orderByRaw("CASE WHEN status = 'closed' THEN 1 ELSE 0 END ASC")
                ->orderByDesc('sent_at')
                ->get();
        }

        return view('admin.lawyer.projects.index', compact('projects'));
    }

    // Просмотр проекта юристом (подробная страница)
    public function show(ProjectLawyer $projectLawyer)
    {
        // Разрешаем только назначенному юристу или админу
        if ($projectLawyer->user_id !== auth()->id() && ! auth()->user()->isAdmin()) {
            abort(403);
        }

        $projectLawyer->load('project.organization', 'project.marketer');

        $project = $projectLawyer->project;
        // Загружаем только комментарии юриста (project_lawyer_comments)
        $project->load(['lawyerComments.user']);

        $comments = $project->lawyerComments->map(function ($c) {
            return (object) [
                'id' => $c->id,
                'type' => 'lawyer',
                'user' => $c->user,
                'role' => $c->user?->role,
                'body' => $c->comment,
                'file_path' => $c->file_path, // legacy single file
                'files' => $c->files ?? collect(), // new multiple files relation
                'created_at' => $c->created_at,
            ];
        })->sortBy('created_at')->values();

        return view('admin.lawyer.projects.show', compact('projectLawyer', 'project', 'comments'));
    }

    // Просмотр ограниченной информации о проекте для юриста
    public function project(ProjectLawyer $projectLawyer)
    {
        if ($projectLawyer->user_id !== auth()->id() && ! auth()->user()->isAdmin()) {
            abort(403);
        }

        // Загружаем все необходимые связи, как в admin.projects.show
        $project = $projectLawyer->project()->with([
            'organization',
            'marketer',
            'invoices',
            'payments',
            'importance',
            'paymentMethod',
            'stages',
            'domains',
            'comments.user',
            'comments.photos',
        ])->first();

        return view('admin.lawyer.projects.project', compact('project', 'projectLawyer'));
    }

    // Просмотр ограниченной информации об организации для юриста
    public function organization(ProjectLawyer $projectLawyer)
    {
        if ($projectLawyer->user_id !== auth()->id() && ! auth()->user()->isAdmin()) {
            abort(403);
        }

        $organization = $projectLawyer->project->organization;

        // Подготовим контакты для страницы организации (как в admin view)
        $contacts = $organization->contacts()->orderBy('last_name')->paginate(10);

        return view('admin.lawyer.organizations.show', compact('organization', 'projectLawyer', 'contacts'));
    }

    // Отправка проекта юристу
    public function store(Request $request, Project $project)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'note' => 'nullable|string',
        ]);

        $lawyer = User::findOrFail($request->user_id);

        if ($lawyer->role !== 'lawyer') {
            return back()->withErrors('Выбранный пользователь не является юристом');
        }

        // Создаём или обновляем запись
        ProjectLawyer::updateOrCreate(
            ['project_id' => $project->id, 'user_id' => $lawyer->id],
            [
                'sent_by' => auth()->id(),
                'sent_at' => now(),
                'status' => 'pending',
                'note' => $request->note,
            ]
        );

        return back()->with('success', 'Проект отправлен юристу');
    }

    // Обновление статуса проекта для юриста (mark processed)
    public function update(Request $request, ProjectLawyer $projectLawyer)
    {
        // Разрешаем обновлять назначение: назначенному юристу, админу или проектному менеджеру
        $user = auth()->user();
        if (! ($user->isAdmin() || $user->isProjectManager() || $projectLawyer->user_id === $user->id)) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:pending,processed,closed',
        ]);

        $projectLawyer->update([
            'status' => $request->status,
        ]);

        return back()->with('success', 'Статус обновлен');
    }
}
