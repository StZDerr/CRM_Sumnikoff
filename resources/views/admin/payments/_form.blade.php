{{-- resources/views/admin/payments/_form.blade.php --}}
@csrf
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="space-y-1">
        <x-input-label for="project_id" value="Проект" />

        <select id="project_id" name="project_id" required
            class="js-project-select block w-full rounded-md border-gray-300 shadow-sm
                   focus:border-indigo-500 focus:ring-indigo-500
                   @error('project_id') border-red-500 @enderror">
            <option value="">—</option>
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
        <input id="amount_input" type="number" name="amount" step="0.01" required class="w-full border rounded p-2"
            value="{{ old('amount', $payment->amount ?? '') }}" />
        <x-input-error :messages="$errors->get('amount')" />
    </div>

    <div>
        <label class="text-xs text-gray-500">НДС (5%)</label>
        <input id="vat_amount" name="vat_amount" type="number" step="0.01" class="w-full border rounded p-2"
            value="{{ old('vat_amount', isset($payment) ? number_format($payment->vat_amount ?? 0, 2, '.', '') : '0.00') }}" />
    </div>

    <div>
        <label class="text-xs text-gray-500">УСН (7%)</label>
        <input id="usn_amount" name="usn_amount" type="number" step="0.01" class="w-full border rounded p-2"
            value="{{ old('usn_amount', isset($payment) ? number_format($payment->usn_amount ?? 0, 2, '.', '') : '0.00') }}" />
    </div>

    <div>
        <label class="text-xs text-gray-500">Категория платежа</label>
        <select name="payment_category_id" class="w-full border rounded p-2">
            <option value="">— не указано —</option>
            @foreach ($paymentCategories as $cat)
                <option value="{{ $cat->id }}" @selected(old('payment_category_id', $payment->payment_category_id ?? '') == $cat->id)>{{ $cat->title }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('payment_category_id')" />
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
        <select id="invoice-status-select" name="invoice_status_id" class="w-full border rounded p-2">
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
        function initPaymentForm(container = document) {
            const invoiceStatusSelect = container.querySelector('#invoice-status-select');
            const invoiceSelect = container.querySelector('#invoice-select');
            const txInput = container.querySelector('#transaction_id');
            const projectSelect = container.querySelector('select[name="project_id"]');
            // Флаг, указывающий, сделал ли пользователь ручной выбор статуса счёта
            let manualInvoiceStatus = false;

            // ранее существующая логика
            function updateTxState() {
                const sel = invoiceSelect && invoiceSelect.selectedOptions[0];
                const invoiceSelected = invoiceSelect && invoiceSelect.value !== '';

                if (!invoiceSelected) {
                    if (txInput) {
                        txInput.readOnly = false;
                        txInput.classList.remove('bg-gray-50', 'opacity-80');
                    }
                    return;
                }

                const tx = sel?.dataset?.transaction || '';
                if (tx && txInput) {
                    txInput.value = tx;
                    txInput.readOnly = true;
                    txInput.classList.add('bg-gray-50', 'opacity-80');
                } else if (txInput) {
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
                const prev = invoiceSelect ? invoiceSelect.value : '';
                if (!invoiceSelect) return;

                // Client-side filter: exclude invoices whose status name contains 'оплач' (case-insensitive)
                const filtered = (items || []).filter(inv => {
                    const name = (inv.invoice_status_name || '').toString().toLowerCase();
                    return !name.includes('оплач');
                });

                invoiceSelect.innerHTML = '<option value="">— без счёта —</option>';
                filtered.forEach(inv => {
                    const opt = document.createElement('option');
                    opt.value = inv.id;
                    opt.dataset.transaction = inv.transaction_id ?? '';
                    opt.dataset.status = inv.invoice_status_id ?? '';
                    const issued = inv.issued_at ? inv.issued_at : '';
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
                const noteEl = container.querySelector('#invoice-status-note');
                const sel = invoiceSelect && invoiceSelect.selectedOptions[0];
                const invoiceSelected = invoiceSelect && invoiceSelect.value !== '';

                if (!invoiceSelected) {
                    // доступен для ручного выбора, но приглушён визуально
                    invoiceStatusSelect.classList.add('opacity-60');
                    invoiceStatusSelect.classList.remove('opacity-100');

                    if (!manualInvoiceStatus) {
                        // нет привязанного счёта и пользователь не выбирал статус вручную — очистим
                        invoiceStatusSelect.value = '';
                        if (noteEl) {
                            noteEl.textContent = 'Нет привязанного счёта — можно выбрать статус вручную.';
                            noteEl.classList.remove('text-yellow-600');
                            noteEl.classList.add('text-gray-500');
                        }
                    } else {
                        // оставим выбор пользователя
                        if (noteEl) {
                            noteEl.textContent = 'Выбран вручную.';
                            noteEl.classList.remove('text-yellow-600');
                            noteEl.classList.add('text-gray-500');
                        }
                    }
                    return;
                }

                // есть выбранный счёт — показываем его статус
                manualInvoiceStatus = false;
                invoiceStatusSelect.classList.remove('opacity-60');
                invoiceStatusSelect.classList.add('opacity-100');

                const status = String(sel?.dataset?.status || '');
                let found = false;
                for (const opt of invoiceStatusSelect.options) {
                    if (String(opt.value) === status) {
                        invoiceStatusSelect.value = opt.value;
                        found = true;
                        break;
                    }
                }

                if (!found) invoiceStatusSelect.value = '';

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
                    manualInvoiceStatus = false;
                    loadInvoicesForProject(e.target.value, false);
                });
            }

            // Слушаем смену селекта счетов
            if (invoiceSelect) {
                invoiceSelect.addEventListener('change', function() {
                    manualInvoiceStatus = false;
                    updateTxState();
                    updateInvoiceStatusState();
                });
            }

            // Слушаем ручное изменение статуса — показываем примечание "Выбран вручную"
            if (invoiceStatusSelect) {
                const noteEl = container.querySelector('#invoice-status-note');
                invoiceStatusSelect.addEventListener('change', function() {
                    manualInvoiceStatus = true;
                    const sel = invoiceSelect ? invoiceSelect.selectedOptions[0] : undefined;
                    const invoiceStatusFromInvoice = String(sel?.dataset?.status || '');
                    const current = String(invoiceStatusSelect.value || '');
                    if (current && invoiceStatusFromInvoice && current !== invoiceStatusFromInvoice) {
                        if (noteEl) {
                            noteEl.textContent = 'Выбран вручную (изменение не сохраняется автоматически)';
                            noteEl.classList.add('text-yellow-600');
                        }
                    } else {
                        if (noteEl) {
                            noteEl.textContent = 'Выбран вручную.';
                            noteEl.classList.remove('text-yellow-600');
                            noteEl.classList.add('text-gray-500');
                        }
                    }
                });
            }

            // Инициалная инициализация tx state и статуса счёта
            updateTxState();
            updateInvoiceStatusState();

            // --- TAXS (VAT 5% / USN 7%) realtime calculation ---
            const amountInput = container.querySelector('#amount_input');
            const vatInput = container.querySelector('#vat_amount');
            const usnInput = container.querySelector('#usn_amount');

            function formatNumber(val) {
                return Number(val).toLocaleString('ru-RU', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            // Manual input flags so user edits are not overwritten by auto-calculation
            let vatManual = false;
            let usnManual = false;

            function parseNumericInput(v) {
                if (v === undefined || v === null || v === '') return 0;
                return parseFloat(String(v).replace(/\s+/g, '').replace(',', '.')) || 0;
            }

            function recalcTaxes() {
                const val = parseNumericInput(amountInput?.value || 0);
                const vat = Math.round(val * 0.05 * 100) / 100;
                const usn = Math.round(val * 0.07 * 100) / 100;
                if (vatInput && !vatManual) vatInput.value = vat.toFixed(2);
                if (usnInput && !usnManual) usnInput.value = usn.toFixed(2);
            }

            if (vatInput) {
                vatInput.addEventListener('input', () => {
                    vatManual = true;
                });
                vatInput.addEventListener('blur', () => {
                    vatInput.value = parseNumericInput(vatInput.value).toFixed(2);
                });
            }
            if (usnInput) {
                usnInput.addEventListener('input', () => {
                    usnManual = true;
                });
                usnInput.addEventListener('blur', () => {
                    usnInput.value = parseNumericInput(usnInput.value).toFixed(2);
                });
            }

            if (amountInput) {
                amountInput.addEventListener('input', recalcTaxes);
                // initial
                recalcTaxes();
            }
        }

        // auto-init on full page load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                initPaymentForm(document);
            });
        } else {
            initPaymentForm(document);
        }
    </script>
@endonce
