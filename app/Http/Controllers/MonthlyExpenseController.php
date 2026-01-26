<?php

namespace App\Http\Controllers;

use App\Models\MonthlyExpense;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Carbon\Carbon;

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

    /**
     * Пометить как оплаченный без создания Expense.
     */
    public function markPaidOnly(Request $request, MonthlyExpense $monthlyExpense)
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
            $monthKey = Carbon::createFromFormat('Y-m', $data['month'])->format('Y-m');

            DB::transaction(function () use ($monthlyExpense, $monthKey) {
                $status = $monthlyExpense->statuses()
                    ->where('month', $monthKey)
                    ->lockForUpdate()
                    ->first();

                if ($status && $status->paid_at) {
                    throw ValidationException::withMessages([
                        'month' => 'Этот ежемесячный расход уже помечен как оплаченный за выбранный месяц.',
                    ]);
                }

                if (! $status) {
                    $status = $monthlyExpense->statuses()->create([
                        'month' => $monthKey,
                    ]);
                }

                $status->update([
                    'paid_at' => now(),
                    'expense_id' => null,
                ]);
            });
        } catch (ValidationException $e) {
            if ($request->wantsJson()) {
                return response()->json(['errors' => $e->errors()], 422);
            }

            return redirect()->back()->withErrors($e->errors());
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['errors' => ['server' => ['Ошибка сервера. Попробуйте позже.']]], 500);
            }

            return redirect()->back()->withErrors(['server' => 'Ошибка сервера. Попробуйте позже.']);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Ежемесячный расход помечен как оплаченный.');
    }

    public function pay(Request $request, MonthlyExpense $monthlyExpense)
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
            'amount' => ['nullable', 'numeric', 'min:0.01'],
            'expense_date' => ['nullable', 'date_format:Y-m-d'],
            'expense_category_id' => ['nullable', 'integer', 'exists:expense_categories,id'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'organization_id' => ['nullable', 'integer', 'exists:organizations,id'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $overrides = [];
        foreach (['amount', 'expense_date', 'expense_category_id', 'project_id', 'organization_id', 'description'] as $k) {
            if ($request->has($k) && $data[$k] !== null) {
                $overrides[$k] = $data[$k];
            }
        }

        try {
            $expense = $monthlyExpense->createExpenseForMonth($data['month'], $overrides);
        } catch (ValidationException $e) {
            if ($request->wantsJson()) {
                return response()->json(['errors' => $e->errors()], 422);
            }

            return redirect()->back()->withErrors($e->errors());
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'expense_id' => $expense->id]);
        }

        return redirect()->back()->with('success', "Расход оплачен и создан Expense #{$expense->id}.");
    }
}
