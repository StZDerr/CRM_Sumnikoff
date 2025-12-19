@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold">Новый уровень важности</h1>
            <a href="{{ route('importances.index') }}"
                class="inline-flex items-center px-3 py-2 border rounded hover:bg-gray-50">
                ← Назад
            </a>
        </div>

        <div class="bg-white shadow rounded p-6">
            <form action="{{ route('importances.store') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Name -->
                <div>
                    <x-input-label for="name" :value="'Название'" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')"
                        required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <!-- Slug -->
                <div>
                    <x-input-label for="slug" :value="'Slug (необязательно)'" />
                    <x-text-input id="slug" name="slug" type="text" class="mt-1 block w-full"
                        :value="old('slug')" />
                    <div class="text-xs text-gray-500 mt-1">Оставьте пустым, чтобы сгенерировать автоматически</div>
                    <x-input-error :messages="$errors->get('slug')" class="mt-2" />
                </div>

                <!-- Color -->
                <div class="grid grid-cols-2 gap-4 items-center">
                    <div>
                        <x-input-label for="color" :value="'Цвет (HEX)'" />
                        <x-text-input id="color" name="color" type="text" class="mt-1 block w-full"
                            :value="old('color')" placeholder="#4F46E5" />
                        <x-input-error :messages="$errors->get('color')" class="mt-2" />
                        <div class="text-xs text-gray-500 mt-1">Можно указать HEX (например <code>#4F46E5</code>)</div>
                    </div>
                    <div>
                        <x-input-label :value="'Превью'" />
                        <div class="mt-1">
                            <input id="color_preview" type="color" class="w-12 h-10 p-0 border rounded"
                                value="{{ old('color', '#CBD5E1') }}"
                                onchange="document.getElementById('color').value = this.value" />
                        </div>
                    </div>
                </div>

                <!-- Sort order -->
                <div>
                    <x-input-label for="sort_order" :value="'Позиция (опционально)'" />
                    <x-text-input id="sort_order" name="sort_order" type="number" class="mt-1 block w-48"
                        :value="old('sort_order')" placeholder="1" min="1" />
                    <div class="text-xs text-gray-500 mt-1">Число позиции — 1 = первая в списке. Если оставить пустым, будет
                        добавлен в конец.</div>
                    <x-input-error :messages="$errors->get('sort_order')" class="mt-2" />
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-3">
                    <x-primary-button>Создать</x-primary-button>
                    <a href="{{ route('importances.index') }}"
                        class="inline-flex items-center px-3 py-2 border rounded hover:bg-gray-50">
                        Отмена
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Простой синхронизатор цвета: если пользователь вводит HEX вручную, обновляем preview -->
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
@endsection
