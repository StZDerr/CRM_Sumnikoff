<?php

namespace App\Http\Controllers;

use App\Models\Importance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ImportanceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(): View
    {
        $importances = Importance::ordered()->get();

        return view('admin.importance.index', compact('importances'));
    }

    public function create(): View
    {
        return view('admin.importance.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:importances,slug',
            'color' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:1',
        ]);

        if (empty($data['slug'])) {
            $base = Str::slug($data['name']);
            $slug = $base;
            $i = 1;
            while (Importance::where('slug', $slug)->exists()) {
                $slug = $base.'-'.$i++;
            }
            $data['slug'] = $slug;
        }

        $importance = Importance::create($data);

        if (! empty($data['sort_order'])) {
            $importance->moveToPosition((int) $data['sort_order']);
        }

        return redirect()->route('importances.index')->with('success', 'Уровень важности создан');
    }

    public function show(Importance $importance): View
    {
        return view('admin.importance.show', compact('importance'));
    }

    public function edit(Importance $importance): View
    {
        return view('admin.importance.edit', compact('importance'));
    }

    public function update(Request $request, Importance $importance): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('importances', 'slug')->ignore($importance->id)],
            'color' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:1',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $importance->update($data);

        if (! empty($data['sort_order']) && (int) $data['sort_order'] !== (int) $importance->sort_order) {
            $importance->moveToPosition((int) $data['sort_order']);
        }

        return redirect()->route('importances.index')->with('success', 'Уровень важности обновлён');
    }

    public function destroy(Importance $importance): RedirectResponse
    {
        $importance->delete();

        return redirect()->route('importances.index')->with('success', 'Уровень важности удалён');
    }

    /**
     * Reorder via AJAX — принимает payload: { order: [id1, id2, id3, ...] }
     */
    public function reorder(Request $request)
    {
        $data = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:importances,id',
        ]);

        foreach ($data['order'] as $index => $id) {
            Importance::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }
}
