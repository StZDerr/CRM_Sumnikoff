@props(['specialty'])

<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700">Название</label>
        <input type="text" name="name" value="{{ old('name', $specialty->name) }}" required
            class="mt-1 block w-full rounded border-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
        @error('name')
            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Оклад (руб.)</label>
        <input type="number" name="salary" value="{{ old('salary', $specialty->salary ?? 0) }}" min="0" required
            class="mt-1 block w-full rounded border-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
        @error('salary')
            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="flex items-center gap-3">
        <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="active" value="1"
                {{ old('active', $specialty->active ?? true) ? 'checked' : '' }}
                class="rounded border-gray-200 focus:ring-indigo-500" />
            <span class="text-sm text-gray-700">Активна</span>
        </label>
    </div>

    <div class="pt-4">
        <button type="submit"
            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded shadow-sm hover:bg-indigo-500">
            Сохранить
        </button>
        <a href="{{ route('specialties.index') }}" class="ml-2 text-sm text-gray-600 hover:underline">Отмена</a>
    </div>
</div>
