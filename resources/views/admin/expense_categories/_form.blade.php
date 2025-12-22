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
</div>
