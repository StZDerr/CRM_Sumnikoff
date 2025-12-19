<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Project;
use Illuminate\Http\Request;

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
        $invoices = Invoice::orderByDesc('issued_at')->get();

        // Если у нас пришёл ?project=ID — запомним его для предзаполнения
        $selectedProjectId = $request->query('project') ? (int) $request->query('project') : null;

        return view('admin.payments.create', compact('projects', 'paymentMethods', 'invoices', 'selectedProjectId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'nullable|date',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'transaction_id' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:2000',
        ]);

        // Use current timestamp if payment_date not provided
        if (empty($data['payment_date'])) {
            $data['payment_date'] = now();
        }

        $payment = Payment::create($data);

        // обновим received_total у проекта
        $this->recalcProjectReceived($payment->project_id);

        return redirect()->route('payments.show', $payment)->with('success', 'Поступление добавлено.');
    }

    public function show(Payment $payment)
    {
        $payment->load(['project', 'paymentMethod', 'invoice']);

        return view('admin.payments.show', compact('payment'));
    }

    public function edit(Payment $payment)
    {
        $projects = Project::orderBy('title')->get();
        $paymentMethods = PaymentMethod::orderBy('sort_order')->get();
        $invoices = Invoice::orderByDesc('issued_at')->get();

        return view('admin.payments.edit', compact('payment', 'projects', 'paymentMethods', 'invoices'));
    }

    public function update(Request $request, Payment $payment)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'nullable|date',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'transaction_id' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:2000',
        ]);

        if (empty($data['payment_date'])) {
            $data['payment_date'] = now();
        }

        $oldProjectId = $payment->getOriginal('project_id');

        $payment->update($data);

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
        $payment->delete();
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
        $sum = Payment::where('project_id', $projectId)
            ->whereNotNull('payment_date')
            ->sum('amount');

        // Берём текущий ожидаемый долг (debt) — если не заполнен, используем 0
        $project = Project::find($projectId);
        $debt = $project->debt ?? 0;

        // balance = debt - received_total
        $balance = round((float) $debt - (float) $sum, 2);

        Project::where('id', $projectId)->update([
            'received_total' => $sum,
            'received_calculated_at' => now(),
            'balance' => $balance,
            'balance_calculated_at' => now(),
        ]);
    }
}
