@php $id = $invoiceStatus->id ?? null; @endphp

<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700">Название</label>
        <input name="name" type="text" required value="{{ old('name', $invoiceStatus->name ?? '') }}"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        @error('name')
            <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Slug</label>
        <input name="slug" type="text" value="{{ old('slug', $invoiceStatus->slug ?? '') }}"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        <div class="text-xs text-gray-400 mt-1">Оставьте пустым, чтобы slug сгенерировался автоматически.</div>
        @error('slug')
            <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Позиция (sort order)</label>
        <input name="sort_order" type="number" min="1"
            value="{{ old('sort_order', $invoiceStatus->sort_order ?? '') }}"
            class="mt-1 block w-40 border-gray-300 rounded-md shadow-sm" />
        @error('sort_order')
            <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
        @enderror
    </div>
</div>
