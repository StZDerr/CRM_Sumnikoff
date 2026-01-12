<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceStatus;
use App\Models\PaymentMethod;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['project', 'paymentMethod'])->orderByDesc('issued_at');

        $project = null;
        if ($request->filled('project')) {
            $query->where('project_id', $request->project);
            $project = Project::find($request->project);
        }

        $invoices = $query->paginate(20)->withQueryString();

        return view('admin.invoices.index', compact('invoices', 'project'));
    }

    // create — запоминаем ?project=ID для предзаполнения
    public function create(Request $request)
    {
        $projects = Project::orderBy('title')->get();
        $paymentMethods = PaymentMethod::orderBy('sort_order')->get();
        $invoiceStatuses = InvoiceStatus::ordered()->get();

        $selectedProjectId = $request->query('project') ? (int) $request->query('project') : null;

        return view('admin.invoices.create', compact('projects', 'paymentMethods', 'invoiceStatuses', 'selectedProjectId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'number' => 'required|string|max:255|unique:invoices,number',
            'issued_at' => 'required|date',
            'project_id' => 'required|exists:projects,id',
            'contract_number' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'invoice_status_id' => 'nullable|exists:invoice_statuses,id',
            'attachments' => 'nullable|array|max:10',
            'attachments.*' => 'file|mimes:pdf,jpeg,png,jpg,gif,webp|max:10240',
            'transaction_id' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($data, $request, &$invoice) {
            $files = $request->file('attachments', []);
            $storeData = $data;
            unset($storeData['attachments']);

            $invoice = Invoice::create($storeData);

            $paths = [];
            foreach ($files as $file) {
                $paths[] = $file->store("invoices/{$invoice->id}", 'public');
            }

            if ($paths) {
                $invoice->update(['attachments' => $paths]);
            }
        });

        return redirect()->route('invoices.show', $invoice)->with('success', 'Счёт сохранён.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['project', 'paymentMethod']);

        return view('admin.invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $projects = Project::orderBy('title')->get();
        $paymentMethods = PaymentMethod::orderBy('sort_order')->get();
        $invoiceStatuses = InvoiceStatus::ordered()->get();

        return view('admin.invoices.edit', compact('invoice', 'projects', 'paymentMethods', 'invoiceStatuses'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'number' => ['required', 'string', 'max:255', Rule::unique('invoices', 'number')->ignore($invoice->id)],
            'issued_at' => 'required|date',
            'project_id' => 'required|exists:projects,id',
            'contract_number' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'invoice_status_id' => 'nullable|exists:invoice_statuses,id',
            'attachments' => 'nullable|array|max:10',
            'attachments.*' => 'file|mimes:pdf,jpeg,png,jpg,gif,webp|max:10240',
            'transaction_id' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($data, $request, $invoice) {
            $files = $request->file('attachments', []);
            $storeData = $data;
            unset($storeData['attachments']);

            $invoice->update($storeData);

            $paths = $invoice->attachments ?? [];
            foreach ($files as $file) {
                $paths[] = $file->store("invoices/{$invoice->id}", 'public');
            }

            $invoice->update(['attachments' => $paths]);
        });

        return redirect()->route('invoices.show', $invoice)->with('success', 'Счёт обновлён.');
    }

    public function destroy(Invoice $invoice)
    {
        // удалить файлы
        foreach ($invoice->attachments ?? [] as $path) {
            Storage::disk('public')->delete($path);
        }

        $invoice->delete();

        return redirect()->route('invoices.index')->with('success', 'Счёт удалён.');
    }

    public function invoicesByProject(Project $project)
    {
        try {
            $excludeStatusIds = InvoiceStatus::whereRaw('LOWER(name) LIKE ?', ['%оплач%'])->pluck('id')->all();

            $query = Invoice::where('project_id', $project->id)
                ->orderByDesc('issued_at')
                ->with('status');

            if (! empty($excludeStatusIds)) {
                $query->where(function ($q) use ($excludeStatusIds) {
                    $q->whereNull('invoice_status_id')
                        ->orWhereNotIn('invoice_status_id', $excludeStatusIds);
                });
            }

            $invoices = $query->get(['id', 'number', 'issued_at', 'amount', 'transaction_id', 'invoice_status_id'])
                ->map(function ($inv) {
                    return [
                        'id' => $inv->id,
                        'number' => $inv->number,
                        'issued_at' => optional($inv->issued_at)->toDateString(),
                        'amount' => (float) $inv->amount,
                        'transaction_id' => $inv->transaction_id,
                        'invoice_status_id' => $inv->invoice_status_id,
                        'invoice_status_name' => $inv->status?->name ?? null,
                    ];
                });

            return response()->json($invoices);
        } catch (\Throwable $e) {
            \Log::error('invoicesByProject error', ['project_id' => $project->id, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return response()->json(['error' => 'Не удалось загрузить счета проекта'], 500);
        }
    }
}
