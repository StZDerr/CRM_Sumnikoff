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
    public function index(Project $project)
    {
        // Загружаем авторов комментариев
        $project->load(['comments.user']);

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
        ]);

        $comment = $project->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
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

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('admin.projects._comment', compact('comment', 'project'))->render(),
            ]);
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
    public function update(Request $request, ProjectComment $projectComment)
    {
        //
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
