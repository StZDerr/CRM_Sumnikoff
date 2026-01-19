<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // все методы — только admin, кроме create и store (чтобы маркетолог мог открыть форму и отправить её)
        $this->middleware('admin')->except(['create', 'store', 'show']);
    }

    public function index(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'error' => 'Доступ запрещён'], 403);
            }
            abort(403);
        }
        $query = Expense::with(['category', 'organization', 'paymentMethod', 'project'])
            ->orderByDesc('expense_date');

        // Фильтр: только офисные расходы
        if ($request->has('office') && $request->office == '1') {
            $officeCategoryIds = ExpenseCategory::office()->pluck('id');
            $query->whereIn('expense_category_id', $officeCategoryIds);
        }

        $items = $query->paginate(25)->withQueryString();

        // Данные для модального окна офисного расхода (исключаем категории помеченные is_salary)
        $officeCategories = ExpenseCategory::office()->where('is_salary', false)->ordered()->get();
        $paymentMethods = PaymentMethod::orderBy('title')->get();
        $bankAccounts = BankAccount::orderBy('title')->get();

        return view('admin.expenses.index', compact('items', 'officeCategories', 'paymentMethods', 'bankAccounts'));
    }

    /**
     * Сохраняет зарплатный расход аванса из табеля
     */
    public function storeAdvance(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'error' => 'Доступ запрещён'], 403);
            }
            abort(403);
        }
        $data = $request->validate([
            'expense_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'description' => 'nullable|string',
            'salary_recipient' => 'required|exists:users,id',
            'salary_report_id' => 'required|exists:salary_reports,id',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx|max:5120',
        ]);

        // Проверяем, что категория действительно помечена как is_salary
        $category = ExpenseCategory::find($data['expense_category_id']);
        if (! $category || ! $category->is_salary || $category->is_office) {
            return response()->json([
                'success' => false,
                'message' => 'Категория должна быть помечена как зарплатная и не должна быть офисной.',
            ], 422);
        }

        // Создаём расход
        $data['project_id'] = null;
        $data['organization_id'] = null;
        $data['status'] = 'paid';

        if (! empty($data['salary_recipient'])) {
            $data['salary_recipient'] = (string) $data['salary_recipient'];
        }

        $expense = Expense::create($data);

        // Сохраняем файлы
        foreach ($request->file('documents', []) as $file) {
            $path = $file->store('documents', 'public');
            $expense->documents()->create([
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
            ]);
        }

        // Обновляем статус табеля на 'advance_paid'
        $salaryReport = \App\Models\SalaryReport::find($data['salary_report_id']);
        if ($salaryReport) {
            $salaryReport->status = 'advance_paid';
            $salaryReport->advance_amount = $data['amount'];
            $salaryReport->remaining_amount = $salaryReport->total_salary - $data['amount'];
            $salaryReport->advance_paid_by = auth()->id();
            $salaryReport->save();
        }

        // Пересчитываем балансы счетов
        if (! empty($data['bank_account_id'])) {
            $this->recalcBankBalance($data['bank_account_id']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Аванс успешно выплачен, статус табеля обновлён.',
            'expense_id' => $expense->id,
        ]);
    }

    /**
     * Сохраняет полную выплату зарплаты из табеля
     */
    public function storeFinalSalary(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'error' => 'Доступ запрещён'], 403);
            }
            abort(403);
        }
        $data = $request->validate([
            'expense_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'description' => 'nullable|string',
            'salary_recipient' => 'required|exists:users,id',
            'salary_report_id' => 'required|exists:salary_reports,id',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx|max:5120',
        ]);

        // Проверяем, что категория действительно помечена как is_salary
        $category = ExpenseCategory::find($data['expense_category_id']);
        if (! $category || ! $category->is_salary || $category->is_office) {
            return response()->json([
                'success' => false,
                'message' => 'Категория должна быть помечена как зарплатная и не должна быть офисной.',
            ], 422);
        }

        // Создаём расход
        $data['project_id'] = null;
        $data['organization_id'] = null;
        $data['status'] = 'paid';

        if (! empty($data['salary_recipient'])) {
            $data['salary_recipient'] = (string) $data['salary_recipient'];
        }

        $expense = Expense::create($data);

        // Сохраняем файлы
        foreach ($request->file('documents', []) as $file) {
            $path = $file->store('documents', 'public');
            $expense->documents()->create([
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
            ]);
        }

        // Обновляем статус табеля на 'paid' (полная оплата)
        $salaryReport = \App\Models\SalaryReport::find($data['salary_report_id']);
        if ($salaryReport) {
            $salaryReport->status = 'paid';
            // Если был аванс, добавляем к нему текущую выплату, иначе записываем полную сумму
            $salaryReport->remaining_amount = 0; // Остаток = 0 при полной оплате
            $salaryReport->paid_by = auth()->id();
            $salaryReport->save();
        }

        // Пересчитываем балансы счетов
        if (! empty($data['bank_account_id'])) {
            $this->recalcBankBalance($data['bank_account_id']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Зарплата успешно выплачена, статус табеля обновлён на "Оплачено".',
            'expense_id' => $expense->id,
        ]);
    }

    /**
     * Создание зарплатного расхода (аналогично офисному модалке, но категории is_salary)
     */
    public function storeSalary(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'error' => 'Доступ запрещён'], 403);
            }
            abort(403);
        }
        $data = $request->validate([
            'expense_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'description' => 'nullable|string',
            'salary_recipient' => 'nullable|exists:users,id',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx|max:5120',
        ]);

        // Проверяем, что категория действительно помечена как is_salary и не является офисной
        $category = ExpenseCategory::find($data['expense_category_id']);
        if (! $category || ! $category->is_salary || $category->is_office) {
            return response()->json([
                'success' => false,
                'message' => 'Категория должна быть помечена как зарплатная и не должна быть офисной.',
            ], 422);
        }

        // Создаём расход без привязки к проекту
        $data['project_id'] = null;
        $data['organization_id'] = null;
        $data['status'] = 'paid';

        // Сохраняваем id получателя зарплаты как строку (поле в БД string)
        if (! empty($data['salary_recipient'])) {
            $data['salary_recipient'] = (string) $data['salary_recipient'];
        }

        $expense = Expense::create($data);

        // Сохраняем файлы
        foreach ($request->file('documents', []) as $file) {
            $path = $file->store('documents', 'public');
            $expense->documents()->create([
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        // Пересчёт баланса банка
        if ($expense->bank_account_id) {
            $this->recalcBankBalance($expense->bank_account_id);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'expense_id' => $expense->id,
                'message' => 'Зарплатный расход сохранён.',
            ], 201);
        }

        return redirect()->route('expenses.index')->with('success', 'Зарплатный расход сохранён.');
    }

    public function create(Request $request)
    {

        // Не показываем зарплатные категории в общей форме расходов
        $categories = ExpenseCategory::ordered()->where('is_salary', false)->get();
        $organizations = Organization::ordered()->get();
        $paymentMethods = PaymentMethod::orderBy('title')->get();
        $bankAccounts = BankAccount::orderBy('title')->get();

        $currentUser = auth()->user();
        if ($currentUser->isMarketer()) {
            $projects = Project::where('marketer_id', $currentUser->id)->orderBy('title')->get();
        } else {
            $projects = Project::orderBy('title')->get();
        }

        $expense = new Expense;

        // If request is AJAX, return only the form wrapper for offcanvas (без офисных категорий)
        if ($request->ajax()) {
            // Для offcanvas формы исключаем зарплатные категории
            $categories = ExpenseCategory::notOffice()->where('is_salary', false)->ordered()->get();

            return view('admin.expenses._form_offcanvas', compact('expense', 'categories', 'organizations', 'paymentMethods', 'bankAccounts', 'projects'));
        }

        return view('admin.expenses.create', compact('expense', 'categories', 'organizations', 'paymentMethods', 'bankAccounts', 'projects'));
    }

    public function store(Request $request)
    {
        // ПРоверка, что бы маркетолог мог создавать только записи, где он указан в роли маркетолога проекта
        $current = auth()->user();
        if (! $current->isAdmin() && ! $current->isMarketer()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'error' => 'Доступ запрещён'], 403);
            }
            abort(403);
        }
        $data = $request->validate([
            'expense_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'expense_category_id' => 'nullable|exists:expense_categories,id',
            'organization_id' => 'nullable|exists:organizations,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'project_id' => 'nullable|exists:projects,id',
            'document_number' => 'nullable|string|max:255',
            'status' => 'required|string|in:paid,awaiting,partial',
            'description' => 'nullable|string',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx|max:5120',
        ]);

        // Проверяем, чтобы зарплатные категории нельзя было создать через общую форму
        if (! empty($data['expense_category_id'])) {
            $catCheck = ExpenseCategory::find($data['expense_category_id']);
            if ($catCheck && $catCheck->is_salary) {
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'error' => 'Нельзя создавать зарплатные расходы через эту форму. Используйте раздел ЗП.'], 422);
                }

                return redirect()->back()->with('error', 'Нельзя создавать зарплатные расходы через эту форму.');
            }
        }

        // Если маркетолог, проверяем, что проект принадлежит ему
        $currentUser = auth()->user();
        if (! empty($data['project_id']) && $currentUser->isMarketer()) {
            $project = Project::find($data['project_id']);
            if (! $project || $project->marketer_id !== $currentUser->id) {
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'error' => 'Доступ запрещён'], 403);
                }

                return redirect()->back()->with('error', 'Доступ запрещён');
            }
        }

        $expense = Expense::create($data);

        // Сохраняем файлы (если пришли)
        foreach ($request->file('documents', []) as $file) {
            $path = $file->store('documents', 'public');
            $expense->documents()->create([
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        // пересчёт баланса банка (если указан)
        if ($expense->bank_account_id) {
            $this->recalcBankBalance($expense->bank_account_id);
        }

        // If AJAX request, return JSON for client-side handling
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'expense_id' => $expense->id,
                'redirect' => route('operation.index'),
            ], 201);
        }

        return redirect()->route('operation.index')->with('success', 'Расход сохранён.');
    }

    /**
     * Создание офисного расхода (быстрая форма).
     * Категория ограничена только офисными, project_id = null.
     */
    public function storeOffice(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'error' => 'Доступ запрещён'], 403);
            }
            abort(403);
        }
        $data = $request->validate([
            'expense_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'description' => 'nullable|string',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx|max:5120',
        ]);

        // Проверяем, что категория действительно офисная и не является зарплатной
        $category = ExpenseCategory::find($data['expense_category_id']);
        if (! $category || ! $category->is_office || $category->is_salary) {
            return response()->json([
                'success' => false,
                'message' => 'Категория должна быть офисной и не может быть помечена как зарплатная.',
            ], 422);
        }

        // Создаём расход без привязки к проекту
        $data['project_id'] = null;
        $data['organization_id'] = null;
        $data['status'] = 'paid'; // По умолчанию оплачено

        $expense = Expense::create($data);

        // Сохраняем файлы
        foreach ($request->file('documents', []) as $file) {
            $path = $file->store('documents', 'public');
            $expense->documents()->create([
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        // Пересчёт баланса банка
        if ($expense->bank_account_id) {
            $this->recalcBankBalance($expense->bank_account_id);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'expense_id' => $expense->id,
                'message' => 'Офисный расход сохранён.',
            ], 201);
        }

        return redirect()->route('expenses.index')->with('success', 'Офисный расход сохранён.');
    }

    public function show(Expense $expense)
    {

        $expense->load('documents', 'category', 'organization', 'paymentMethod', 'bankAccount', 'project');

        return view('admin.expenses.show', compact('expense'));
    }

    public function edit(Expense $expense)
    {
        $categories = ExpenseCategory::ordered()->get();
        $organizations = Organization::ordered()->get();
        $paymentMethods = PaymentMethod::orderBy('title')->get();
        $bankAccounts = BankAccount::orderBy('title')->get();

        $currentUser = auth()->user();
        if ($currentUser->isMarketer()) {
            $projects = Project::where('marketer_id', $currentUser->id)->orderBy('title')->get();
        } else {
            $projects = Project::orderBy('title')->get();
        }

        return view('admin.expenses.edit', compact('expense', 'categories', 'organizations', 'paymentMethods', 'bankAccounts', 'projects'));
    }

    public function update(Request $request, Expense $expense)
    {
        if (! auth()->user()->isAdmin()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'error' => 'Доступ запрещён'], 403);
            }
            abort(403);
        }
        $data = $request->validate([
            'expense_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'expense_category_id' => 'nullable|exists:expense_categories,id',
            'organization_id' => 'nullable|exists:organizations,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'project_id' => 'nullable|exists:projects,id',
            'document_number' => 'nullable|string|max:255',
            'status' => 'required|string|in:paid,awaiting,partial',
            'description' => 'nullable|string',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx|max:5120',
        ]);

        $expense->update($data);
        $oldBankId = $expense->getOriginal('bank_account_id');
        // пересчитать для текущего банка
        if ($expense->bank_account_id) {
            $this->recalcBankBalance($expense->bank_account_id);
        }

        // если сменился банк, пересчитать и для старого
        if ($oldBankId && $oldBankId !== (int) ($expense->bank_account_id ?? 0)) {
            $this->recalcBankBalance($oldBankId);
        }
        foreach ($request->file('documents', []) as $file) {
            $path = $file->store('documents', 'public');
            $expense->documents()->create([
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        return redirect()->route('expenses.index')->with('success', 'Расход обновлён.');
    }

    public function destroy(Expense $expense)
    {
        // При необходимости можно удалить файлы из диска:
        foreach ($expense->documents as $doc) {
            Storage::disk('public')->delete($doc->path);
            $doc->delete();
        }
        $bankId = $expense->bank_account_id;
        $expense->delete();
        if ($bankId) {
            $this->recalcBankBalance($bankId);
        }

        return redirect()->route('expenses.index')->with('success', 'Расход удалён.');
    }

    private function recalcBankBalance(?int $bankId): void
    {
        if (! $bankId) {
            return;
        }

        // Сумма всех поступлений на счёт
        $paymentsSum = Payment::where('bank_account_id', $bankId)->sum('amount');

        // Сумма всех расходов с этого счёта
        $expensesSum = Expense::where('bank_account_id', $bankId)->sum('amount');

        $balance = round((float) $paymentsSum - (float) $expensesSum, 2);

        BankAccount::where('id', $bankId)->update(['balance' => $balance]);
    }
}
