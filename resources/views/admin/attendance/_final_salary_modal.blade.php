{{-- Модальное окно для полной выплаты зарплаты --}}
<div id="finalSalaryModal-{{ $report->id }}" class="fixed inset-0 z-50 hidden overflow-y-auto">
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>

    {{-- Modal Content --}}
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-lg transform rounded-lg bg-white shadow-xl transition-all">
            {{-- Header --}}
            <div class="flex items-center justify-between border-b px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-900">Полная выплата ЗП: {{ $report->user->name }}</h3>
                <button type="button" class="close-final-salary-modal text-gray-400 hover:text-gray-600"
                    data-modal-id="finalSalaryModal-{{ $report->id }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            {{-- Form --}}
            <form class="final-salary-form" data-report-id="{{ $report->id }}">
                @csrf
                <input type="hidden" name="salary_report_id" value="{{ $report->id }}">
                <input type="hidden" name="salary_recipient" value="{{ $report->user_id }}">

                <div class="space-y-4 px-6 py-4">
                    {{-- Сотрудник (readonly) --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Сотрудник</label>
                        <input type="text" value="{{ $report->user->name }}" readonly
                            class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm cursor-not-allowed" />
                        <p class="mt-1 text-xs text-gray-500">Получатель определяется автоматически из табеля</p>
                    </div>

                    {{-- Дата --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Дата расхода</label>
                        <input name="expense_date" type="datetime-local" required
                            value="{{ now()->format('Y-m-d\TH:i') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>

                    {{-- Сумма --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Сумма зарплаты</label>
                        <input name="amount" type="number" step="0.01" min="0.01" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="0.00" value="{{ $report->remaining_amount ?? '' }}" />
                    </div>

                    {{-- Зарплатная категория --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Категория</label>
                        <select name="expense_category_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach ($salaryCategories ?? [] as $cat)
                                <option value="{{ $cat->id }}" @if ($loop->first) selected @endif>
                                    {{ $cat->title }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Только категории с пометкой «ЗП»</p>
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
                            placeholder="Зарплата за {{ \Carbon\Carbon::parse($report->month)->translatedFormat('F Y') }}"></textarea>
                    </div>

                    {{-- Файлы --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Прикрепить документы (макс. 5 МБ
                            каждый)</label>
                        <input name="documents[]" type="file" multiple
                            accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:rounded file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-blue-700 hover:file:bg-blue-100" />
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end space-x-2 border-t bg-gray-50 px-6 py-4">
                    <button type="button"
                        class="close-final-salary-modal rounded bg-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-300"
                        data-modal-id="finalSalaryModal-{{ $report->id }}">
                        Отмена
                    </button>
                    <button type="submit"
                        class="rounded bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                        Сохранить
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Открытие модального окна
        const openBtn = document.getElementById('openAdvanceModal-{{ $report->id }}');
        const modal = document.getElementById('finalSalaryModal-{{ $report->id }}');

        if (openBtn && modal) {
            openBtn.addEventListener('click', function() {
                modal.classList.remove('hidden');
            });
        }

        // Закрытие модального окна
        const closeButtons = modal.querySelectorAll('.close-final-salary-modal');
        closeButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const modalId = this.getAttribute('data-modal-id');
                document.getElementById(modalId).classList.add('hidden');
            });
        });

        // Отправка формы
        const form = modal.querySelector('.final-salary-form');
        if (form) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                submitBtn.textContent = 'Сохранение...';

                try {
                    const response = await fetch('{{ route('expenses.store-final-salary') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        alert('Зарплата успешно выплачена!');
                        window.location.reload();
                    } else {
                        alert(data.message || 'Ошибка при сохранении');
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Сохранить';
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Произошла ошибка при сохранении');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Сохранить';
                }
            });
        }
    });
</script>
