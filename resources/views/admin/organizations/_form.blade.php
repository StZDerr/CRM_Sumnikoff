@csrf

<div class="max-w-3xl mx-auto p-6 bg-gray-50 rounded-lg shadow-md">

    {{-- Тип организации --}}
    <div class="mb-6">
        <span class="block text-sm font-medium text-gray-700 mb-2">Тип организации</span>
        <div class="flex rounded-md shadow-sm overflow-hidden w-full border border-gray-300">
            <button type="button" data-type="legal"
                class="entity-toggle flex-1 px-4 py-2 text-center bg-indigo-500 text-white font-medium hover:bg-indigo-600 transition-colors duration-200 focus:outline-none">Юридическое
                лицо</button>
            <button type="button" data-type="individual"
                class="entity-toggle flex-1 px-4 py-2 text-center bg-white text-gray-700 font-medium hover:bg-gray-100 transition-colors duration-200 focus:outline-none">Физическое
                лицо</button>
        </div>
        <input type="hidden" id="entity_type" name="entity_type"
            value="{{ old('entity_type', $organization->entity_type ?? 'legal') }}">
        <x-input-error :messages="$errors->get('entity_type')" class="mt-2" />
    </div>

    <div class="grid grid-cols-2 gap-4">

        {{-- Основные поля --}}
        <div class="col-span-2 p-4 bg-white shadow rounded transition-all duration-300">
            <x-input-label for="name_full" :value="'Полное название'" />
            <x-text-input id="name_full" name="name_full" type="text" class="mt-1 block w-full" :value="old('name_full', $organization->name_full ?? '')"
                required />
            <x-input-error :messages="$errors->get('name_full')" class="mt-2" />
        </div>

        <div class="p-4 bg-white shadow rounded transition-all duration-300">
            <x-input-label for="name_short" :value="'Сокращённое название'" />
            <x-text-input id="name_short" name="name_short" type="text" class="mt-1 block w-full"
                :value="old('name_short', $organization->name_short ?? '')" />
            <x-input-error :messages="$errors->get('name_short')" class="mt-2" />
        </div>

        <div class="p-4 bg-white shadow rounded transition-all duration-300">
            <x-input-label for="phone" :value="'Телефон'" />
            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $organization->phone ?? '')" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <div class="p-4 bg-white shadow rounded transition-all duration-300">
            <x-input-label for="email" :value="'Email'" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $organization->email ?? '')" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        {{-- Поля для Юр. лица --}}
        <div class="p-4 bg-white shadow rounded transition-all duration-300 legal-only">
            <x-input-label for="inn" :value="'ИНН'" />
            <x-text-input id="inn" name="inn" type="text" class="mt-1 block w-full" :value="old('inn', $organization->inn ?? '')" />
            <x-input-error :messages="$errors->get('inn')" class="mt-2" />
        </div>

        <div class="p-4 bg-white shadow rounded transition-all duration-300 legal-only">
            <x-input-label for="ogrnip" :value="'ОГРНИП'" />
            <x-text-input id="ogrnip" name="ogrnip" type="text" class="mt-1 block w-full" :value="old('ogrnip', $organization->ogrnip ?? '')" />
            <x-input-error :messages="$errors->get('ogrnip')" class="mt-2" />
        </div>

        <div class="col-span-2 p-4 bg-white shadow rounded transition-all duration-300 legal-only">
            <x-input-label for="legal_address" :value="'Юридический адрес'" />
            <textarea id="legal_address" name="legal_address" rows="2" class="mt-1 block w-full rounded border px-3 py-2">{{ old('legal_address', $organization->legal_address ?? '') }}</textarea>
            <x-input-error :messages="$errors->get('legal_address')" class="mt-2" />
        </div>

        {{-- Фактический адрес --}}
        <div class="col-span-2 p-4 bg-white shadow rounded transition-all duration-300">
            <x-input-label for="actual_address" :value="'Фактический адрес'" />
            <textarea id="actual_address" name="actual_address" rows="2" class="mt-1 block w-full rounded border px-3 py-2">{{ old('actual_address', $organization->actual_address ?? '') }}</textarea>
            <x-input-error :messages="$errors->get('actual_address')" class="mt-2" />
        </div>

        {{-- Банковские реквизиты --}}
        <div class="p-4 bg-white shadow rounded transition-all duration-300">
            <x-input-label for="account_number" :value="'Расчётный счёт'" />
            <x-text-input id="account_number" name="account_number" type="text" class="mt-1 block w-full"
                :value="old('account_number', $organization->account_number ?? '')" />
            <x-input-error :messages="$errors->get('account_number')" class="mt-2" />
        </div>

        <div class="p-4 bg-white shadow rounded transition-all duration-300">
            <x-input-label for="bank_name" :value="'Банк'" />
            <x-text-input id="bank_name" name="bank_name" type="text" class="mt-1 block w-full" :value="old('bank_name', $organization->bank_name ?? '')" />
            <x-input-error :messages="$errors->get('bank_name')" class="mt-2" />
        </div>

        <div class="p-4 bg-white shadow rounded transition-all duration-300">
            <x-input-label for="corr_account" :value="'Корр. счёт'" />
            <x-text-input id="corr_account" name="corr_account" type="text" class="mt-1 block w-full"
                :value="old('corr_account', $organization->corr_account ?? '')" />
            <x-input-error :messages="$errors->get('corr_account')" class="mt-2" />
        </div>

        <div class="p-4 bg-white shadow rounded transition-all duration-300">
            <x-input-label for="bic" :value="'БИК'" />
            <x-text-input id="bic" name="bic" type="text" class="mt-1 block w-full" :value="old('bic', $organization->bic ?? '')" />
            <x-input-error :messages="$errors->get('bic')" class="mt-2" />
        </div>

        <div class="col-span-2 p-4 bg-white shadow rounded transition-all duration-300">
            <x-input-label for="notes" :value="'Примечание'" />
            <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded border px-3 py-2">{{ old('notes', $organization->notes ?? '') }}</textarea>
            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
        </div>

        {{-- Статус --}}
        <div class="p-4 bg-white shadow rounded transition-all duration-300">
            <x-input-label for="campaign_status_id" :value="'Статус'" />
            <select id="campaign_status_id" name="campaign_status_id"
                class="mt-1 block w-full rounded border px-3 py-2">
                @foreach ($statuses ?? [] as $id => $name)
                    <option value="{{ $id }}" @selected((string) old('campaign_status_id', $organization->campaign_status_id ?? '') === (string) $id)>{{ $name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('campaign_status_id')" class="mt-2" />
        </div>

        {{-- Источник --}}
        <div class="p-4 bg-white shadow rounded transition-all duration-300">
            <div class="flex items-center justify-between">
                <x-input-label for="campaign_source_id" :value="'Источник'" />
                <button type="button" id="addSourceBtn" class="text-sm text-indigo-600 hover:underline">Добавить источник</button>
            </div>

            <select id="campaign_source_id" name="campaign_source_id"
                class="mt-1 block w-full rounded border px-3 py-2">
                <option value="">— не выбрано —</option>
                @foreach ($sources ?? [] as $id => $name)
                    <option value="{{ $id }}" @selected((string) old('campaign_source_id', $organization->campaign_source_id ?? '') === (string) $id)>{{ $name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('campaign_source_id')" class="mt-2" />

            {{-- Modal для добавления источника --}}
            <div id="addSourceModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
                <div class="bg-white w-full max-w-md rounded shadow p-6">
                    <h3 class="text-lg font-medium mb-4">Новый источник</h3>
                    <div id="addSourceErrors" class="text-sm text-red-600 mb-3"></div>
                    <div class="mb-3">
                        <label class="block text-sm text-gray-700">Название</label>
                        <input id="newSourceName" type="text" class="mt-1 block w-full rounded border px-3 py-2" />
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm text-gray-700">Позиция (опционально)</label>
                        <input id="newSourceSort" type="number" min="1" class="mt-1 block w-full rounded border px-3 py-2" />
                    </div>
                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" id="cancelAddSource" class="px-3 py-2 border rounded">Отмена</button>
                        <button type="button" id="saveAddSource" class="px-3 py-2 bg-indigo-600 text-white rounded">Сохранить</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Кнопки --}}
    <div class="flex items-center gap-3 mt-6">
        <x-primary-button>{{ $submit ?? 'Сохранить' }}</x-primary-button>
        <a href="{{ route('organizations.index') }}"
            class="inline-flex items-center px-4 py-2 border rounded hover:bg-gray-100 transition-colors duration-200">Отмена</a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const hiddenInput = document.getElementById('entity_type');
        const legalOnlyEls = document.querySelectorAll('.legal-only');
        const toggles = document.querySelectorAll('.entity-toggle');

        function setType(type) {
            hiddenInput.value = type;

            // Показываем/скрываем поля для юр. лица
            legalOnlyEls.forEach(el => {
                el.style.display = (type === 'legal' ? '' : 'none');
            });

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

        toggles.forEach(btn => btn.addEventListener('click', () => setType(btn.dataset.type)));

        // initial
        setType(hiddenInput.value);
    });
    // Inline add source modal behaviour
    (function(){
        const addBtn = document.getElementById('addSourceBtn');
        const modal = document.getElementById('addSourceModal');
        const cancelBtn = document.getElementById('cancelAddSource');
        const saveBtn = document.getElementById('saveAddSource');
        const nameInput = document.getElementById('newSourceName');
        const sortInput = document.getElementById('newSourceSort');
        const errorsEl = document.getElementById('addSourceErrors');
        const select = document.getElementById('campaign_source_id');

        if (!addBtn || !modal) return;

        function open() {
            modal.classList.remove('hidden');
            nameInput.focus();
        }
        function close() {
            modal.classList.add('hidden');
            nameInput.value = '';
            sortInput.value = '';
            errorsEl.innerHTML = '';
        }

        addBtn.addEventListener('click', open);
        cancelBtn.addEventListener('click', close);

        saveBtn.addEventListener('click', async () => {
            const name = nameInput.value.trim();
            const sort = sortInput.value ? parseInt(sortInput.value, 10) : null;
            errorsEl.innerHTML = '';
            if (!name) {
                errorsEl.textContent = 'Название обязательно.';
                return;
            }

            // Отправляем AJAX запрос на создание источника
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch("{{ route('campaign-sources.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({ name, sort_order: sort })
                });

                if (res.status === 422) {
                    const data = await res.json();
                    errorsEl.innerHTML = Object.values(data.errors || {}).flat().join('<br>');
                    return;
                }

                if (!res.ok) {
                    errorsEl.textContent = 'Ошибка сервера при создании источника.';
                    return;
                }

                const json = await res.json();
                // добавляем опцию и выбираем её
                const opt = document.createElement('option');
                opt.value = json.id;
                opt.textContent = json.name;
                opt.selected = true;
                select.appendChild(opt);

                close();
            } catch (err) {
                errorsEl.textContent = 'Ошибка при отправке запроса.';
                console.error(err);
            }
        });
    })();
</script>
