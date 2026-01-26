<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Payment;
use App\Models\PaymentCategory;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class OperationController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 100;
        $page = (int) $request->query('page', 1);

        $currentUser = auth()->user();

        // date filter (required — default to current month)
        $dateFrom = $request->query('date_from') ? \Illuminate\Support\Carbon::parse($request->query('date_from'))->startOfDay() : \Illuminate\Support\Carbon::now()->startOfMonth();
        $dateTo = $request->query('date_to') ? \Illuminate\Support\Carbon::parse($request->query('date_to'))->endOfDay() : \Illuminate\Support\Carbon::now()->endOfMonth();

        $type = $request->query('type', 'all'); // payment|expense|all
        $projectId = $request->query('project_id');
        $expenseCategoryId = $request->query('expense_category_id');
        $paymentCategoryId = $request->query('payment_category_id');
        $expenseFlag = $request->query('expense_flag');
        $isSalary = $expenseFlag === 'salary' ? '1' : null;
        $isOffice = $expenseFlag === 'office' ? '1' : null;
        $isDomains = $expenseFlag === 'domains' ? '1' : null;
        $amountMin = $request->query('amount_min');
        $amountMax = $request->query('amount_max');
        $q = $request->query('q');
        $expenseStatus = $request->query('expense_status');
        $invoiceId = $request->query('invoice_id');
        $paymentMethodId = $request->query('payment_method_id');
        $bankAccountId = $request->query('bank_account_id');
        $createdBy = $request->query('created_by');
        $hasProject = $request->query('has_project'); // 1 -> has, 0 -> no
        $sortAmount = $request->query('sort_amount'); // asc|desc

        // По умолчанию — пустые коллекции
        $payments = collect();

        // Payments: только для admin
        if ($currentUser->isAdmin()) {
            $paymentsQuery = Payment::with(['project', 'paymentMethod', 'invoice', 'bankAccount']);

            // Date
            $paymentsQuery->whereRaw('DATE(COALESCE(payment_date, payments.created_at)) between ? and ?', [$dateFrom->toDateString(), $dateTo->toDateString()]);

            if ($projectId) {
                $paymentsQuery->where('project_id', $projectId);
            }
            if ($paymentCategoryId) {
                $paymentsQuery->where('payment_category_id', $paymentCategoryId);
            }
            if ($paymentMethodId) {
                $paymentsQuery->where('payment_method_id', $paymentMethodId);
            }
            if ($bankAccountId) {
                $paymentsQuery->where('bank_account_id', $bankAccountId);
            }
            if ($createdBy) {
                $paymentsQuery->where('created_by', $createdBy);
            }
            if ($invoiceId) {
                $paymentsQuery->where('invoice_id', $invoiceId);
            }
            if ($amountMin) {
                $paymentsQuery->where('amount', '>=', (float) $amountMin);
            }
            if ($amountMax) {
                $paymentsQuery->where('amount', '<=', (float) $amountMax);
            }
            if ($q) {
                $paymentsQuery->where(function ($qq) use ($q) {
                    $qq->where('note', 'like', "%{$q}%")
                        ->orWhere('transaction_id', 'like', "%{$q}%")
                        ->orWhereHas('invoice', fn ($qi) => $qi->where('number', 'like', "%{$q}%"));
                });
            }

            if ($hasProject === '1') {
                $paymentsQuery->whereNotNull('project_id');
            }
            if ($hasProject === '0') {
                $paymentsQuery->whereNull('project_id');
            }

            $payments = $paymentsQuery->orderByDesc('payment_date')->limit(1000)->get()
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

        // Date (include full day range for datetime values)
        $expensesQuery->whereBetween('expense_date', [$dateFrom->toDateTimeString(), $dateTo->toDateTimeString()]);

        if ($projectId) {
            $expensesQuery->where('project_id', $projectId);
        }
        if ($expenseCategoryId) {
            $expensesQuery->where('expense_category_id', $expenseCategoryId);
        }
        if ($paymentMethodId) {
            $expensesQuery->where('payment_method_id', $paymentMethodId);
        }
        if ($bankAccountId) {
            $expensesQuery->where('bank_account_id', $bankAccountId);
        }
        if ($createdBy) {
            if (\Illuminate\Support\Facades\Schema::hasColumn((new \App\Models\Expense)->getTable(), 'created_by')) {
                $expensesQuery->where('created_by', $createdBy);
            } else {
                $expensesQuery->whereRaw('1 = 0');
            }
        }
        if ($expenseStatus) {
            $expensesQuery->where('status', $expenseStatus);
        }
        if ($amountMin) {
            $expensesQuery->where('amount', '>=', (float) $amountMin);
        }
        if ($amountMax) {
            $expensesQuery->where('amount', '<=', (float) $amountMax);
        }
        if ($q) {
            $expensesQuery->where(function ($qe) use ($q) {
                $qe->where('description', 'like', "%{$q}%")
                    ->orWhere('document_number', 'like', "%{$q}%")
                    ->orWhereHas('organization', function ($qo) use ($q) {
                        $qo->where('name_full', 'like', "%{$q}%")
                            ->orWhere('name_short', 'like', "%{$q}%");
                    });
            });
        }

        if ($hasProject === '1') {
            $expensesQuery->whereNotNull('project_id');
        }
        if ($hasProject === '0') {
            $expensesQuery->whereNull('project_id');
        }

        // Filters: is_salary / is_office / is_domains (categories flags)
        if ($isSalary == '1' || $isOffice == '1' || $isDomains == '1') {
            $catQuery = \App\Models\ExpenseCategory::query();
            $catQuery->where(function ($q) use ($isSalary, $isOffice, $isDomains) {
                if ($isSalary == '1') {
                    $q->orWhere('is_salary', true);
                }
                if ($isOffice == '1') {
                    $q->orWhere('is_office', true);
                }
                if ($isDomains == '1') {
                    $q->orWhere('is_domains_hosting', true);
                }
            });
            $catIds = $catQuery->pluck('id');

            if ($isDomains == '1') {
                // domains filter should include expenses tied to a domain even if category flag missing
                $expensesQuery->where(function ($q) use ($catIds) {
                    $q->whereIn('expense_category_id', $catIds)
                        ->orWhereNotNull('domain_id');
                });
            } else {
                $expensesQuery->whereIn('expense_category_id', $catIds);
            }
        }

        $expenses = $expensesQuery->orderByDesc('expense_date')->limit(1000)->get()
            ->map(fn ($e) => [
                'type' => 'expense',
                'id' => $e->id,
                'date' => $e->expense_date ?? $e->created_at,
                'amount' => (float) $e->amount,
                'model' => $e,
            ]);

        // If specific type requested or if type-specific filters are applied
        $expenseOnlyFilters = ($expenseCategoryId || $expenseStatus || $isSalary == '1' || $isOffice == '1' || $isDomains == '1');
        $paymentOnlyFilters = ($paymentCategoryId || $invoiceId);

        if ($type === 'payment' || ($paymentOnlyFilters && ! $expenseOnlyFilters)) {
            $items = $payments->sortByDesc('date')->values();
        } elseif ($type === 'expense' || ($expenseOnlyFilters && ! $paymentOnlyFilters)) {
            $items = $expenses->sortByDesc('date')->values();
        } else {
            $items = $payments->concat($expenses)->sortByDesc('date')->values();
        }

        // Amount sort
        if ($sortAmount === 'asc') {
            $items = $items->sortBy('amount')->values();
        } elseif ($sortAmount === 'desc') {
            $items = $items->sortByDesc('amount')->values();
        }

        $total = $items->count();
        $slice = $items->slice(($page - 1) * $perPage, $perPage)->values();

        $operations = new LengthAwarePaginator($slice, $total, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        // Данные для модальных окон и фильтров
        // Офисные категории — исключаем пометку is_salary
        $officeCategories = ExpenseCategory::office()->where('is_salary', false)->ordered()->get();
        // Категории для ЗП
        $salaryCategories = ExpenseCategory::where('is_salary', true)->ordered()->get();

        // Категории для Домена и Хостинга
        $domainHostingCategories = ExpenseCategory::where('is_domains_hosting', true)->ordered()->get();
        $domains = \App\Models\Domain::where('provider', 'manual')->orderBy('name')->get();
        $paymentMethods = PaymentMethod::orderBy('title')->get();
        $bankAccounts = BankAccount::orderBy('title')->get();
        $users = \App\Models\User::orderBy('name')->get();

        // Доп. фильтровые справочники
        $projects = \App\Models\Project::orderBy('title')->get();
        $expenseCategories = ExpenseCategory::ordered()->get();
        $paymentCategories = PaymentCategory::ordered()->get();

        // Итоги по текущему набору айтемов
        $sumIncome = $items->where('type', 'payment')->sum('amount');
        $sumExpense = $items->where('type', 'expense')->sum('amount');

        return view('admin.operation.index', compact(
            'operations',
            'officeCategories',
            'salaryCategories',
            'paymentMethods',
            'bankAccounts',
            'users',
            'projects',
            'expenseCategories',
            'paymentCategories',
            'sumIncome',
            'sumExpense',
            'domainHostingCategories',
            'domains'
        ));
    }
}
