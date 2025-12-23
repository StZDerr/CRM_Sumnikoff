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
    public function index()
    {
        $items = Expense::with(['category', 'organization', 'paymentMethod', 'project'])
            ->orderByDesc('expense_date')
            ->paginate(25);

        return view('admin.expenses.index', compact('items'));
    }

    public function create()
    {
        $categories = ExpenseCategory::ordered()->get();
        $organizations = Organization::ordered()->get();
        $paymentMethods = PaymentMethod::orderBy('title')->get();
        $bankAccounts = BankAccount::orderBy('title')->get();
        $projects = Project::orderBy('title')->get();

        $expense = new Expense;

        return view('admin.expenses.create', compact('expense', 'categories', 'organizations', 'paymentMethods', 'bankAccounts', 'projects'));
    }

    public function store(Request $request)
    {
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

        return redirect()->route('expenses.index')->with('success', 'Расход сохранён.');
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
        $projects = Project::orderBy('title')->get();

        return view('admin.expenses.edit', compact('expense', 'categories', 'organizations', 'paymentMethods', 'bankAccounts', 'projects'));
    }

    public function update(Request $request, Expense $expense)
    {
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
