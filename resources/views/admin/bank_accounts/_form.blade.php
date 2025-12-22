@php
    $id = $bankAccount->id ?? null;
@endphp

<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700">Наименование счёта</label>
        <input name="title" type="text" required value="{{ old('title', $bankAccount->title ?? '') }}"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        @error('title')
            <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Р/сч</label>
            <input name="account_number" type="text" required
                value="{{ old('account_number', $bankAccount->account_number ?? '') }}"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
            @error('account_number')
                <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">к/сч</label>
            <input name="correspondent_account" type="text"
                value="{{ old('correspondent_account', $bankAccount->correspondent_account ?? '') }}"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
            @error('correspondent_account')
                <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">БИК</label>
            <input name="bik" type="text" value="{{ old('bik', $bankAccount->bik ?? '') }}"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
            @error('bik')
                <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">ИНН</label>
            <input name="inn" type="text" value="{{ old('inn', $bankAccount->inn ?? '') }}"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
            @error('inn')
                <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Наименование банка</label>
            <input name="bank_name" type="text" value="{{ old('bank_name', $bankAccount->bank_name ?? '') }}"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
            @error('bank_name')
                <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Примечание</label>
        <textarea name="notes" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('notes', $bankAccount->notes ?? '') }}</textarea>
        @error('notes')
            <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
        @enderror
    </div>
</div>
