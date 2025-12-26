<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectComment;
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
            'body' => 'required|string|max:2000',
            'photos' => 'nullable|array|max:5', // макс 5 файлов
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB
            'month' => ['nullable', 'regex:/^\d{4}-\d{2}$/'],
        ]);

        $comment = $project->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
            'month' => $data['month'] ?? null,
        ]);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $index => $file) {
                $path = $file->store("project_comments/{$comment->id}", 'public');
                $comment->photos()->create([
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'order' => $index,
                ]);
            }
        }

        $comment->load('user', 'photos');

        $redirect = $request->input('redirect');
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('admin.projects._comment', compact('comment', 'project'))->render(),
            ]);
        }

        if ($redirect) {
            return redirect()->to($redirect)->with('success', 'Комментарий добавлен.');
        }

        return redirect()->route('projects.show', $project)->with('success', 'Комментарий добавлен.');
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
}
