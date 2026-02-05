<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Task::class);

        $statuses = TaskStatus::ordered()->get();

        $isLimited = ! ($request->user()->isAdmin() || $request->user()->isProjectManager());

        $query = Task::with(['project', 'status', 'assignee'])
            ->when($isLimited, fn ($q) => $q->forUser($request->user()->id))
            ->when($request->filled('project_id'), fn ($q) => $q->where('project_id', $request->integer('project_id')))
            ->when($request->filled('assignee_id'), fn ($q) => $q->where('assignee_id', $request->integer('assignee_id')))
            ->when($request->filled('status_id'), fn ($q) => $q->where('status_id', $request->integer('status_id')));

        $tasks = $query->orderByDesc('created_at')->paginate(20);

        $deadlineGroups = [
            'overdue' => Task::with(['project', 'status', 'assignee'])->when($isLimited, fn ($q) => $q->forUser($request->user()->id))->overdue()->get(),
            'today' => Task::with(['project', 'status', 'assignee'])->when($isLimited, fn ($q) => $q->forUser($request->user()->id))->dueToday()->get(),
            'week' => Task::with(['project', 'status', 'assignee'])->when($isLimited, fn ($q) => $q->forUser($request->user()->id))->dueWeek()->get(),
            'no_deadline' => Task::with(['project', 'status', 'assignee'])->when($isLimited, fn ($q) => $q->forUser($request->user()->id))->withoutDeadline()->get(),
            'done' => Task::with(['project', 'status', 'assignee'])->when($isLimited, fn ($q) => $q->forUser($request->user()->id))->completed()->get(),
        ];

        $planTasks = Task::with(['project', 'status', 'assignee'])
            ->forUser($request->user()->id)
            ->whereNull('closed_at')
            ->get()
            ->groupBy('status_id');

        return view('admin.tasks.index', compact('tasks', 'statuses', 'deadlineGroups', 'planTasks'));
    }

    public function create()
    {
        $this->authorize('create', Task::class);

        $projects = Project::orderBy('title')->get();
        $users = User::orderBy('name')->get();
        $statuses = TaskStatus::ordered()->get();

        return view('admin.tasks.create', compact('projects', 'users', 'statuses'));
    }

    public function store(Request $request, TaskService $taskService)
    {
        $this->authorize('create', Task::class);

        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status_id' => 'required|exists:task_statuses,id',
            'assignee_id' => 'required|exists:users,id',
            'deadline_at' => 'nullable|date',
            'co_executor_ids' => 'nullable|array',
            'co_executor_ids.*' => 'exists:users,id',
            'observer_ids' => 'nullable|array',
            'observer_ids.*' => 'exists:users,id',
        ]);

        $data['created_by'] = $request->user()->id;

        $task = $taskService->create($data, $data['co_executor_ids'] ?? [], $data['observer_ids'] ?? [], $request->user()->id);

        return redirect()->route('tasks.show', $task)->with('success', 'Задача создана.');
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task);

        $task->load([
            'project',
            'status',
            'assignee',
            'creator',
            'coExecutors',
            'observers',
            'comments.user',
            'deadlineChanges.changedBy',
            'disputes.openedBy',
        ]);

        return view('admin.tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
        $this->authorize('update', $task);

        $projects = Project::orderBy('title')->get();
        $users = User::orderBy('name')->get();
        $statuses = TaskStatus::ordered()->get();

        $task->load(['coExecutors', 'observers']);

        return view('admin.tasks.edit', compact('task', 'projects', 'users', 'statuses'));
    }

    public function update(Request $request, Task $task, TaskService $taskService)
    {
        $this->authorize('update', $task);

        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status_id' => 'required|exists:task_statuses,id',
            'assignee_id' => 'required|exists:users,id',
            'deadline_at' => 'nullable|date',
            'co_executor_ids' => 'nullable|array',
            'co_executor_ids.*' => 'exists:users,id',
            'observer_ids' => 'nullable|array',
            'observer_ids.*' => 'exists:users,id',
        ]);

        $taskService->update($task, $data, $data['co_executor_ids'] ?? [], $data['observer_ids'] ?? [], $request->user()->id);

        return redirect()->route('tasks.show', $task)->with('success', 'Задача обновлена.');
    }

    public function changeStatus(Request $request, Task $task, TaskService $taskService)
    {
        $this->authorize('changeStatus', $task);

        $data = $request->validate([
            'status_id' => 'required|exists:task_statuses,id',
        ]);

        $status = TaskStatus::findOrFail($data['status_id']);
        $taskService->changeStatus($task, $status, $request->user());

        return redirect()->route('tasks.show', $task)->with('success', 'Статус задачи изменён.');
    }

    public function changeDeadline(Request $request, Task $task, TaskService $taskService)
    {
        $this->authorize('changeDeadline', $task);

        $data = $request->validate([
            'deadline_at' => 'nullable|date',
            'reason' => 'nullable|string|max:255',
        ]);

        $taskService->changeDeadline($task, $data['deadline_at'] ?? null, $request->user(), $data['reason'] ?? null);

        return redirect()->route('tasks.show', $task)->with('success', 'Дедлайн обновлён.');
    }

    public function close(Task $task, TaskService $taskService, Request $request)
    {
        $this->authorize('close', $task);

        $taskService->close($task, $request->user());

        return redirect()->route('tasks.show', $task)->with('success', 'Задача закрыта.');
    }
}
