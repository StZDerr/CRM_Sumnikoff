<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class OperationController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 20;
        $page = (int) $request->query('page', 1);

        $currentUser = auth()->user();

        // По умолчанию — пустые коллекции
        $payments = collect();

        // Payments: только для admin
        if ($currentUser->isAdmin()) {
            $payments = Payment::with(['project', 'paymentMethod', 'invoice', 'bankAccount'])
                ->orderByDesc('payment_date')->limit(200)->get()
                ->map(fn ($p) => [
                    'type' => 'payment',
                    'id' => $p->id,
                    'date' => $p->payment_date ?? $p->created_at,
                    'amount' => (float) $p->amount,
                    'model' => $p,
                ]);
        }

        $expensesQuery = Expense::with(['category', 'organization', 'paymentMethod', 'bankAccount', 'project']);

        // Маркетологи видят только расходы по проектам, где они указаны
        if ($currentUser->isMarketer()) {
            $expensesQuery->whereHas('project', fn ($q) => $q->where('marketer_id', $currentUser->id));
        }

        // Проджект менеджер видит все расходы (ничего не меняем)

        // Фильтр: только офисные расходы
        if ($request->query('office') == '1') {
            $officeIds = ExpenseCategory::where('is_office', true)->pluck('id');
            $expensesQuery->whereIn('expense_category_id', $officeIds);
        }

        $expenses = $expensesQuery->orderByDesc('expense_date')->limit(200)->get()
            ->map(fn ($e) => [
                'type' => 'expense',
                'id' => $e->id,
                'date' => $e->expense_date ?? $e->created_at,
                'amount' => (float) $e->amount,
                'model' => $e,
            ]);

        // Если фильтр офиса — не добавляем платежи
        if ($request->query('office') == '1') {
            $items = $expenses->sortByDesc('date')->values();
        } else {
            $items = $payments->concat($expenses)->sortByDesc('date')->values();
        }

        $total = $items->count();
        $slice = $items->slice(($page - 1) * $perPage, $perPage)->values();

        $operations = new LengthAwarePaginator($slice, $total, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        // Данные для модальных окон
        // Офисные категории — исключаем пометку is_salary
        $officeCategories = ExpenseCategory::office()->where('is_salary', false)->ordered()->get();
        // Категории для ЗП
        $salaryCategories = ExpenseCategory::where('is_salary', true)->ordered()->get();
        $paymentMethods = PaymentMethod::orderBy('title')->get();
        $bankAccounts = BankAccount::orderBy('title')->get();
        $users = \App\Models\User::orderBy('name')->get();

        return view('admin.operation.index', compact('operations', 'officeCategories', 'salaryCategories', 'paymentMethods', 'bankAccounts', 'users'));
    }
}
