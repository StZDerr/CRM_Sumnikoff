@php
    $isEdit = isset($monthlyExpense);
@endphp

<div class="bg-white rounded shadow p-6 space-y-4">
    <div>
        <label class="block text-sm text-gray-500 mb-1">Пользователь</label>
        <select name="user_id" class="w-full border rounded px-3 py-2" required>
            <option value="">— выберите —</option>
            @foreach ($users as $user)
                <option value="{{ $user->id }}" @selected(old('user_id', $monthlyExpense->user_id ?? '') == $user->id)>
                    {{ $user->name }} ({{ $user->email }})
                </option>
            @endforeach
        </select>
        @error('user_id')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm text-gray-500 mb-1">День оплаты (1-31)</label>
            <input type="number" name="day_of_month" min="1" max="31" required
                value="{{ old('day_of_month', $monthlyExpense->day_of_month ?? 1) }}"
                class="w-full border rounded px-3 py-2" />
            @error('day_of_month')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm text-gray-500 mb-1">Сумма</label>
            <input type="number" name="amount" step="0.01" min="0.01" required
                value="{{ old('amount', $monthlyExpense->amount ?? 0) }}" class="w-full border rounded px-3 py-2" />
            @error('amount')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-end">
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1"
                    {{ old('is_active', $monthlyExpense->is_active ?? true) ? 'checked' : '' }}
                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                <span class="text-sm text-gray-700">Активен</span>
            </label>
        </div>
    </div>

    <div>
        <label class="block text-sm text-gray-500 mb-1">Наименование</label>
        <input type="text" name="title" required value="{{ old('title', $monthlyExpense->title ?? '') }}"
            class="w-full border rounded px-3 py-2" />
        @error('title')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm text-gray-500 mb-1">Примечание</label>
        <textarea name="note" rows="3" class="w-full border rounded px-3 py-2">{{ old('note', $monthlyExpense->note ?? '') }}</textarea>
        @error('note')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-4 flex items-center gap-2">
    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
        {{ $isEdit ? 'Сохранить' : 'Создать' }}
    </button>
    <a href="{{ route('monthly-expenses.index') }}" class="px-4 py-2 bg-gray-100 rounded">Отмена</a>
</div>
