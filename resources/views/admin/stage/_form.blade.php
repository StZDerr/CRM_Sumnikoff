<!-- Used in create/edit -->
<div class="space-y-6">
    <div>
        <x-input-label for="name" :value="'Название'" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $stage->name ?? '')" required
            autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="slug" :value="'Slug (опционально)'" />
        <x-text-input id="slug" name="slug" type="text" class="mt-1 block w-full" :value="old('slug', $stage->slug ?? '')" />
        <div class="text-xs text-gray-500 mt-1">Оставьте пустым для автоматической генерации</div>
        <x-input-error :messages="$errors->get('slug')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="description" :value="'Описание (опционально)'" />
        <textarea id="description" name="description" class="mt-1 block w-full border rounded p-2" rows="4">{{ old('description', $stage->description ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <div class="grid grid-cols-2 gap-4 items-center">
        <div>
            <x-input-label for="color" :value="'Цвет (HEX)'" />
            <x-text-input id="color" name="color" type="text" class="mt-1 block w-full" :value="old('color', $stage->color ?? '')"
                placeholder="#4F46E5" />
            <x-input-error :messages="$errors->get('color')" class="mt-2" />
            <div class="text-xs text-gray-500 mt-1">HEX или любой CSS-цвет</div>
        </div>
        <div>
            <x-input-label :value="'Превью'" />
            <div class="mt-1">
                <input id="color_preview" type="color" class="w-12 h-10 p-0 border rounded"
                    value="{{ old('color', $stage->color ?? '#CBD5E1') }}"
                    onchange="document.getElementById('color').value = this.value" />
            </div>
        </div>
    </div>

    <div>
        <x-input-label for="sort_order" :value="'Позиция (опционально)'" />
        <x-text-input id="sort_order" name="sort_order" type="number" class="mt-1 block w-48" :value="old('sort_order', $stage->sort_order ?? '')"
            min="1" />
        <div class="text-xs text-gray-500 mt-1">1 = первая; оставить пустым для добавления в конец</div>
        <x-input-error :messages="$errors->get('sort_order')" class="mt-2" />
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const colorInput = document.getElementById('color');
        const preview = document.getElementById('color_preview');
        if (!colorInput || !preview) return;
        colorInput.addEventListener('input', () => {
            try {
                preview.value = colorInput.value || '#CBD5E1';
            } catch (e) {}
        });
    });
</script>
