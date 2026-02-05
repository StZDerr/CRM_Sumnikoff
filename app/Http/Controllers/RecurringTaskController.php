<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\RecurringTask;
use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecurringTaskController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', RecurringTask::class);

        $recurringTasks = RecurringTask::with(['project', 'assignee', 'status', 'rules'])->orderByDesc('created_at')->paginate(20);

        return view('admin.recurring_tasks.index', compact('recurringTasks'));
    }

    public function create()
    {
        $this->authorize('create', RecurringTask::class);

        $projects = Project::orderBy('title')->get();
        $users = User::orderBy('name')->get();
        $statuses = TaskStatus::ordered()->get();

        return view('admin.recurring_tasks.create', compact('projects', 'users', 'statuses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'status_id' => 'required|exists:task_statuses,id',
            'assignee_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'timezone' => 'nullable|string|max:100',
            'rules' => 'required|array|min:1',
            'rules.*.type' => 'required|in:daily,weekly,monthly',
            'rules.*.interval_days' => 'nullable|integer|min:1',
            'rules.*.weekly_days' => 'nullable|array',
            'rules.*.weekly_days.*' => 'integer|min:1|max:7',
            'rules.*.time_of_day' => 'nullable|date_format:H:i',
            'rules.*.start_date' => 'nullable|date',
            'rules.*.monthly_rules' => 'nullable|array',
        ]);

        $data['created_by'] = $request->user()->id;

        $recurringTask = DB::transaction(function () use ($data) {
            $rulesData = $data['rules'];
            unset($data['rules']);

            $recurringTask = RecurringTask::create($data);
            foreach ($rulesData as $rule) {
                $recurringTask->rules()->create($rule);
            }

            return $recurringTask;
        });

        return redirect()->route('recurring-tasks.show', $recurringTask)->with('success', 'Регулярная задача создана.');
    }

    public function show(RecurringTask $recurringTask)
    {
        $this->authorize('view', $recurringTask);

        $recurringTask->load(['project', 'assignee', 'status', 'rules', 'tasks']);

        return view('admin.recurring_tasks.show', compact('recurringTask'));
    }

    public function edit(RecurringTask $recurringTask)
    {
        $this->authorize('update', $recurringTask);

        $projects = Project::orderBy('title')->get();
        $users = User::orderBy('name')->get();
        $statuses = TaskStatus::ordered()->get();
        $recurringTask->load('rules');

        return view('admin.recurring_tasks.edit', compact('recurringTask', 'projects', 'users', 'statuses'));
    }

    public function update(Request $request, RecurringTask $recurringTask)
    {
        $this->authorize('update', $recurringTask);

        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'status_id' => 'required|exists:task_statuses,id',
            'assignee_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'timezone' => 'nullable|string|max:100',
            'rules' => 'required|array|min:1',
            'rules.*.type' => 'required|in:daily,weekly,monthly',
            'rules.*.interval_days' => 'nullable|integer|min:1',
            'rules.*.weekly_days' => 'nullable|array',
            'rules.*.weekly_days.*' => 'integer|min:1|max:7',
            'rules.*.time_of_day' => 'nullable|date_format:H:i',
            'rules.*.start_date' => 'nullable|date',
            'rules.*.monthly_rules' => 'nullable|array',
        ]);

        DB::transaction(function () use ($recurringTask, $data) {
            $rulesData = $data['rules'];
            unset($data['rules']);

            $recurringTask->update($data);
            $recurringTask->rules()->delete();
            foreach ($rulesData as $rule) {
                $recurringTask->rules()->create($rule);
            }
        });

        return redirect()->route('recurring-tasks.show', $recurringTask)->with('success', 'Регулярная задача обновлена.');
    }

    public function destroy(RecurringTask $recurringTask)
    {
        $this->authorize('delete', $recurringTask);

        $recurringTask->delete();

        return redirect()->route('recurring-tasks.index')->with('success', 'Регулярная задача удалена.');
    }
}
