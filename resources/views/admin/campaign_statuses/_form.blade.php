@csrf

<div>
    <x-input-label for="name" :value="'Название'" />
    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $campaignStatus->name ?? '')" required
        autofocus />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div>
    <x-input-label for="slug" :value="'Slug (необязательно)'" />
    <x-text-input id="slug" name="slug" type="text" class="mt-1 block w-full" :value="old('slug', $campaignStatus->slug ?? '')" />
    <div class="text-xs text-gray-500 mt-1">Оставьте пустым, чтобы сгенерировать автоматически</div>
    <x-input-error :messages="$errors->get('slug')" class="mt-2" />
</div>

<div>
    <x-input-label for="sort_order" :value="'Позиция (опционально)'" />
    <x-text-input id="sort_order" name="sort_order" type="number" class="mt-1 block w-48" :value="old('sort_order', $campaignStatus->sort_order ?? '')"
        placeholder="1" min="1" />
    <div class="text-xs text-gray-500 mt-1">1 = первая. Оставьте пустым, чтобы добавить в конец.</div>
    <x-input-error :messages="$errors->get('sort_order')" class="mt-2" />
</div>

<div class="flex items-center gap-3 mt-4">
    <x-primary-button>{{ $submit ?? 'Сохранить' }}</x-primary-button>
    <a href="{{ route('campaign-statuses.index') }}"
        class="inline-flex items-center px-3 py-2 border rounded hover:bg-gray-50">Отмена</a>
</div>
