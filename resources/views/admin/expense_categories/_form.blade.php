@csrf
<div class="grid grid-cols-1 gap-4">
    <div>
        <label class="text-xs text-gray-500">Название</label>
        <input type="text" name="title" value="{{ old('title', $expenseCategory->title ?? '') }}" required
            class="w-full border rounded p-2" />
        <x-input-error :messages="$errors->get('title')" class="mt-2" />
    </div>

    <div>
        <label class="text-xs text-gray-500">Slug (необязательно)</label>
        <input type="text" name="slug" value="{{ old('slug', $expenseCategory->slug ?? '') }}"
            class="w-full border rounded p-2" />
        <x-input-error :messages="$errors->get('slug')" class="mt-2" />
    </div>

    <div>
        <label class="text-xs text-gray-500">Позиция (необязательно)</label>
        <input type="number" name="sort_order" value="{{ old('sort_order', $expenseCategory->sort_order ?? '') }}"
            min="1" class="w-full border rounded p-2" />
        <x-input-error :messages="$errors->get('sort_order')" class="mt-2" />
    </div>

    <div class="flex items-center gap-2">
        <input type="hidden" name="is_office" value="0">
        <input type="checkbox" name="is_office" id="is_office" value="1"
            {{ old('is_office', $expenseCategory->is_office ?? false) ? 'checked' : '' }}
            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
        <label for="is_office" class="text-sm text-gray-700">Отнести к расходам офиса</label>
    </div>

    <div class="flex items-center gap-2">
        <input type="hidden" name="is_salary" value="0">
        <input type="checkbox" name="is_salary" id="is_salary" value="1"
            {{ old('is_salary', $expenseCategory->is_salary ?? false) ? 'checked' : '' }}
            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
        <label for="is_salary" class="text-sm text-gray-700">Отнести к расходам ЗП</label>
    </div>

    <div class="flex items-center gap-2">
        <input type="hidden" name="is_domains_hosting" value="0">
        <input type="checkbox" name="is_domains_hosting" id="is_domains_hosting" value="1"
            {{ old('is_domains_hosting', $expenseCategory->is_domains_hosting ?? false) ? 'checked' : '' }}
            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
        <label for="is_domains_hosting" class="text-sm text-gray-700">Отнести к расходам на домены и хостинг</label>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const flags = [
            document.getElementById('is_office'),
            document.getElementById('is_salary'),
            document.getElementById('is_domains_hosting'),
        ];

        flags.forEach(current => {
            current.addEventListener('change', () => {
                if (!current.checked) return;

                flags.forEach(other => {
                    if (other !== current) {
                        other.checked = false;
                    }
                });
            });
        });
    });
</script>
