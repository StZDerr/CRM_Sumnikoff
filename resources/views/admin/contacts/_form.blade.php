@csrf

<div class="grid grid-cols-2 gap-4">
    <div>
        <x-input-label for="last_name" :value="'Фамилия'" />
        <x-text-input id="last_name" name="last_name" type="text" class="mt-1 block w-full" :value="old('last_name', $contact->last_name ?? '')" />
        <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="first_name" :value="'Имя'" />
        <x-text-input id="first_name" name="first_name" type="text" class="mt-1 block w-full" :value="old('first_name', $contact->first_name ?? '')" />
        <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="middle_name" :value="'Отчество'" />
        <x-text-input id="middle_name" name="middle_name" type="text" class="mt-1 block w-full" :value="old('middle_name', $contact->middle_name ?? '')" />
        <x-input-error :messages="$errors->get('middle_name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="position" :value="'Должность'" />
        <x-text-input id="position" name="position" type="text" class="mt-1 block w-full" :value="old('position', $contact->position ?? '')" />
        <x-input-error :messages="$errors->get('position')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="phone" :value="'Телефон'" />
        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $contact->phone ?? '')" />
        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="email" :value="'Email'" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $contact->email ?? '')" />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="preferred_messenger" :value="'Мессенджер (приоритет)'" />
        <select id="preferred_messenger" name="preferred_messenger" class="mt-1 block w-full rounded border px-3 py-2">
            <option value="">—</option>
            @foreach (['telegram', 'whatsapp', 'viber', 'skype', 'call', 'other'] as $m)
                <option value="{{ $m }}" @selected(old('preferred_messenger', $contact->preferred_messenger ?? '') === $m)>{{ ucfirst($m) }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('preferred_messenger')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="messenger_contact" :value="'Контакт в мессенджере'" />
        <x-text-input id="messenger_contact" name="messenger_contact" type="text" class="mt-1 block w-full"
            :value="old('messenger_contact', $contact->messenger_contact ?? '')" />
        <x-input-error :messages="$errors->get('messenger_contact')" class="mt-2" />
    </div>

    <!-- Organization (optional) -->
    <div>
        <x-input-label for="organization_id" :value="'Организация (опционально)'" />
        <select id="organization_id" name="organization_id" class="mt-1 block w-full rounded border px-3 py-2">
            <option value="">— не привязывать —</option>
            @foreach ($organizations ?? [] as $id => $title)
                <option value="{{ $id }}" @selected(old('organization_id', $contact->organization_id ?? '') == $id)>{{ $title }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('organization_id')" class="mt-2" />
    </div>

    <!-- Паспорт РФ -->
    <div class="col-span-2 mt-4 border-t pt-4">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Паспорт РФ</h3>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <x-input-label for="passport_series" :value="'Серия паспорта'" />
                <x-text-input id="passport_series" name="passport_series" type="text" maxlength="4"
                    class="mt-1 block w-full" :value="old('passport_series', $contact->passport_series ?? '')" />
                <x-input-error :messages="$errors->get('passport_series')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="passport_number" :value="'Номер паспорта'" />
                <x-text-input id="passport_number" name="passport_number" type="text" maxlength="6"
                    class="mt-1 block w-full" :value="old('passport_number', $contact->passport_number ?? '')" />
                <x-input-error :messages="$errors->get('passport_number')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="passport_issued_at" :value="'Дата выдачи'" />
                <x-text-input id="passport_issued_at" name="passport_issued_at" type="date" class="mt-1 block w-full"
                    :value="old('passport_issued_at', optional($contact->passport_issued_at)->format('Y-m-d'))" />
                <x-input-error :messages="$errors->get('passport_issued_at')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="passport_department_code" :value="'Код подразделения'" />
                <x-text-input id="passport_department_code" name="passport_department_code" type="text"
                    placeholder="000-000" class="mt-1 block w-full" :value="old('passport_department_code', $contact->passport_department_code ?? '')" />
                <x-input-error :messages="$errors->get('passport_department_code')" class="mt-2" />
            </div>

            <div class="col-span-2">
                <x-input-label for="passport_issued_by" :value="'Кем выдан'" />
                <x-text-input id="passport_issued_by" name="passport_issued_by" type="text" class="mt-1 block w-full"
                    :value="old('passport_issued_by', $contact->passport_issued_by ?? '')" />
                <x-input-error :messages="$errors->get('passport_issued_by')" class="mt-2" />
            </div>

            <div class="col-span-2">
                <x-input-label for="passport_birth_place" :value="'Место рождения'" />
                <x-text-input id="passport_birth_place" name="passport_birth_place" type="text"
                    class="mt-1 block w-full" :value="old('passport_birth_place', $contact->passport_birth_place ?? '')" />
                <x-input-error :messages="$errors->get('passport_birth_place')" class="mt-2" />
            </div>
        </div>
    </div>


    <div class="col-span-2">
        <x-input-label for="comment" :value="'Комментарий'" />
        <textarea id="comment" name="comment" rows="4" class="mt-1 block w-full rounded border px-3 py-2">{{ old('comment', $contact->comment ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('comment')" class="mt-2" />
    </div>
</div>

<div class="flex items-center gap-3 mt-4">
    <x-primary-button>{{ $submit ?? 'Сохранить' }}</x-primary-button>
    <a href="{{ route('contacts.index') }}"
        class="inline-flex items-center px-3 py-2 border rounded hover:bg-gray-50">Отмена</a>
</div>
