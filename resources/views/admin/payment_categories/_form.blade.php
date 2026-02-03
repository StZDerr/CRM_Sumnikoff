@php $id = $paymentCategory->id ?? null; @endphp

<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700">Название</label>
        <input name="title" type="text" required value="{{ old('title', $paymentCategory->title ?? '') }}"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        @error('title')
            <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Slug</label>
        <input name="slug" type="text" value="{{ old('slug', $paymentCategory->slug ?? '') }}"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        <div class="text-xs text-gray-400 mt-1">Оставьте пустым, чтобы slug сгенерировался автоматически.</div>
        @error('slug')
            <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <label class="text-xs text-gray-500">Позиция (необязательно)</label>
        <input type="number" name="sort_order"
            value="{{ old('sort_order', ($paymentCategory->sort_order ?? 0) > 0 ? $paymentCategory->sort_order : '') }}"
            min="1" class="w-full border rounded p-2" />
        <x-input-error :messages="$errors->get('sort_order')" class="mt-2" />
    </div>

    <div class="flex items-center gap-2">
        <input type="hidden" name="is_domains_hosting" value="0" />
        <input id="is_domains_hosting" type="checkbox" name="is_domains_hosting" value="1" class="h-4 w-4"
            @checked(old('is_domains_hosting', $paymentCategory->is_domains_hosting ?? false)) />
        <label for="is_domains_hosting" class="text-sm text-gray-700">Категория для доменов/хостинга</label>
    </div>

</div>
