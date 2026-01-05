<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Invoice;
use App\Models\InvoiceStatus;
use App\Models\Payment;
use App\Models\PaymentCategory;
use App\Models\PaymentMethod;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['project', 'paymentMethod', 'invoice'])->orderByDesc('payment_date');

        $project = null;
        if ($request->filled('project')) {
            $query->where('project_id', $request->project);
            $project = Project::find($request->project);
        }

        $payments = $query->paginate(20)->withQueryString();

        return view('admin.payments.index', compact('payments', 'project'));
    }

    public function create(Request $request)
    {
        $projects = Project::orderBy('title')->get();
        $paymentMethods = PaymentMethod::orderBy('sort_order')->get();
        $bankAccounts = BankAccount::orderBy('title')->get();
        $paymentCategories = PaymentCategory::ordered()->get();

        // exclude invoices with status 'Оплаченно полностью'
        $excludeStatusIds = InvoiceStatus::where('name', 'Оплаченно полностью')->pluck('id')->all();
        $invoicesQuery = Invoice::orderByDesc('issued_at');
        if (! empty($excludeStatusIds)) {
            $invoicesQuery->where(function ($q) use ($excludeStatusIds) {
                $q->whereNull('invoice_status_id')->orWhereNotIn('invoice_status_id', $excludeStatusIds);
            });
        }
        $invoices = $invoicesQuery->get();

        $invoiceStatuses = InvoiceStatus::ordered()->get(); // <-- добавлено

        $selectedProjectId = $request->query('project') ? (int) $request->query('project') : null;

        // If request is AJAX, return only the form wrapper for offcanvas
        if ($request->ajax()) {
            return view('admin.payments._form_offcanvas', compact('projects', 'paymentMethods', 'invoices', 'invoiceStatuses', 'selectedProjectId', 'bankAccounts', 'paymentCategories'));
        }

        return view('admin.payments.create', compact('projects', 'paymentMethods', 'invoices', 'invoiceStatuses', 'selectedProjectId', 'bankAccounts', 'paymentCategories'));

    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'nullable|date',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'invoice_status_id' => 'nullable|exists:invoice_statuses,id',
            'transaction_id' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:2000',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'payment_category_id' => 'nullable|exists:payment_categories,id',
        ]);

        // Use current timestamp if payment_date not provided
        if (empty($data['payment_date'])) {
            $data['payment_date'] = now();
        }

        // TAX calculation from gross amount: VAT 5%, USN 7%
        $amount = (float) ($data['amount'] ?? 0);
        $data['vat_amount'] = round($amount * 0.05, 2);
        $data['usn_amount'] = round($amount * 0.07, 2);

        // Создадим платёж и обновим статус счёта в транзакции
        DB::transaction(function () use ($data, &$payment) {
            $payment = Payment::create($data);

            if (! empty($data['invoice_id']) && array_key_exists('invoice_status_id', $data)) {
                $statusValue = $data['invoice_status_id'] === '' ? null : $data['invoice_status_id'];
                Invoice::where('id', $data['invoice_id'])->update(['invoice_status_id' => $statusValue]);
            }
        });

        // обновим received_total у проекта
        $this->recalcProjectReceived($payment->project_id);
        $this->recalcBankBalance($payment->bank_account_id ?? null);

        // If AJAX request, return JSON for client-side handling
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'payment_id' => $payment->id,
                'redirect' => route('payments.show', $payment),
            ], 201);
        }

        return redirect()->route('payments.show', $payment)->with('success', 'Поступление добавлено.');
    }

    public function show(Payment $payment)
    {
        $payment->load(['project', 'paymentMethod', 'invoice', 'paymentCategory']);

        return view('admin.payments.show', compact('payment'));
    }

    public function edit(Payment $payment)
    {
        $projects = Project::orderBy('title')->get();
        $paymentMethods = PaymentMethod::orderBy('sort_order')->get();
        $bankAccounts = BankAccount::orderBy('title')->get();
        $paymentCategories = PaymentCategory::ordered()->get();

        // exclude invoices with status 'Оплаченно полностью' but make sure to include the invoice currently linked to the payment (if any)
        $excludeStatusIds = InvoiceStatus::where('name', 'Оплаченно полностью')->pluck('id')->all();
        $invoicesQuery = Invoice::orderByDesc('issued_at');
        if (! empty($excludeStatusIds)) {
            $invoicesQuery->where(function ($q) use ($excludeStatusIds) {
                $q->whereNull('invoice_status_id')->orWhereNotIn('invoice_status_id', $excludeStatusIds);
            });
        }
        $invoices = $invoicesQuery->get();

        // ensure current linked invoice is present in the list (even if it would otherwise be excluded)
        if ($payment->invoice_id && ! $invoices->contains('id', $payment->invoice_id)) {
            $inv = Invoice::find($payment->invoice_id);
            if ($inv) {
                $invoices->prepend($inv);
            }
        }

        $invoiceStatuses = InvoiceStatus::ordered()->get(); // <-- добавлено

        return view('admin.payments.edit', compact('payment', 'projects', 'paymentMethods', 'invoices', 'invoiceStatuses', 'bankAccounts', 'paymentCategories'));
    }

    public function update(Request $request, Payment $payment)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'nullable|date',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'invoice_status_id' => 'nullable|exists:invoice_statuses,id',
            'transaction_id' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:2000',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'payment_category_id' => 'nullable|exists:payment_categories,id',
        ]);

        if (empty($data['payment_date'])) {
            $data['payment_date'] = now();
        }

        // TAX recalculation when updating amount
        $amount = (float) ($data['amount'] ?? 0);
        $data['vat_amount'] = round($amount * 0.05, 2);
        $data['usn_amount'] = round($amount * 0.07, 2);

        $oldProjectId = $payment->getOriginal('project_id');
        $oldBankId = $payment->getOriginal('bank_account_id');

        // Обновим платёж и статус счёта в транзакции
        DB::transaction(function () use ($data, $payment) {
            $payment->update($data);

            if (! empty($data['invoice_id']) && array_key_exists('invoice_status_id', $data)) {
                $statusValue = $data['invoice_status_id'] === '' ? null : $data['invoice_status_id'];
                Invoice::where('id', $data['invoice_id'])->update(['invoice_status_id' => $statusValue]);
            }
        });

        // пересчитать баланс для текущего банка
        $this->recalcBankBalance($payment->bank_account_id ?? null);

        // если сменился банк — пересчитать и для старого
        if ($oldBankId && $oldBankId != ($payment->bank_account_id ?? null)) {
            $this->recalcBankBalance($oldBankId);
        }

        // Если сменился проект — обновим оба
        if ($oldProjectId && $oldProjectId != $payment->project_id) {
            $this->recalcProjectReceived($oldProjectId);
        }
        $this->recalcProjectReceived($payment->project_id);

        return redirect()->route('payments.show', $payment)->with('success', 'Поступление обновлено.');
    }

    public function destroy(Payment $payment)
    {
        $projectId = $payment->project_id;
        $bankId = $payment->bank_account_id;
        $payment->delete();
        if ($bankId) {
            $this->recalcBankBalance($bankId);
        }
        // обновим received_total
        $this->recalcProjectReceived($projectId);

        return redirect()->route('payments.index')->with('success', 'Поступление удалено.');
    }

    private function recalcProjectReceived(?int $projectId): void
    {
        if (! $projectId) {
            return;
        }

        // Пересчитываем сумму поступлений
        // Учёт платежей: если payment_date не задан — используем created_at
        $sum = Payment::where('project_id', $projectId)
            ->where(function ($q) {
                $q->whereNotNull('payment_date')
                    ->orWhere(function ($q2) {
                        $q2->whereNull('payment_date')->whereNotNull('created_at');
                    });
            })
            ->sum('amount');

        // Берём текущий ожидаемый долг (debt) — если не заполнен, используем 0
        $project = Project::find($projectId);
        $debt = $project->debt ?? 0;

        // balance = received_total - debt (положительное — переплата)
        $balance = round((float) $sum - (float) $debt, 2);

        Project::where('id', $projectId)->update([
            'received_total' => $sum,
            'received_calculated_at' => now(),
            'balance' => $balance,
            'balance_calculated_at' => now(),
        ]);
    }

    private function recalcBankBalance(?int $bankId): void
    {
        if (! $bankId) {
            return;
        }

        $sum = Payment::where('bank_account_id', $bankId)->sum('amount');

        BankAccount::where('id', $bankId)->update([
            'balance' => round((float) $sum, 2),
        ]);
    }
}
