<?php

namespace App\Http\Controllers;

use App\Models\MonthlyExpense;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MonthlyExpenseController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(): View
    {
        $items = MonthlyExpense::with('user')
            ->orderBy('user_id')
            ->orderBy('day_of_month')
            ->paginate(25);

        return view('admin.monthly_expenses.index', compact('items'));
    }

    public function create(): View
    {
        $users = User::orderBy('name')->get();

        return view('admin.monthly_expenses.create', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'day_of_month' => 'required|integer|min:1|max:31',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        MonthlyExpense::create($data);

        return redirect()->route('monthly-expenses.index')->with('success', 'Ежемесячный расход создан.');
    }

    public function edit(MonthlyExpense $monthlyExpense): View
    {
        $users = User::orderBy('name')->get();

        return view('admin.monthly_expenses.edit', compact('monthlyExpense', 'users'));
    }

    public function update(Request $request, MonthlyExpense $monthlyExpense): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'day_of_month' => 'required|integer|min:1|max:31',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        $monthlyExpense->update($data);

        return redirect()->route('monthly-expenses.index')->with('success', 'Ежемесячный расход обновлён.');
    }

    public function destroy(MonthlyExpense $monthlyExpense): RedirectResponse
    {
        $monthlyExpense->delete();

        return redirect()->route('monthly-expenses.index')->with('success', 'Ежемесячный расход удалён.');
    }

    public function pay(Request $request, MonthlyExpense $monthlyExpense): RedirectResponse
    {
        $user = auth()->user();
        if (! $user) {
            abort(403);
        }

        if (! $user->isAdmin() && $monthlyExpense->user_id !== $user->id) {
            abort(403);
        }

        $data = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
        ]);

        try {
            $expense = $monthlyExpense->markAsPaid($data['month']);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }

        return redirect()->back()->with('success', "Расход оплачен и создан Expense #{$expense->id}.");
    }
}
