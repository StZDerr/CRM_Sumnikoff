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

        $payments = Payment::with(['project', 'paymentMethod', 'invoice', 'bankAccount'])
            ->orderByDesc('payment_date')->limit(200)->get()
            ->map(fn ($p) => [
                'type' => 'payment',
                'id' => $p->id,
                'date' => $p->payment_date ?? $p->created_at,
                'amount' => (float) $p->amount,
                'model' => $p,
            ]);

        $expensesQuery = Expense::with(['category', 'organization', 'paymentMethod', 'bankAccount', 'project']);
        
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

        // Данные для модального окна офисного расхода
        $officeCategories = ExpenseCategory::office()->ordered()->get();
        $paymentMethods = PaymentMethod::orderBy('title')->get();
        $bankAccounts = BankAccount::orderBy('title')->get();

        return view('admin.operation.index', compact('operations', 'officeCategories', 'paymentMethods', 'bankAccounts'));
    }
}
