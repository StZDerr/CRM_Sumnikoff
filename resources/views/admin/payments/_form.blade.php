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
        <label class="text-xs text-gray-500">Банковский счёт (куда оплата)</label>
        <select name="bank_account_id" class="w-full border rounded p-2">
            <option value="">— не указан —</option>
            @foreach ($bankAccounts as $b)
                <option value="{{ $b->id }}" @selected(old('bank_account_id', $payment->bank_account_id ?? '') == $b->id)>{{ $b->display_name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('bank_account_id')" />
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
        <label class="text-xs text-gray-500">Оплаченный счёт (если выставляли)</label>
        <select id="invoice-select" name="invoice_id" class="w-full border rounded p-2">
            <option value="">— без счёта —</option>
            @foreach ($invoices as $inv)
                <option value="{{ $inv->id }}" data-transaction="{{ e($inv->transaction_id) }}"
                    data-status="{{ $inv->invoice_status_id ?? '' }}" @selected(old('invoice_id', $payment->invoice_id ?? '') == $inv->id)>
                    {{ $inv->number }} — {{ $inv->issued_at->format('Y-m-d') }}
                    ({{ number_format($inv->amount, 2) }})
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('invoice_id')" />
    </div>

    <div>
        <label class="text-xs text-gray-500">Статус счёта</label>
        <select id="invoice-status-select" name="invoice_status_id" class="w-full border rounded p-2" disabled>
            <option value="">— без статуса —</option>
            @foreach ($invoiceStatuses ?? \App\Models\InvoiceStatus::ordered()->get() as $s)
                <option value="{{ $s->id }}" @selected(old('invoice_status_id', $payment->invoice_status_id ?? '') == $s->id)>{{ $s->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('invoice_status_id')" />
        <div id="invoice-status-note" class="text-xs text-gray-500 mt-1"></div>
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
            const invoiceStatusSelect = document.getElementById('invoice-status-select');
            const invoiceSelect = document.getElementById('invoice-select');
            const txInput = document.getElementById('transaction_id');
            const projectSelect = document.querySelector('select[name="project_id"]');

            // ранее существующая логика
            function updateTxState() {
                const sel = invoiceSelect.selectedOptions[0];
                const invoiceSelected = invoiceSelect.value !== '';

                if (!invoiceSelected) {
                    txInput.readOnly = false;
                    txInput.classList.remove('bg-gray-50', 'opacity-80');
                    return;
                }

                const tx = sel?.dataset?.transaction || '';
                if (tx) {
                    txInput.value = tx;
                    txInput.readOnly = true;
                    txInput.classList.add('bg-gray-50', 'opacity-80');
                } else {
                    txInput.readOnly = false;
                    txInput.classList.remove('bg-gray-50', 'opacity-80');
                }
            }

            // --- Новая логика: загрузка счетов по проекту ---
            const invoicesCache = {};

            const invoicesUrlTemplate = "{{ route('projects.invoices', ['project' => 'PROJECT_ID']) }}";

            async function loadInvoicesForProject(projectId, preserveSelected = true) {
                if (!invoiceSelect) return;
                // пустой селект, если нет projectId
                if (!projectId) {
                    invoiceSelect.innerHTML = '<option value="">— без счёта —</option>';
                    updateTxState();
                    return;
                }

                // использовать кэш
                if (invoicesCache[projectId]) {
                    renderInvoices(invoicesCache[projectId], preserveSelected);
                    return;
                }

                const url = invoicesUrlTemplate.replace('PROJECT_ID', projectId);
                try {
                    const res = await fetch(url, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    if (!res.ok) throw new Error('Network response not OK');
                    const json = await res.json();
                    invoicesCache[projectId] = json;
                    renderInvoices(json, preserveSelected);
                } catch (err) {
                    console.error('Не удалось загрузить счета проекта:', err);
                    invoiceSelect.innerHTML = '<option value="">— не удалось загрузить —</option>';
                    updateTxState();
                }
            }

            function formatMoney(val) {
                return Number(val).toLocaleString('ru-RU', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) + ' ₽';
            }

            function renderInvoices(items, preserveSelected = true) {
                const prev = invoiceSelect.value;
                invoiceSelect.innerHTML = '<option value="">— без счёта —</option>';
                items.forEach(inv => {
                    const opt = document.createElement('option');
                    opt.value = inv.id;
                    opt.dataset.transaction = inv.transaction_id ?? '';
                    opt.dataset.status = inv.invoice_status_id ?? '';
                    const issued = inv.issued_at ? (new Date(inv.issued_at)).toISOString().slice(0, 10) :
                        '';
                    opt.textContent = `${inv.number} — ${issued} (${formatMoney(inv.amount)})`;
                    invoiceSelect.appendChild(opt);
                });

                if (preserveSelected && prev) {
                    const found = Array.from(invoiceSelect.options).some(o => o.value === prev);
                    if (found) invoiceSelect.value = prev;
                }

                // обновим состояние transaction input
                updateTxState();
                // обновим состояние селекта статуса в зависимости от выбранного счета
                updateInvoiceStatusState();
            }

            function updateInvoiceStatusState() {
                if (!invoiceStatusSelect) return;
                const noteEl = document.getElementById('invoice-status-note');
                const sel = invoiceSelect.selectedOptions[0];
                const invoiceSelected = invoiceSelect.value !== '';

                if (!invoiceSelected) {
                    invoiceStatusSelect.value = '';
                    invoiceStatusSelect.disabled = true;
                    invoiceStatusSelect.classList.add('opacity-60');
                    if (noteEl) noteEl.textContent = '';
                    return;
                }

                const status = String(sel?.dataset?.status || '');
                invoiceStatusSelect.disabled = false;
                invoiceStatusSelect.classList.remove('opacity-60');

                // Найдем опцию со значением status (строковое сравнение)
                let found = false;
                for (const opt of invoiceStatusSelect.options) {
                    if (String(opt.value) === status) {
                        invoiceStatusSelect.value = opt.value;
                        found = true;
                        break;
                    }
                }

                // Если не нашли — оставим пустым
                if (!found) invoiceStatusSelect.value = '';

                // Покажем подсказку с текущим статусом из счёта
                if (noteEl) {
                    const selOpt = invoiceStatusSelect.selectedOptions[0];
                    if (selOpt && selOpt.value) {
                        noteEl.textContent = 'Статус из счёта: ' + selOpt.text;
                        noteEl.classList.remove('text-yellow-600');
                        noteEl.classList.add('text-gray-500');
                    } else {
                        noteEl.textContent = 'У счёта статус не указан.';
                        noteEl.classList.remove('text-yellow-600');
                        noteEl.classList.add('text-gray-500');
                    }
                }
            }

            // Инициализация: при загрузке формы подгрузим счета проекта, если проект выбран
            if (projectSelect && projectSelect.value) {
                loadInvoicesForProject(projectSelect.value, true);
            }

            // Обработка смены проекта
            if (projectSelect) {
                projectSelect.addEventListener('change', function(e) {
                    // при смене проекта очищаем выбранный счёт
                    loadInvoicesForProject(e.target.value, false);
                });
            }

            // Слушаем смену селекта счетов
            if (invoiceSelect) {
                invoiceSelect.addEventListener('change', function() {
                    updateTxState();
                    updateInvoiceStatusState();
                });
            }

            // Слушаем ручное изменение статуса — показываем примечание "Выбран вручную"
            if (invoiceStatusSelect) {
                const noteEl = document.getElementById('invoice-status-note');
                invoiceStatusSelect.addEventListener('change', function() {
                    const sel = invoiceSelect.selectedOptions[0];
                    const invoiceStatusFromInvoice = String(sel?.dataset?.status || '');
                    const current = String(invoiceStatusSelect.value || '');
                    if (current && invoiceStatusFromInvoice && current !== invoiceStatusFromInvoice) {
                        if (noteEl) {
                            noteEl.textContent = 'Выбран вручную (изменение не сохраняется автоматически)';
                            noteEl.classList.add('text-yellow-600');
                        }
                    } else {
                        // вернуть стандартную подсказку
                        updateInvoiceStatusState();
                    }
                });
            }

            // Инициалная инициализация tx state и статуса счёта
            updateTxState();
            updateInvoiceStatusState();
        });
    </script>
@endonce
