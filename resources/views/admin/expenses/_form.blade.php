@php
    $expenseDateValue = old(
        'expense_date',
        isset($expense) && $expense->expense_date
            ? $expense->expense_date->format('Y-m-d\TH:i')
            : now()->format('Y-m-d\TH:i'),
    );
@endphp

<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700">Дата расхода</label>
        <input name="expense_date" type="datetime-local" required value="{{ $expenseDateValue }}"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        @error('expense_date')
            <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Сумма</label>
        <input name="amount" type="number" step="0.01" required value="{{ old('amount', $expense->amount ?? '') }}"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        @error('amount')
            <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="grid grid-cols-1 md:grid-cols-1 ">
        <div>
            <label class="block text-sm font-medium text-gray-700">Категория</label>
            <select name="expense_category_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                <option value="">— без категории —</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->id }}" @selected((int) old('expense_category_id', $expense->expense_category_id ?? 0) === $c->id)>{{ $c->title }}</option>
                @endforeach
            </select>
            @error('expense_category_id')
                <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
            @enderror
        </div>

        {{-- <div>
            <label class="block text-sm font-medium text-gray-700">Контрагент / Получатель</label>
            <select name="organization_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                <option value="">— без контрагента —</option>
                @foreach ($organizations as $o)
                    <option value="{{ $o->id }}" @selected((int) old('organization_id', $expense->organization_id ?? 0) === $o->id)>{{ $o->title }}</option>
                @endforeach
            </select>
            @error('organization_id')
                <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
            @enderror
        </div> --}}
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Метод оплаты</label>
            <select name="payment_method_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                <option value="">— выбрать —</option>
                @foreach ($paymentMethods as $pm)
                    <option value="{{ $pm->id }}" @selected((int) old('payment_method_id', $expense->payment_method_id ?? 0) === $pm->id)>{{ $pm->title }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Банковский счёт</label>
            <select name="bank_account_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                <option value="">— выбрать —</option>
                @foreach ($bankAccounts as $b)
                    <option value="{{ $b->id }}" @selected((int) old('bank_account_id', $expense->bank_account_id ?? 0) === $b->id)>{{ $b->display_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="space-y-1">
            <x-input-label for="project_id" value="Проект / Отдел" />

            <select id="project_id" name="project_id"
                class="js-project-select block w-full rounded-md border-gray-300 shadow-sm
                       focus:border-indigo-500 focus:ring-indigo-500
                       @error('project_id') border-red-500 @enderror">
                <option value="">— без проекта —</option>
                @foreach ($projects as $p)
                    <option value="{{ $p->id }}" @selected((int) old('project_id', $expense->project_id ?? 0) === $p->id)>{{ $p->title }}</option>
                @endforeach
            </select>

            <x-input-error :messages="$errors->get('project_id')" />
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Номер документа (счёт/чек)</label>
        <input name="document_number" type="text"
            value="{{ old('document_number', $expense->document_number ?? '') }}"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Назначение / Комментарий</label>
        <textarea name="description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('description', $expense->description ?? '') }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Статус</label>
        <select name="status" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            <option value="awaiting" @selected(old('status', $expense->status ?? 'awaiting') === 'awaiting')>Ожидает оплаты</option>
            <option value="partial" @selected(old('status', $expense->status ?? '') === 'partial')>Частично оплачено</option>
            <option value="paid" @selected(old('status', $expense->status ?? '') === 'paid')>Оплачено</option>
        </select>
        @error('status')
            <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Документы (файлы)</label>
        <input type="file" name="documents[]" multiple class="mt-1 block w-full" />
        @error('documents.*')
            <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
        @enderror

        @if (isset($expense) && $expense->documents->count())
            <div class="mt-3 space-y-2">
                @foreach ($expense->documents as $doc)
                    <div class="flex items-center justify-between gap-3 bg-gray-50 p-2 rounded">
                        <a href="{{ $doc->url }}" target="_blank"
                            class="text-sm text-indigo-600 hover:underline">{{ $doc->original_name ?? $doc->path }}</a>
                        <form action="{{ route('documents.destroy', $doc) }}" method="POST"
                            onsubmit="return confirm('Удалить документ?');">
                            @csrf @method('DELETE')
                            <button class="text-red-600 text-sm">Удалить</button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const orgSelect = document.querySelector('select[name="organization_id"]');
        const projectSelect = document.querySelector('select[name="project_id"]');
        if (!orgSelect || !projectSelect) return;

        // URL-шаблон: заменим PLACEHOLDER на id
        const urlTemplate = "{{ route('organizations.projects', ['organization' => 'ORG_ID']) }}";

        function clearProjects() {
            projectSelect.innerHTML = '<option value="">— без проекта —</option>';
        }

        async function loadProjects(orgId) {
            clearProjects();
            if (!orgId) return;
            const url = urlTemplate.replace('ORG_ID', orgId);
            try {
                const res = await fetch(url, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                if (!res.ok) return;
                const data = await res.json();
                data.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.id;
                    opt.textContent = p.title;
                    projectSelect.appendChild(opt);
                });
                // Восстановить выбранный проект (если был)
                const selected = "{{ old('project_id', $expense->project_id ?? '') }}";
                if (selected) projectSelect.value = selected;
            } catch (e) {
                console.error('Не удалось загрузить проекты', e);
            }
        }

        orgSelect.addEventListener('change', (e) => loadProjects(e.target.value));

        // Если уже выбран контрагент при загрузке формы
        if (orgSelect.value) {
            loadProjects(orgSelect.value);
        }
    });
</script>
