<?php

namespace App\Http\Controllers;

use App\Models\TaskStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TaskStatusController extends Controller
{
    public function index()
    {
        $statuses = TaskStatus::ordered()->get();

        return view('admin.tasks.statuses', compact('statuses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer',
            'is_default' => 'nullable|boolean',
        ]);

        $data['slug'] = $this->generateUniqueSlug($data['slug'] ?? null, $data['name']);
        if (! isset($data['sort_order'])) {
            $data['sort_order'] = (int) (TaskStatus::max('sort_order') ?? 0) + 1;
        }

        TaskStatus::create($data);

        return redirect()->route('task-statuses.index')->with('success', 'Статус добавлен.');
    }

    protected function generateUniqueSlug(?string $slug, string $name): string
    {
        $base = Str::slug($slug ?: $name);
        $base = $base !== '' ? $base : Str::random(6);

        $candidate = $base;
        $counter = 1;

        while (TaskStatus::where('slug', $candidate)->exists()) {
            $candidate = $base.'-'.$counter;
            $counter++;
        }

        return $candidate;
    }
}
