<?php

namespace App\Http\Controllers;

use App\Models\InvoiceStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InvoiceStatusController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $items = InvoiceStatus::ordered()->get();

        return view('admin.invoice_statuses.index', compact('items'));
    }

    public function create()
    {
        $invoiceStatus = new InvoiceStatus;

        return view('admin.invoice_statuses.create', compact('invoiceStatus'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:invoice_statuses,slug',
            'sort_order' => 'nullable|integer|min:1',
        ]);

        if (empty($data['slug'])) {
            $base = Str::slug($data['name'] ?? '');
            $slug = $base ?: Str::random(6);
            $i = 1;
            while (InvoiceStatus::where('slug', $slug)->exists()) {
                $slug = $base.'-'.$i++;
            }
            $data['slug'] = $slug;
        }

        $status = InvoiceStatus::create($data);

        if (! empty($data['sort_order'])) {
            $status->moveToPosition((int) $data['sort_order']);
        }

        return redirect()->route('invoice-statuses.index')->with('success', 'Статус счета создан.');
    }

    public function edit(InvoiceStatus $invoiceStatus)
    {
        return view('admin.invoice_statuses.edit', compact('invoiceStatus'));
    }

    public function update(Request $request, InvoiceStatus $invoiceStatus)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:invoice_statuses,slug,'.$invoiceStatus->id,
            'sort_order' => 'nullable|integer|min:1',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name'] ?? $invoiceStatus->name);
        }

        $invoiceStatus->update($data);

        if (isset($data['sort_order']) && (int) $data['sort_order'] !== (int) $invoiceStatus->sort_order) {
            $invoiceStatus->moveToPosition((int) $data['sort_order']);
        }

        return redirect()->route('invoice-statuses.index')->with('success', 'Статус счета обновлён.');
    }

    public function destroy(InvoiceStatus $invoiceStatus)
    {
        InvoiceStatus::where('sort_order', '>', $invoiceStatus->sort_order)->decrement('sort_order');
        $invoiceStatus->delete();

        return redirect()->route('invoice-statuses.index')->with('success', 'Статус счета удалён.');
    }

    public function reorder(Request $request)
    {
        $data = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:invoice_statuses,id',
        ]);

        foreach ($data['order'] as $index => $id) {
            InvoiceStatus::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }
}
