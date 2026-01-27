<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectComment;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectCommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Project $project)
    {
        $month = $request->query('month');

        // Загружаем комментарии с юзером и фото; если указан месяц — фильтруем
        $query = $project->comments()->with(['user', 'photos'])->orderByDesc('created_at');

        if (! empty($month)) {
            // Допустим формат YYYY-MM
            $query->where('month', $month);
        }

        $comments = $query->get();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('admin.projects._comments', compact('project', 'comments'))->render(),
            ]);
        }

        // Для обычного запроса вернём фрагмент (или можно оставить как прежде)
        $project->setRelation('comments', $comments);

        return view('admin.projects._comments', compact('project'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        $data = $request->validate([
            'body' => 'required|string',
            'photos' => 'nullable|array|max:5', // макс 5 изображений
            'photos.*' => 'file|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB
            'documents' => 'nullable|array|max:10', // макс 10 документов
            'documents.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,zip,txt,rtf|max:10240', // 10MB
            'month' => ['nullable', 'regex:/^\d{4}-\d{2}$/'],
        ]);

        $comment = $project->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
            'month' => $data['month'] ?? null,
        ]);

        $fileErrors = [];
        $savedCount = 0;
        $order = 0;

        // Images
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                try {
                    if (! $file->isValid()) {
                        $fileErrors[] = "Файл {$file->getClientOriginalName()} невалиден";
                        continue;
                    }

                    $path = $file->store("project_comments/{$comment->id}", 'public');
                    $comment->photos()->create([
                        'path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'order' => $order++,
                    ]);

                    $savedCount++;
                } catch (\Exception $e) {
                    \Log::error('Error saving project comment file', [
                        'project_id' => $project->id,
                        'comment_id' => $comment->id,
                        'error' => $e->getMessage(),
                    ]);
                    $fileErrors[] = "Ошибка при сохранении файла {$file->getClientOriginalName()}";
                }
            }
        }

        // Documents
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                try {
                    if (! $file->isValid()) {
                        $fileErrors[] = "Файл {$file->getClientOriginalName()} невалиден";
                        continue;
                    }

                    $path = $file->store("project_comments/{$comment->id}", 'public');
                    $comment->photos()->create([
                        'path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'order' => $order++,
                    ]);

                    $savedCount++;
                } catch (\Exception $e) {
                    \Log::error('Error saving project comment file', [
                        'project_id' => $project->id,
                        'comment_id' => $comment->id,
                        'error' => $e->getMessage(),
                    ]);
                    $fileErrors[] = "Ошибка при сохранении файла {$file->getClientOriginalName()}";
                }
            }
        }

        $comment->load('user', 'photos');

        $redirect = $request->input('redirect');

        if ($request->ajax() || $request->wantsJson()) {
            if (! empty($fileErrors)) {
                return response()->json(['errors' => $fileErrors], 422);
            }

            return response()->json([
                'html' => view('admin.projects._comment', compact('comment', 'project'))->render(),
            ]);
        }

        if (! empty($fileErrors)) {
            $message = 'Комментарий добавлен, но были ошибки с файлами: '.implode('; ', $fileErrors);

            return redirect()->to($redirect ?: route('projects.show', $project))->with('error', $message)->with('success', 'Комментарий добавлен.');
        }

        $successMessage = 'Комментарий добавлен.'.($savedCount ? " Файлов сохранено: {$savedCount}" : '');

        if ($redirect) {
            return redirect()->to($redirect)->with('success', $successMessage);
        }

        return redirect()->route('projects.show', $project)->with('success', $successMessage);
    }

    /**
     * Display the specified resource.
     */
    public function show(ProjectComment $projectComment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProjectComment $projectComment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, ProjectComment $comment)
    {
        $user = $request->user();
        if (! ($user->isAdmin() || $user->id === $comment->user_id)) {
            abort(403);
        }

        $data = $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        $comment->update(['body' => $data['body']]);
        $comment->load('user', 'photos');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('admin.projects._comment', compact('comment', 'project'))->render(),
            ]);
        }

        return redirect()->route('projects.show', $project)->with('success', 'Комментарий обновлён.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Project $project, ProjectComment $comment)
    {
        $user = auth()->user();
        if (! ($user->isAdmin() || $user->id === $comment->user_id)) {
            abort(403);
        }

        foreach ($comment->photos as $photo) {
            Storage::disk('public')->delete($photo->path);
        }
        $comment->photos()->delete();
        $comment->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['deleted' => true]);
        }

        return redirect()->route('projects.show', $project)->with('success', 'Комментарий удалён.');
    }

    /**
     * Удаление отдельного файла (фото/документ) из комментария
     */
    public function destroyFile(Request $request, Project $project, ProjectComment $comment, Photo $photo)
    {
        $user = auth()->user();
        if (! ($user->isAdmin() || $user->id === $comment->user_id)) {
            abort(403);
        }

        // Защита: убедимся, что файл принадлежит комментарию
        if ($photo->project_comment_id !== $comment->id) {
            abort(404);
        }

        // Удаляем файл с диска и запись в БД
        Storage::disk('public')->delete($photo->path);
        $photo->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['deleted' => true]);
        }

        return redirect()->route('projects.show', $project)->with('success', 'Файл удалён.');
    }
}
