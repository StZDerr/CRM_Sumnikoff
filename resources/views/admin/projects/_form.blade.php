@csrf

<div class="grid grid-cols-2 gap-4">
    <div class="col-span-2">
        <x-input-label for="title" :value="'Название проекта'" />
        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', isset($project) ? $project->title : '')" required
            autofocus />
        <x-input-error :messages="$errors->get('title')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="organization_id" :value="'Организация'" />
        <select id="organization_id" name="organization_id" class="mt-1 block w-full rounded border px-3 py-2">
            <option value="">—</option>
            @foreach ($organizations ?? [] as $id => $name)
                <option value="{{ $id }}" @selected((string) old('organization_id', isset($project) ? $project->organization_id : '') === (string) $id)>{{ $name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('organization_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="marketer_id" :value="'Маркетолог'" />
        <select id="marketer_id" name="marketer_id" class="mt-1 block w-full rounded border px-3 py-2">
            <option value="">—</option>
            @foreach ($marketers ?? [] as $id => $name)
                <option value="{{ $id }}" @selected((string) old('marketer_id', isset($project) ? $project->marketer_id : '') === (string) $id)>{{ $name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('marketer_id')" class="mt-2" />
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

    <div>
        <x-input-label for="payment_due_day" :value="'Срок оплаты (день месяца)'" />
        <x-text-input id="payment_due_day" name="payment_due_day" type="number" min="1" max="31"
            class="mt-1 block w-40" :value="old('payment_due_day', isset($project) ? $project->payment_due_day : '')" />
        <x-input-error :messages="$errors->get('payment_due_day')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="closed_at" :value="'Дата закрытия (опционально)'" />
        <x-text-input id="closed_at" name="closed_at" type="date" :value="old('closed_at', isset($project) && $project->closed_at ? $project->closed_at->format('Y-m-d') : '')" class="mt-1 block w-48" />
        <x-input-error :messages="$errors->get('closed_at')" class="mt-2" />
    </div>

    <!-- Stages: sortable list + selection -->
    <div class="col-span-2">
        <x-input-label :value="'Этапы (перетаскивайте для порядка, клик чтобы выбрать)'" />
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
        <div class="text-xs text-gray-500 mt-2">Выбранные этапы и их порядок отправляются при сохранении.</div>
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
            // Toggle selection on click (excluding drag handle)
            el.addEventListener('click', (e) => {
                if (e.target.closest('.drag-handle')) return;
                const cb = el.querySelector('.stage-checkbox');
                cb.checked = !cb.checked;
                el.classList.toggle('selected', cb.checked);
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
