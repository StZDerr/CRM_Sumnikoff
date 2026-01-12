@csrf

<div class="grid grid-cols-2 gap-4">
    <div class="col-span-2">
        <x-input-label for="name_full" :value="'Полное название'" />
        <x-text-input id="name_full" name="name_full" type="text" class="mt-1 block w-full" :value="old('name_full', $organization->name_full ?? '')" required />
        <x-input-error :messages="$errors->get('name_full')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="name_short" :value="'Сокращённое название'" />
        <x-text-input id="name_short" name="name_short" type="text" class="mt-1 block w-full" :value="old('name_short', $organization->name_short ?? '')" />
        <x-input-error :messages="$errors->get('name_short')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="phone" :value="'Телефон'" />
        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $organization->phone ?? '')" />
        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="email" :value="'Email'" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $organization->email ?? '')" />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="inn" :value="'ИНН'" />
        <x-text-input id="inn" name="inn" type="text" class="mt-1 block w-full" :value="old('inn', $organization->inn ?? '')" />
        <x-input-error :messages="$errors->get('inn')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="ogrnip" :value="'ОГРНИП'" />
        <x-text-input id="ogrnip" name="ogrnip" type="text" class="mt-1 block w-full" :value="old('ogrnip', $organization->ogrnip ?? '')" />
        <x-input-error :messages="$errors->get('ogrnip')" class="mt-2" />
    </div>

    <div class="col-span-2">
        <x-input-label for="legal_address" :value="'Юридический адрес'" />
        <textarea id="legal_address" name="legal_address" rows="2" class="mt-1 block w-full rounded border px-3 py-2">{{ old('legal_address', $organization->legal_address ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('legal_address')" class="mt-2" />
    </div>

    <div class="col-span-2">
        <x-input-label for="actual_address" :value="'Фактический адрес'" />
        <textarea id="actual_address" name="actual_address" rows="2" class="mt-1 block w-full rounded border px-3 py-2">{{ old('actual_address', $organization->actual_address ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('actual_address')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="account_number" :value="'Расчётный счёт'" />
        <x-text-input id="account_number" name="account_number" type="text" class="mt-1 block w-full"
            :value="old('account_number', $organization->account_number ?? '')" />
        <x-input-error :messages="$errors->get('account_number')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="bank_name" :value="'Банк'" />
        <x-text-input id="bank_name" name="bank_name" type="text" class="mt-1 block w-full" :value="old('bank_name', $organization->bank_name ?? '')" />
        <x-input-error :messages="$errors->get('bank_name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="corr_account" :value="'Корр. счёт'" />
        <x-text-input id="corr_account" name="corr_account" type="text" class="mt-1 block w-full"
            :value="old('corr_account', $organization->corr_account ?? '')" />
        <x-input-error :messages="$errors->get('corr_account')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="bic" :value="'БИК'" />
        <x-text-input id="bic" name="bic" type="text" class="mt-1 block w-full" :value="old('bic', $organization->bic ?? '')" />
        <x-input-error :messages="$errors->get('bic')" class="mt-2" />
    </div>

    <div class="col-span-2">
        <x-input-label for="notes" :value="'Примечание'" />
        <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded border px-3 py-2">{{ old('notes', $organization->notes ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('notes')" class="mt-2" />
    </div>

    <!-- Статус -->
    <div>
        <x-input-label for="campaign_status_id" :value="'Статус'" />
        <select id="campaign_status_id" name="campaign_status_id" class="mt-1 block w-full rounded border px-3 py-2">
            @foreach ($statuses ?? [] as $id => $name)
                <option value="{{ $id }}" @selected((string) old('campaign_status_id', $organization->campaign_status_id ?? '') === (string) $id)>{{ $name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('campaign_status_id')" class="mt-2" />
    </div>

    <!-- Источник -->
    <div>
        <x-input-label for="campaign_source_id" :value="'Источник'" />
        <select id="campaign_source_id" name="campaign_source_id" class="mt-1 block w-full rounded border px-3 py-2">
            <option value="">— не выбрано —</option>
            @foreach ($sources ?? [] as $id => $name)
                <option value="{{ $id }}" @selected((string) old('campaign_source_id', $organization->campaign_source_id ?? '') === (string) $id)>{{ $name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('campaign_source_id')" class="mt-2" />
    </div>
</div>

<div class="flex items-center gap-3 mt-4">
    <x-primary-button>{{ $submit ?? 'Сохранить' }}</x-primary-button>
    <a href="{{ route('organizations.index') }}"
        class="inline-flex items-center px-3 py-2 border rounded hover:bg-gray-50">Отмена</a>
</div>
