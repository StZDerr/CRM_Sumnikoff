{{-- resources/views/admin/payments/_form.blade.php --}}
@csrf
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="text-xs text-gray-500">Проект</label>
        <select name="project_id" required class="w-full border rounded p-2">

            @foreach ($projects as $proj)
                <option value="{{ $proj->id }}" @selected((string) old('project_id', $payment->project_id ?? ($selectedProjectId ?? '')) === (string) $proj->id)>
                    {{ $proj->title ?? ($proj->name_short ?? $proj->name_full) }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('project_id')" />
    </div>

    <div>
        <label class="text-xs text-gray-500">Сумма</label>
        <input type="number" name="amount" step="0.01" required class="w-full border rounded p-2"
            value="{{ old('amount', $payment->amount ?? '') }}" />
        <x-input-error :messages="$errors->get('amount')" />
    </div>

    <div>
        <label class="text-xs text-gray-500">Дата оплаты</label>
        <input type="datetime-local" name="payment_date" class="w-full border rounded p-2"
            value="{{ old('payment_date', isset($payment) && $payment->payment_date ? $payment->payment_date->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}" />
        <x-input-error :messages="$errors->get('payment_date')" />
    </div>

    <div>
        <label class="text-xs text-gray-500">Способ оплаты</label>
        <select name="payment_method_id" class="w-full border rounded p-2">
            <option value="">— не указано —</option>
            @foreach ($paymentMethods as $pm)
                <option value="{{ $pm->id }}" @selected(old('payment_method_id', $payment->payment_method_id ?? '') == $pm->id)>{{ $pm->title }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('payment_method_id')" />
    </div>

    <div>
        <label class="text-xs text-gray-500">Оплаченный счёт (необязательно)</label>
        <select id="invoice-select" name="invoice_id" class="w-full border rounded p-2">
            <option value="">— без счёта —</option>
            @foreach ($invoices as $inv)
                <option value="{{ $inv->id }}" data-transaction="{{ e($inv->transaction_id) }}"
                    @selected(old('invoice_id', $payment->invoice_id ?? '') == $inv->id)>
                    {{ $inv->number }} —
                    {{ $inv->issued_at->format('Y-m-d') }} ({{ number_format($inv->amount, 2) }})</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('invoice_id')" />
    </div>

    <div class="md:col-span-2">
        <label class="text-xs text-gray-500">Номер транзакции</label>
        <input id="transaction_id" type="text" name="transaction_id" class="w-full border rounded p-2"
            value="{{ old('transaction_id', $payment->transaction_id ?? '') }}" />
        <x-input-error :messages="$errors->get('transaction_id')" />
    </div>

    <div class="md:col-span-2">
        <label class="text-xs text-gray-500">Примечание</label>
        <textarea name="note" rows="3" class="w-full border rounded p-2">{{ old('note', $payment->note ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('note')" />
    </div>
</div>

@once
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const invoiceSelect = document.getElementById('invoice-select');
            const txInput = document.getElementById('transaction_id');
            if (!invoiceSelect || !txInput) return;

            function updateTxState() {
                const sel = invoiceSelect.selectedOptions[0];
                const invoiceSelected = invoiceSelect.value !== '';

                if (!invoiceSelected) {
                    txInput.readOnly = false;
                    txInput.classList.remove('bg-gray-50', 'opacity-80');
                    return;
                }

                const tx = sel?.dataset?.transaction || '';
                // Если в счёте есть номер транзакции — подставим и зафиксируем поле
                if (tx) {
                    txInput.value = tx;
                    txInput.readOnly = true;
                    txInput.classList.add('bg-gray-50', 'opacity-80');
                } else {
                    // счёт без транзации — разрешаем ввод/редактирование вручную; не подставляем номер счёта
                    txInput.readOnly = false;
                    txInput.classList.remove('bg-gray-50', 'opacity-80');
                }
            }

            // Инициализация при загрузке (если открыт edit и счет выбран)
            updateTxState();

            // При смене селекта
            invoiceSelect.addEventListener('change', updateTxState);
        });
    </script>
@endonce
