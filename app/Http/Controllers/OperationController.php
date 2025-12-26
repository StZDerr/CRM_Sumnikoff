<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Payment;
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

        $expenses = Expense::with(['category', 'organization', 'paymentMethod', 'bankAccount', 'project'])
            ->orderByDesc('expense_date')->limit(200)->get()
            ->map(fn ($e) => [
                'type' => 'expense',
                'id' => $e->id,
                'date' => $e->expense_date ?? $e->created_at,
                'amount' => (float) $e->amount,
                'model' => $e,
            ]);

        $items = $payments->concat($expenses)->sortByDesc('date')->values();

        $total = $items->count();
        $slice = $items->slice(($page - 1) * $perPage, $perPage)->values();

        $operations = new LengthAwarePaginator($slice, $total, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return view('admin.operation.index', compact('operations'));
    }
}
