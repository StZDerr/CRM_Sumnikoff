<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectLawyerCommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Добавление комментария к проекту юристом или админом
    public function store(Request $request, Project $project)
    {
        // Разрешаем только назначенному юристу или админу
        if (! (auth()->user()->isAdmin() || $project->lawyerAssignments()->where('user_id', auth()->id())->exists())) {
            abort(403);
        }

        $request->validate([
            'comment' => 'required|string',
            // Несколько файлов опционально, до 10 МБ каждый
            'files' => 'nullable|array|max:8',
            'files.*' => 'file|mimes:jpeg,png,jpg,gif,webp,pdf,doc,docx,xls,xlsx,zip,txt,rtf|max:10240',
        ]);

        $filePath = null;
        $comment = $project->lawyerComments()->create([
            'user_id' => auth()->id(),
            'comment' => $request->comment,
            'file_path' => null, // legacy kept null for new comments
        ]);

        $saved = 0;
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                try {
                    if (! $file->isValid()) continue;
                    $path = $file->store("project_lawyer_comments/{$comment->id}", 'public');
                    $comment->files()->create([
                        'path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                    ]);
                    $saved++;
                } catch (\Exception $e) {
                    \Log::error('Error storing lawyer comment file', ['project_id' => $project->id, 'comment_id' => $comment->id, 'error' => $e->getMessage()]);
                }
            }
        }

        // Вернёмся на страницу назначения (обычно /lawyer/projects/{projectLawyer})
        return back()->with('success', 'Комментарий добавлен');
    }
}
