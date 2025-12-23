{{-- resources/views/admin/invoices/_form.blade.php --}}
@csrf
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="text-xs text-gray-500">Номер счета</label>
        <input name="number" value="{{ old('number', $invoice->number ?? '') }}" required
            class="w-full border rounded p-2" />
        <x-input-error :messages="$errors->get('number')" />
    </div>

    <div>
        <label class="text-xs text-gray-500">Дата выставления</label>
        <input type="date" name="issued_at"
            value="{{ old('issued_at', isset($invoice) ? $invoice->issued_at->format('Y-m-d') : now()->format('Y-m-d')) }}"
            required class="w-full border rounded p-2" />
        <x-input-error :messages="$errors->get('issued_at')" />
    </div>

    <div>
        <label class="text-xs text-gray-500">Проект</label>
        <select name="project_id" required class="w-full border rounded p-2">
            <option value="">— выберите —</option>
            @foreach ($projects as $project)
                <option value="{{ $project->id }}" @selected((string) old('project_id', $invoice->project_id ?? ($selectedProjectId ?? '')) === (string) $project->id)>
                    {{ $project->title ?? ($project->name_short ?? $project->name_full) }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('project_id')" />
    </div>

    <div>
        <label class="text-xs text-gray-500">Номер договора</label>
        <input name="contract_number" value="{{ old('contract_number', $invoice->contract_number ?? '') }}"
            class="w-full border rounded p-2" />
        <x-input-error :messages="$errors->get('contract_number')" />
    </div>

    <div>
        <label class="text-xs text-gray-500">Сумма</label>
        <input type="number" step="0.01" name="amount" value="{{ old('amount', $invoice->amount ?? '') }}" required
            class="w-full border rounded p-2" />
        <x-input-error :messages="$errors->get('amount')" />
    </div>

    <div>
        <label class="text-xs text-gray-500">Назначение платежа</label>
        <select name="payment_method_id" class="w-full border rounded p-2">
            <option value="">— не указано —</option>
            @foreach ($paymentMethods as $pm)
                <option value="{{ $pm->id }}" @selected(old('payment_method_id', $invoice->payment_method_id ?? '') == $pm->id)>{{ $pm->title }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('payment_method_id')" />
    </div>

    @php
        $invoiceStatuses = $invoiceStatuses ?? \App\Models\InvoiceStatus::ordered()->get();
    @endphp

    <div>
        <label class="text-xs text-gray-500">Статус счёта</label>
        <select name="invoice_status_id" class="w-full border rounded p-2">
            <option value="">— без статуса —</option>
            @foreach ($invoiceStatuses as $s)
                <option value="{{ $s->id }}" @selected((string) old('invoice_status_id', $invoice->invoice_status_id ?? '') === (string) $s->id)>{{ $s->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('invoice_status_id')" />
    </div>

    <div class="md:col-span-2">
        <label class="text-xs text-gray-500">Вложения (PDF / изображения) — можно несколько</label>
        <input type="file" name="attachments[]" multiple accept=".pdf,image/*" class="w-full mt-1" />
        <x-input-error :messages="$errors->get('attachments')" />
        <x-input-error :messages="$errors->get('attachments.*')" />
    </div>

    <div class="md:col-span-2">
        <label class="text-xs text-gray-500">Номер транзакции</label>
        <input name="transaction_id" value="{{ old('transaction_id', $invoice->transaction_id ?? '') }}"
            class="w-full border rounded p-2" />
    </div>
</div>
