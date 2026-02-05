<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\TaskDispute;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskDisputeOpened extends Notification
{
    use Queueable;

    public function __construct(public Task $task, public TaskDispute $dispute)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'dispute_id' => $this->dispute->id,
            'reason' => $this->dispute->reason,
        ];
    }
}
