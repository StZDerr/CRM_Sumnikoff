@csrf

<div class="grid grid-cols-2 gap-4">
    <div class="col-span-2">
        <x-input-label for="title" :value="'Название проекта'" />
        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', isset($project) ? $project->title : '')" required
            autofocus />
        <x-input-error :messages="$errors->get('title')" class="mt-2" />
    </div>

    <div class="space-y-1">
        <x-input-label for="organization_id" value="Организация" />

        <div class="flex gap-2">
            <select id="organization_id" name="organization_id"
                class="js-org-select block w-full rounded-md border-gray-300 shadow-sm
                   focus:border-indigo-500 focus:ring-indigo-500
                   @error('organization_id') border-red-500 @enderror">
                <option value="">—</option>
                @foreach ($organizations ?? [] as $id => $name)
                    <option value="{{ $id }}" @selected((string) old('organization_id', $project->organization_id ?? '') === (string) $id)>
                        {{ $name }}
                    </option>
                @endforeach
            </select>

            <button type="button" id="add-organization-btn"
                class="inline-flex items-center px-3 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 transition whitespace-nowrap"
                title="Добавить организацию">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Новая
            </button>
        </div>

        <x-input-error :messages="$errors->get('organization_id')" />
    </div>

    <div class="space-y-1">
        <x-input-label for="marketer_id" value="Маркетолог" />

        <select id="marketer_id" name="marketer_id"
            class="block w-full rounded-md border-gray-300 shadow-sm
               focus:border-indigo-500 focus:ring-indigo-500
               @error('marketer_id') border-red-500 @enderror">
            <option value="">—</option>
            @foreach ($marketers ?? [] as $id => $name)
                <option value="{{ $id }}" @selected((string) old('marketer_id', $project->marketer_id ?? '') === (string) $id)>
                    {{ $name }}
                </option>
            @endforeach
        </select>

        <x-input-error :messages="$errors->get('marketer_id')" />
    </div>

    <div>
        <x-input-label for="importance_id" :value="'Важность'" />
        <select id="importance_id" name="importance_id" class="mt-1 block w-full rounded border px-3 py-2">
            <option value="">—</option>
            @foreach ($importances ?? [] as $id => $name)
                <option value="{{ $id }}" @selected((string) old('importance_id', isset($project) ? $project->importance_id : '') === (string) $id)>{{ $name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('importance_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="payment_method_id" :value="'Тип оплаты'" />
        <select id="payment_method_id" name="payment_method_id" class="mt-1 block w-full rounded border px-3 py-2">
            <option value="">—</option>
            @foreach ($paymentMethods ?? [] as $id => $title)
                <option value="{{ $id }}" @selected((string) old('payment_method_id', isset($project) ? $project->payment_method_id : '') === (string) $id)>{{ $title }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('payment_method_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="payment_type" :value="'Тип расчёта'" />
        <select id="payment_type" name="payment_type" class="mt-1 block w-full rounded border px-3 py-2">
            <option value="paid" @selected((string) old('payment_type', isset($project) ? $project->payment_type : 'paid') === 'paid')>Платят</option>
            <option value="barter" @selected((string) old('payment_type', isset($project) ? $project->payment_type : 'paid') === 'barter')>Бартер</option>
            <option value="own" @selected((string) old('payment_type', isset($project) ? $project->payment_type : 'paid') === 'own')>Свои проекты</option>
        </select>
        <x-input-error :messages="$errors->get('payment_type')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="contract_amount" :value="'Сумма договора'" />
        <x-text-input id="contract_amount" name="contract_amount" type="number" step="0.01"
            class="mt-1 block w-full" :value="old('contract_amount', isset($project) ? $project->contract_amount : '')" />
        <x-input-error :messages="$errors->get('contract_amount')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="contract_date" :value="'Дата заключения договора'" />
        <x-text-input id="contract_date" name="contract_date" type="date" class="mt-1 block w-48"
            :value="old(
                'contract_date',
                isset($project) && $project->contract_date
                    ? \Illuminate\Support\Carbon::make($project->contract_date)->format('Y-m-d')
                    : '',
            )" />
        <x-input-error :messages="$errors->get('contract_date')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="city" :value="'Город'" />
        <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" :value="old('city', isset($project) ? $project->city : '')" />
        <x-input-error :messages="$errors->get('city')" class="mt-2" />
    </div>

    {{-- <div>
        <x-input-label for="payment_due_day" :value="'Срок оплаты (день месяца)'" />
        <x-text-input id="payment_due_day" name="payment_due_day" type="number" min="1" max="31"
            class="mt-1 block w-40" :value="old('payment_due_day', isset($project) ? $project->payment_due_day : '')" />
        <x-input-error :messages="$errors->get('payment_due_day')" class="mt-2" />
    </div> --}}

    <div>
        <x-input-label for="closed_at" :value="'Дата закрытия'" />
        <x-text-input id="closed_at" name="closed_at" type="date" :value="old('closed_at', isset($project) && $project->closed_at ? $project->closed_at->format('Y-m-d') : '')" class="mt-1 block w-48" />
        <x-input-error :messages="$errors->get('closed_at')" class="mt-2" />
    </div>

    <!-- Stages: sortable list + selection -->
    <div class="col-span-2">
        <x-input-label :value="'Виды (перетаскивайте для порядка, клик чтобы выбрать)'" />
        <div id="stages-list" class="mt-2 rounded border bg-white/50 p-2">
            @foreach ($stages ?? [] as $id => $name)
                @php $selected = in_array($id, $currentStages ?? old('stages', [])); @endphp
                <div data-id="{{ $id }}"
                    class="stage-item flex items-center justify-between gap-3 px-3 py-2 mb-2 rounded bg-white/0 border hover:bg-gray-50 cursor-grab {{ $selected ? 'selected bg-indigo-50 border-indigo-200' : '' }}">
                    <div class="flex items-center gap-3">
                        <div class="drag-handle cursor-grab text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8 9h.01M8 15h.01M12 9h.01M12 15h.01M16 9h.01M16 15h.01" />
                            </svg>
                        </div>
                        <div class="text-sm">{{ $name }}</div>
                    </div>
                    <div>
                        <input type="checkbox" class="stage-checkbox" value="{{ $id }}"
                            @if ($selected) checked @endif />
                    </div>
                </div>
            @endforeach
        </div>
        <div class="text-xs text-gray-500 mt-2">Выбранные Виды и их порядок отправляются при сохранении.</div>
    </div>

    <div class="col-span-2">
        <x-input-label for="comment" :value="'Комментарий'" />
        <textarea id="comment" name="comment" rows="3" class="mt-1 block w-full rounded border px-3 py-2">{{ old('comment', isset($project) ? $project->comment : '') }}</textarea>
        <x-input-error :messages="$errors->get('comment')" class="mt-2" />
    </div>
</div>

<div class="flex items-center gap-3 mt-4">
    <x-primary-button>{{ $submit ?? 'Сохранить' }}</x-primary-button>
    <a href="{{ route('projects.index') }}"
        class="inline-flex items-center px-3 py-2 border rounded hover:bg-gray-50">Отмена</a>
</div>

<!-- Модальное окно: Добавление организации -->
<div id="organization-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" id="modal-overlay"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b">
                <h3 class="text-lg font-semibold">Новая организация</h3>
                <button type="button" id="close-modal-btn" class="text-gray-400 hover:text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div id="organization-form" class="p-6 space-y-4">
                <div id="org-form-errors"
                    class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded text-sm"></div>

                <!-- Тип организации (табы) -->
                <div class="mb-4">
                    <span class="block text-sm font-medium text-gray-700 mb-2">Тип организации</span>
                    <div class="flex rounded-md shadow-sm overflow-hidden w-full border border-gray-300">
                        <button type="button" data-type="individual" id="org-toggle-individual"
                            class="org-entity-toggle flex-1 px-4 py-2 text-center bg-white text-gray-700 font-medium hover:bg-gray-100 transition-colors">
                            Физ лицо
                        </button>
                        <button type="button" data-type="ip" id="org-toggle-ip"
                            class="org-entity-toggle flex-1 px-4 py-2 text-center bg-white text-gray-700 font-medium hover:bg-gray-100 transition-colors">
                            ИП
                        </button>
                        <button type="button" data-type="ooo" id="org-toggle-ooo"
                            class="org-entity-toggle flex-1 px-4 py-2 text-center bg-indigo-500 text-white font-medium hover:bg-indigo-600 transition-colors">
                            ООО
                        </button>
                    </div>
                    <input type="hidden" id="org_entity_type" value="ooo">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <!-- Сокращённое название -->
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Сокращённое название</label>
                        <input type="text" id="org_name_short"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <!-- Полное название -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Полное название <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="org_name_full"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <!-- Телефон -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Телефон</label>
                        <input type="text" id="org_phone"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="org_email"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <!-- ИНН (для ИП и ООО) -->
                    <div class="org-inn-field">
                        <label class="block text-sm font-medium text-gray-700">ИНН</label>
                        <input type="text" id="org_inn" maxlength="12"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <!-- ОГРНИП (только для ИП) -->
                    <div class="org-ogrnip-field">
                        <label class="block text-sm font-medium text-gray-700">ОГРНИП</label>
                        <input type="text" id="org_ogrnip"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <!-- КПП (только для ООО) -->
                    <div class="org-kpp-field">
                        <label class="block text-sm font-medium text-gray-700">КПП</label>
                        <input type="text" id="org_kpp" maxlength="9"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <!-- Юридический адрес (для ИП и ООО) -->
                    <div class="col-span-2 org-legal-address-field">
                        <label class="block text-sm font-medium text-gray-700">Юридический адрес</label>
                        <textarea id="org_legal_address" rows="2"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>

                    <!-- Фактический адрес -->
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Фактический адрес</label>
                        <textarea id="org_actual_address" rows="2"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>

                    <!-- Банковские реквизиты -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Расчётный счёт</label>
                        <input type="text" id="org_account_number"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Банк</label>
                        <input type="text" id="org_bank_name"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Корр. счёт</label>
                        <input type="text" id="org_corr_account"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">БИК</label>
                        <input type="text" id="org_bic"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <!-- Примечание -->
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Примечание</label>
                        <textarea id="org_notes" rows="2"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t">
                    <button type="button" id="cancel-org-btn"
                        class="px-4 py-2 border rounded-md text-gray-700 hover:bg-gray-50">
                        Отмена
                    </button>
                    <button type="button" id="save-org-btn"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50">
                        Сохранить
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JS: собираем stages[] в нужном порядке перед submit -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const list = document.getElementById('stages-list');
        if (!list) return;

        // Сделаем sortable через встроенное API (простейший drag)
        let dragEl = null;
        list.addEventListener('dragstart', (e) => {
            dragEl = e.target;
            e.dataTransfer.effectAllowed = 'move';
        }, true);
        list.addEventListener('dragover', (e) => {
            e.preventDefault();
            const target = e.target.closest('.stage-item');
            if (target && dragEl && target !== dragEl) {
                list.insertBefore(dragEl, target.nextSibling);
            }
        }, true);
        Array.from(list.querySelectorAll('.stage-item')).forEach(el => {
            el.setAttribute('draggable', 'true');
            const cb = el.querySelector('.stage-checkbox');

            // Когда чекбокс меняется (клик по самому чекбоксу), обновляем класс
            if (cb) {
                cb.addEventListener('change', () => {
                    el.classList.toggle('selected', cb.checked);
                });
            }

            // Клик по карточке: игнорируем клики по drag-handle и по самому чекбоксу
            el.addEventListener('click', (e) => {
                if (e.target.closest('.drag-handle')) return;
                if (e.target === cb || e.target.closest('.stage-checkbox')) return;

                if (cb) {
                    cb.checked = !cb.checked;
                    el.classList.toggle('selected', cb.checked);
                }
            });
        });

        // Перед отправкой формы - соберём выбранные в порядке DOM и добавим hidden inputs stages[]
        const form = list.closest('form');
        if (!form) return;
        form.addEventListener('submit', () => {
            // remove previous hidden inputs
            form.querySelectorAll('input[name="stages[]"]').forEach(n => n.remove());
            const items = list.querySelectorAll('.stage-item');
            items.forEach(item => {
                const cb = item.querySelector('.stage-checkbox');
                if (cb && cb.checked) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'stages[]';
                    input.value = cb.value;
                    form.appendChild(input);
                }
            });
        });
    });
</script>

<!-- JS: Модальное окно организации -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('organization-modal');
        const openBtn = document.getElementById('add-organization-btn');
        const closeBtn = document.getElementById('close-modal-btn');
        const cancelBtn = document.getElementById('cancel-org-btn');
        const overlay = document.getElementById('modal-overlay');
        const errorsDiv = document.getElementById('org-form-errors');
        const orgSelect = document.getElementById('organization_id');
        const saveBtn = document.getElementById('save-org-btn');

        // Табы типа организации
        const entityTypeInput = document.getElementById('org_entity_type');
        const innFieldEls = document.querySelectorAll('.org-inn-field');
        const ogrnipFieldEls = document.querySelectorAll('.org-ogrnip-field');
        const kppFieldEls = document.querySelectorAll('.org-kpp-field');
        const legalAddressFieldEls = document.querySelectorAll('.org-legal-address-field');
        const toggles = document.querySelectorAll('.org-entity-toggle');

        function setEntityType(type) {
            entityTypeInput.value = type;

            // Физ лицо: скрыть ИНН, КПП, ОГРНИП, юр. адрес
            // ИП: показать ИНН, ОГРНИП; скрыть КПП
            // ООО: показать ИНН, КПП; скрыть ОГРНИП
            innFieldEls.forEach(el => el.style.display = (type === 'ip' || type === 'ooo') ? '' : 'none');
            ogrnipFieldEls.forEach(el => el.style.display = (type === 'ip') ? '' : 'none');
            kppFieldEls.forEach(el => el.style.display = (type === 'ooo') ? '' : 'none');
            legalAddressFieldEls.forEach(el => el.style.display = (type === 'ip' || type === 'ooo') ? '' :
                'none');

            // Перекрашиваем кнопки
            toggles.forEach(btn => {
                if (btn.dataset.type === type) {
                    btn.classList.add('bg-indigo-500', 'text-white');
                    btn.classList.remove('bg-white', 'text-gray-700');
                } else {
                    btn.classList.add('bg-white', 'text-gray-700');
                    btn.classList.remove('bg-indigo-500', 'text-white');
                }
            });
        }

        toggles.forEach(btn => btn.addEventListener('click', () => setEntityType(btn.dataset.type)));

        function resetForm() {
            document.getElementById('org_name_full').value = '';
            document.getElementById('org_name_short').value = '';
            document.getElementById('org_inn').value = '';
            document.getElementById('org_ogrnip').value = '';
            document.getElementById('org_kpp').value = '';
            document.getElementById('org_phone').value = '';
            document.getElementById('org_email').value = '';
            document.getElementById('org_legal_address').value = '';
            document.getElementById('org_actual_address').value = '';
            document.getElementById('org_account_number').value = '';
            document.getElementById('org_bank_name').value = '';
            document.getElementById('org_corr_account').value = '';
            document.getElementById('org_bic').value = '';
            document.getElementById('org_notes').value = '';
            errorsDiv.classList.add('hidden');
            setEntityType('ooo');
        }

        function openModal() {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            resetForm();
            document.getElementById('org_name_short').focus();
        }

        function closeModal() {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }

        if (openBtn) {
            openBtn.addEventListener('click', openModal);
        }
        if (closeBtn) {
            closeBtn.addEventListener('click', closeModal);
        }
        if (cancelBtn) {
            cancelBtn.addEventListener('click', closeModal);
        }
        if (overlay) {
            overlay.addEventListener('click', closeModal);
        }

        // Закрытие по Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });

        // Отправка по клику на кнопку сохранения
        if (saveBtn) {
            saveBtn.addEventListener('click', async () => {
                // Проверяем обязательное поле
                const nameFullInput = document.getElementById('org_name_full');
                if (!nameFullInput.value.trim()) {
                    errorsDiv.textContent = 'Поле "Полное название" обязательно для заполнения';
                    errorsDiv.classList.remove('hidden');
                    nameFullInput.focus();
                    return;
                }

                saveBtn.disabled = true;
                saveBtn.textContent = 'Сохранение...';
                errorsDiv.classList.add('hidden');

                // Собираем данные вручную
                const formData = new FormData();
                const entityType = document.getElementById('org_entity_type').value;
                formData.append('entity_type', entityType);
                formData.append('name_full', document.getElementById('org_name_full').value);
                formData.append('name_short', document.getElementById('org_name_short').value);
                formData.append('phone', document.getElementById('org_phone').value);
                formData.append('email', document.getElementById('org_email').value);
                formData.append('actual_address', document.getElementById('org_actual_address')
                    .value);
                formData.append('account_number', document.getElementById('org_account_number')
                    .value);
                formData.append('bank_name', document.getElementById('org_bank_name').value);
                formData.append('corr_account', document.getElementById('org_corr_account').value);
                formData.append('bic', document.getElementById('org_bic').value);
                formData.append('notes', document.getElementById('org_notes').value);

                // Поля для ИП и ООО
                if (entityType === 'ip' || entityType === 'ooo') {
                    formData.append('inn', document.getElementById('org_inn').value);
                    formData.append('legal_address', document.getElementById('org_legal_address')
                        .value);
                }
                // ОГРНИП только для ИП
                if (entityType === 'ip') {
                    formData.append('ogrnip', document.getElementById('org_ogrnip').value);
                }
                // КПП только для ООО
                if (entityType === 'ooo') {
                    formData.append('kpp', document.getElementById('org_kpp').value);
                }

                try {
                    const response = await fetch('{{ route('organizations.store.ajax') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        // Добавляем новую организацию в select
                        const option = new Option(data.organization.name, data.organization.id,
                            true, true);
                        orgSelect.add(option);

                        // Если используется TomSelect — обновляем
                        if (orgSelect.tomselect) {
                            orgSelect.tomselect.addOption({
                                value: data.organization.id,
                                text: data.organization.name
                            });
                            orgSelect.tomselect.setValue(data.organization.id);
                        }

                        closeModal();
                    } else {
                        // Показываем ошибки
                        let errorHtml = '';
                        if (data.errors) {
                            errorHtml = '<ul class="list-disc list-inside">';
                            for (const [field, messages] of Object.entries(data.errors)) {
                                messages.forEach(msg => {
                                    errorHtml += `<li>${msg}</li>`;
                                });
                            }
                            errorHtml += '</ul>';
                        } else {
                            errorHtml = data.message || 'Произошла ошибка при сохранении';
                        }
                        errorsDiv.innerHTML = errorHtml;
                        errorsDiv.classList.remove('hidden');
                    }
                } catch (error) {
                    errorsDiv.textContent = 'Ошибка сети. Попробуйте ещё раз.';
                    errorsDiv.classList.remove('hidden');
                } finally {
                    saveBtn.disabled = false;
                    saveBtn.textContent = 'Сохранить';
                }
            });
        }
    });
</script>
