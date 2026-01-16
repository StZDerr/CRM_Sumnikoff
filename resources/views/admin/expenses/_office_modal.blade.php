{{-- Модальное окно для создания офисного расхода --}}
<div id="officeExpenseModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    {{-- Backdrop --}}
    <div id="officeExpenseBackdrop" class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>

    {{-- Modal Content --}}
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-lg transform rounded-lg bg-white shadow-xl transition-all">
            {{-- Header --}}
            <div class="flex items-center justify-between border-b px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-900">Новый расход (Офис)</h3>
                <button type="button" id="closeOfficeExpenseModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            {{-- Form --}}
            <form id="officeExpenseForm" action="{{ route('expenses.store-office') }}" method="POST"
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

                    {{-- Офисная категория --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Категория офиса</label>
                        <select name="expense_category_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach ($officeCategories ?? [] as $cat)
                                <option value="{{ $cat->id }}" @if ($loop->first) selected @endif>
                                    {{ $cat->title }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Только категории с пометкой «Офис»</p>
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

                    {{-- Описание --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Примечание</label>
                        <textarea name="description" rows="2"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Опишите расход..."></textarea>
                    </div>

                    {{-- Файл --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Документ (чек/счёт)</label>
                        <input type="file" name="documents[]" multiple
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:rounded file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100" />
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex justify-end gap-3 border-t px-6 py-4">
                    <button type="button" id="cancelOfficeExpense"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Отмена
                    </button>
                    <button type="submit"
                        class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                        Сохранить
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('officeExpenseModal');
        const openBtn = document.getElementById('openOfficeExpenseBtn');
        const closeBtn = document.getElementById('closeOfficeExpenseModal');
        const cancelBtn = document.getElementById('cancelOfficeExpense');
        const backdrop = document.getElementById('officeExpenseBackdrop');
        const form = document.getElementById('officeExpenseForm');

        function openModal() {
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeModal() {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        if (openBtn) openBtn.addEventListener('click', openModal);
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
        if (backdrop) backdrop.addEventListener('click', closeModal);

        // AJAX submit
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
                            // Refresh page or show success message
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
