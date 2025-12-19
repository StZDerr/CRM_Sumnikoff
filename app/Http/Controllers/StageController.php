<?php

namespace App\Http\Controllers;

use App\Models\Stage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StageController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $stages = Stage::ordered()->get();

        return view('admin.stage.index', compact('stages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.stage.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:stages,slug',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:1',
        ]);

        if (empty($data['slug'])) {
            $base = Str::slug($data['name']);
            $slug = $base;
            $i = 1;
            while (Stage::where('slug', $slug)->exists()) {
                $slug = $base.'-'.$i++;
            }
            $data['slug'] = $slug;
        }

        $stage = Stage::create($data);

        if (! empty($data['sort_order'])) {
            $stage->moveToPosition((int) $data['sort_order']);
        }

        return redirect()->route('stages.index')->with('success', 'Этап создан');
    }

    /**
     * Display the specified resource.
     */
    public function show(Stage $stage): View
    {
        return view('admin.stage.show', compact('stage'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Stage $stage): View
    {
        return view('admin.stage.edit', compact('stage'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Stage $stage): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('stages', 'slug')->ignore($stage->id)],
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:1',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $stage->update($data);

        if (! empty($data['sort_order']) && (int) $data['sort_order'] !== (int) $stage->sort_order) {
            $stage->moveToPosition((int) $data['sort_order']);
        }

        return redirect()->route('stages.index')->with('success', 'Этап обновлён');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Stage $stage): RedirectResponse
    {
        $stage->delete();

        return redirect()->route('stages.index')->with('success', 'Этап удалён');
    }

    /**
     * Reorder via AJAX — принимает payload: { order: [id1, id2, ...] }
     */
    public function reorder(Request $request)
    {
        $data = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:stages,id',
        ]);

        foreach ($data['order'] as $index => $id) {
            Stage::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }
}
