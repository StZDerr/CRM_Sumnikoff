<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskDispute;
use App\Services\TaskService;
use Illuminate\Http\Request;

class TaskDisputeController extends Controller
{
    public function store(Request $request, Task $task, TaskService $taskService)
    {
        $this->authorize('changeStatus', $task);

        $data = $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        $taskService->openDispute($task, $request->user(), $data['reason'] ?? null);

        return redirect()->route('tasks.show', $task)->with('success', 'Спор по закрытию задачи открыт.');
    }

    public function resolve(Request $request, TaskDispute $dispute, TaskService $taskService)
    {
        $user = $request->user();
        if (! ($user->isAdmin() || $user->isProjectManager())) {
            abort(403);
        }

        $data = $request->validate([
            'resolution' => 'required|in:'.TaskDispute::STATUS_APPROVED_CLOSE.','.TaskDispute::STATUS_REJECTED_CLOSE,
        ]);

        $taskService->resolveDispute($dispute, $user, $data['resolution']);

        return redirect()->route('tasks.show', $dispute->task)->with('success', 'Спор решён.');
    }
}
