{{-- Модальное окно для создания расхода домены/хостинг --}}
<div id="domainHostingExpenseModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    {{-- Backdrop --}}
    <div id="domainHostingExpenseBackdrop" class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>

    {{-- Modal Content --}}
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-lg transform rounded-lg bg-white shadow-xl transition-all">
            {{-- Header --}}
            <div class="flex items-center justify-between border-b px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-900">Оплата доменов / хостинга</h3>
                <button type="button" id="closeDomainHostingExpenseModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            {{-- Form --}}
            <form id="domainHostingExpenseForm" action="{{ route('expenses.store-domain-hosting') }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                <div class="space-y-4 px-6 py-4">
                    {{-- Дата --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Дата расхода</label>
                        <input name="expense_date" type="datetime-local" required
                            value="{{ now()->format('Y-m-d\TH:i') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>

                    {{-- Сумма --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Сумма</label>
                        <input name="amount" type="number" step="0.01" min="0.01" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="0.00" />
                    </div>

                    {{-- Категория (домены/хостинг) --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Категория домены/хостинг</label>
                        <select name="expense_category_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach ($domainHostingCategories ?? [] as $cat)
                                <option value="{{ $cat->id }}" @if ($loop->first) selected @endif>
                                    {{ $cat->title }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Только категории с пометкой «Домены/Хостинг»</p>
                    </div>

                    {{-- Домен --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Домен</label>
                        <select name="domain_id" id="domainHostingDomainSelect" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">— выбрать домен —</option>
                            @foreach ($domainsForModal ?? ($domains ?? []) as $d)
                                <option value="{{ $d->id }}"
                                    data-expires-at="{{ optional($d->expires_at)->format('Y-m-d') }}">
                                    {{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">До какого срока продлевается</label>
                        <div class="mt-1 flex items-center gap-2">
                            <input type="date" name="renew_until" id="domainHostingRenewUntil"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            <button type="button" id="domainHostingRenewYear"
                                class="whitespace-nowrap rounded-md border border-indigo-200 bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-100">
                                На год
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Кнопка установит +1 год от даты окончания выбранного
                            домена.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        {{-- Метод оплаты --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Метод оплаты</label>
                            <select name="payment_method_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">— выбрать —</option>
                                @foreach ($paymentMethods ?? [] as $pm)
                                    <option value="{{ $pm->id }}">{{ $pm->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Банковский счёт --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Банковский счёт</label>
                            <select name="bank_account_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">— выбрать —</option>
                                @foreach ($bankAccounts ?? [] as $ba)
                                    <option value="{{ $ba->id }}">{{ $ba->display_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Номер документа --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Номер документа (счёт/чек)</label>
                        <input name="document_number" type="text"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>

                    {{-- Статус --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Статус</label>
                        <select name="status" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach (\App\Models\Expense::STATUSES as $key => $meta)
                                <option value="{{ $key }}" @if ($key === 'paid') selected @endif>
                                    {{ $meta['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Описание --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Назначение / Комментарий</label>
                        <textarea name="description" rows="2"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Опишите расход..."></textarea>
                    </div>

                    {{-- Файл --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Документы (чек/счёт)</label>
                        <input type="file" name="documents[]" multiple
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:rounded file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100" />
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex justify-end gap-3 border-t px-6 py-4">
                    <button type="button" id="cancelDomainHostingExpense"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Отмена
                    </button>
                    <button type="submit"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Сохранить
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('domainHostingExpenseModal');
        const openBtn = document.getElementById('openDomainHostingCategoriesBtn');
        const closeBtn = document.getElementById('closeDomainHostingExpenseModal');
        const cancelBtn = document.getElementById('cancelDomainHostingExpense');
        const backdrop = document.getElementById('domainHostingExpenseBackdrop');
        const form = document.getElementById('domainHostingExpenseForm');
        const domainSelect = document.getElementById('domainHostingDomainSelect');
        const renewUntilInput = document.getElementById('domainHostingRenewUntil');
        const renewYearBtn = document.getElementById('domainHostingRenewYear');
        const amountInput = form ? form.querySelector('input[name="amount"]') : null;

        function openModal(trigger) {
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');

            if (trigger) {
                const domainId = trigger.dataset.domainId || '';
                const renewPrice = trigger.dataset.renewPrice;
                const expiresAt = trigger.dataset.expiresAt || '';

                if (domainId && domainSelect) {
                    domainSelect.value = domainId;
                }

                if (amountInput && renewPrice !== undefined && renewPrice !== null && renewPrice !== '') {
                    amountInput.value = renewPrice;
                }

                if (renewUntilInput) {
                    const opt = domainSelect ? domainSelect.selectedOptions[0] : null;
                    const baseExpires = expiresAt || (opt ? opt.dataset.expiresAt : '') || '';
                    renewUntilInput.value = addOneYear(baseExpires);
                }
            }
        }

        function closeModal() {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        if (openBtn) {
            openBtn.addEventListener('click', function() {
                openModal(openBtn);
            });
        }
        document.querySelectorAll('[data-domain-hosting-open]').forEach((btn) => {
            btn.addEventListener('click', function() {
                openModal(btn);
            });
        });
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
        if (backdrop) backdrop.addEventListener('click', closeModal);

        function addOneYear(dateStr) {
            const base = dateStr ? new Date(dateStr + 'T00:00:00') : new Date();
            const d = new Date(base);
            d.setFullYear(d.getFullYear() + 1);
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${y}-${m}-${day}`;
        }

        if (renewYearBtn) {
            renewYearBtn.addEventListener('click', function() {
                const opt = domainSelect ? domainSelect.selectedOptions[0] : null;
                const expiresAt = opt ? opt.dataset.expiresAt : '';
                if (renewUntilInput) {
                    renewUntilInput.value = addOneYear(expiresAt);
                }
            });
        }

        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(form);

                fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            closeModal();
                            window.location.reload();
                        } else {
                            alert(data.message || 'Ошибка сохранения');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Ошибка сервера');
                    });
            });
        }
    });
</script>
