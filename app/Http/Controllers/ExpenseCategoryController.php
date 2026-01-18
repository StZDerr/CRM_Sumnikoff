<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExpenseCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $items = ExpenseCategory::ordered()->paginate(25);

        return view('admin.expense_categories.index', compact('items'));
    }

    public function create()
    {
        return view('admin.expense_categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:expense_categories,slug',
            'sort_order' => 'nullable|integer|min:1',
            'is_office' => 'nullable|boolean',
            'is_salary' => 'nullable|boolean',
        ]);

        if (empty($data['slug'])) {
            $base = Str::slug($data['title'] ?? '');
            $slug = $base ?: Str::random(6);
            $i = 1;
            while (ExpenseCategory::where('slug', $slug)->exists()) {
                $slug = $base.'-'.$i++;
            }
            $data['slug'] = $slug;
        }

        ExpenseCategory::create($data);

        return redirect()->route('expense-categories.index')->with('success', 'Категория расходов создана.');
    }

    public function edit(ExpenseCategory $expenseCategory)
    {
        return view('admin.expense_categories.edit', compact('expenseCategory'));
    }

    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:expense_categories,slug,'.$expenseCategory->id,
            'sort_order' => 'nullable|integer|min:1',
            'is_office' => 'nullable|boolean',
            'is_salary' => 'nullable|boolean',
        ]);

        if (empty($data['slug'])) {
            $base = Str::slug($data['title'] ?? $expenseCategory->title);
            $slug = $base ?: Str::random(6);
            $i = 1;
            while (ExpenseCategory::where('slug', $slug)->where('id', '<>', $expenseCategory->id)->exists()) {
                $slug = $base.'-'.$i++;
            }
            $data['slug'] = $slug;
        }

        $expenseCategory->update($data);

        if (isset($data['sort_order']) && (int) $data['sort_order'] !== (int) $expenseCategory->sort_order) {
            $expenseCategory->moveToPosition((int) $data['sort_order']);
        }

        return redirect()->route('expense-categories.index')->with('success', 'Категория обновлена.');
    }

    public function destroy(ExpenseCategory $expenseCategory)
    {
        ExpenseCategory::where('sort_order', '>', $expenseCategory->sort_order)->decrement('sort_order');
        $expenseCategory->delete();

        return redirect()->route('expense-categories.index')->with('success', 'Категория удалена.');
    }

    public function reorder(Request $request)
    {
        $data = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:expense_categories,id',
        ]);

        foreach ($data['order'] as $index => $id) {
            ExpenseCategory::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }
}
