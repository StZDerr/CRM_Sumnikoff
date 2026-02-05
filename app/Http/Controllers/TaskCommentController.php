<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    public function store(Request $request, Task $task, TaskService $taskService)
    {
        $this->authorize('comment', $task);

        $data = $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $taskService->addComment($task, $request->user(), $data['message']);

        return redirect()->route('tasks.show', $task)->with('success', 'Комментарий добавлен.');
    }
}
