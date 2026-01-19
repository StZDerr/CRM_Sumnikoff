@extends('layouts.app')

@push('scripts')
    @vite('resources/js/passport-masks.js')
@endpush

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold">{{ $organization->name_short ?: $organization->name_full }}</h1>
            <div class="flex items-center gap-3">
                <a href="{{ route('organizations.edit', $organization) }}"
                    class="inline-flex items-center px-3 py-2 border rounded hover:bg-gray-50">Редактировать</a>
                <a href="{{ route('organizations.index') }}"
                    class="inline-flex items-center px-3 py-2 border rounded hover:bg-gray-50">← Назад</a>
            </div>
        </div>

        <div class="bg-white shadow rounded p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                @if ($organization->name_full)
                    <div>
                        <div class="text-sm text-gray-500">Полное название</div>
                        <div class="font-medium">{{ $organization->name_full }}</div>
                    </div>
                @endif

                @if ($organization->name_short)
                    <div>
                        <div class="text-sm text-gray-500">Сокращённое</div>
                        <div class="font-medium">{{ $organization->name_short }}</div>
                    </div>
                @endif

                @if ($organization->phone)
                    <div>
                        <div class="text-sm text-gray-500">Телефон</div>
                        <div class="font-medium">{{ $organization->phone }}</div>
                    </div>
                @endif

                @if ($organization->email)
                    <div>
                        <div class="text-sm text-gray-500">Email</div>
                        <div class="font-medium">{{ $organization->email }}</div>
                    </div>
                @endif

                @if ($organization->inn)
                    <div>
                        <div class="text-sm text-gray-500">ИНН</div>
                        <div class="font-medium">{{ $organization->inn }}</div>
                    </div>
                @endif

                @if ($organization->ogrnip)
                    <div>
                        <div class="text-sm text-gray-500">ОГРНИП</div>
                        <div class="font-medium">{{ $organization->ogrnip }}</div>
                    </div>
                @endif

                @if ($organization->legal_address)
                    <div class="col-span-2">
                        <div class="text-sm text-gray-500">Юридический адрес</div>
                        <div class="font-medium">{{ $organization->legal_address }}</div>
                    </div>
                @endif

                @if ($organization->actual_address)
                    <div class="col-span-2">
                        <div class="text-sm text-gray-500">Фактический адрес</div>
                        <div class="font-medium">{{ $organization->actual_address }}</div>
                    </div>
                @endif

                @if ($organization->account_number)
                    <div>
                        <div class="text-sm text-gray-500">Р/с</div>
                        <div class="font-medium">{{ $organization->account_number }}</div>
                    </div>
                @endif

                @if ($organization->bank_name || $organization->bic)
                    <div>
                        <div class="text-sm text-gray-500">Банк / БИК</div>
                        <div class="font-medium">
                            {{ $organization->bank_name ?? '' }}
                            {{ $organization->bank_name && $organization->bic ? '/' : '' }} {{ $organization->bic ?? '' }}
                        </div>
                    </div>
                @endif

                @if ($organization->status)
                    <div>
                        <div class="text-sm text-gray-500">Статус</div>
                        <div class="font-medium">{{ $organization->status->name }}</div>
                    </div>
                @endif

                @if ($organization->source)
                    <div>
                        <div class="text-sm text-gray-500">Источник</div>
                        <div class="font-medium">{{ $organization->source->name }}</div>
                    </div>
                @endif

                @if ($organization->notes)
                    <div class="col-span-2">
                        <div class="text-sm text-gray-500">Примечание</div>
                        <div class="font-medium whitespace-pre-line">{{ $organization->notes }}</div>
                    </div>
                @endif

                @if ($organization->createdBy)
                    <div>
                        <div class="text-sm text-gray-500">Создал</div>
                        <div class="font-medium">
                            {{ $organization->createdBy->name }}
                            @if ($organization->created_at)
                                <div class="text-xs text-gray-400 mt-1">
                                    {{ $organization->created_at->format('d.m.Y H:i') }}</div>
                            @endif
                        </div>
                    </div>
                @endif

                @if ($organization->updatedBy)
                    <div>
                        <div class="text-sm text-gray-500">Последнее обновление</div>
                        <div class="font-medium">
                            {{ $organization->updatedBy->name }}
                            @if ($organization->updated_at)
                                <div class="text-xs text-gray-400 mt-1">
                                    {{ $organization->updated_at->format('d.m.Y H:i') }}</div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>


            <hr>

            <div>
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-lg font-semibold">Контакты</h2>

                    <button type="button" onclick="openAddContactModal()"
                        class="inline-flex items-center gap-2 rounded-xl
           bg-blue-600 px-4 py-2.5
           text-sm font-semibold text-white
           shadow-sm transition
           hover:bg-blue-700 hover:shadow-md
           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
           active:scale-[0.98]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Добавить контакт
                    </button>
                </div>
                @if ($contacts->count())
                    <div class="divide-y rounded border">
                        @foreach ($contacts as $contact)
                            <div class="flex items-center justify-between px-4 py-3">
                                <div>
                                    <div class="font-medium">{{ $contact->full_name ?: '—' }}</div>
                                    <div class="text-xs text-gray-500">{{ $contact->position ?? '-' }} •
                                        {{ $contact->phone ?? '-' }}</div>
                                </div>
                                <div class="text-sm">
                                    <a href="{{ route('contacts.show', $contact) }}"
                                        class="text-indigo-600 hover:text-indigo-800 p-2">Просмотр</a>
                                    <a href="{{ route('contacts.edit', $contact) }}"
                                        class="text-indigo-600 hover:text-indigo-800 p-2">Редактировать</a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-3">
                        {{ $contacts->links() }}
                    </div>
                @else
                    <div class="text-sm text-gray-500">Контакты не добавлены.</div>
                @endif
            </div>
        </div>
    </div>
    <!-- Add Contact Modal -->
    <div id="addContactModal" class="fixed inset-0 z-50 hidden items-center justify-center">
        <div class="fixed inset-0 bg-black/50" onclick="closeAddContactModal()"></div>

        <div class="bg-white rounded shadow-lg w-full max-w-2xl mx-4 z-10 overflow-auto max-h-[90vh]" role="dialog"
            aria-modal="true">
            <div class="p-4 border-b flex items-center justify-between">
                <h3 class="text-lg font-medium">Новый сотрудник кампании —
                    {{ $organization->name_short ?: $organization->name_full }}</h3>
                <button type="button" class="text-gray-600" onclick="closeAddContactModal()">✕</button>
            </div>

            <div class="p-6 overflow-y-auto" style="max-height: calc(90vh - 64px);">
                <form action="{{ route('contacts.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="organization_id" value="{{ $organization->id }}">

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="last_name" :value="'Фамилия'" />
                            <x-text-input id="last_name" name="last_name" type="text" class="mt-1 block w-full"
                                :value="old('last_name')" />
                            <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="first_name" :value="'Имя'" />
                            <x-text-input id="first_name" name="first_name" type="text" class="mt-1 block w-full"
                                :value="old('first_name')" />
                            <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="middle_name" :value="'Отчество'" />
                            <x-text-input id="middle_name" name="middle_name" type="text" class="mt-1 block w-full"
                                :value="old('middle_name')" />
                            <x-input-error :messages="$errors->get('middle_name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="position" :value="'Должность'" />
                            <x-text-input id="position" name="position" type="text" class="mt-1 block w-full"
                                :value="old('position')" />
                            <x-input-error :messages="$errors->get('position')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="phone" :value="'Телефон'" />
                            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full"
                                :value="old('phone')" />
                            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="email" :value="'Email'" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                                :value="old('email')" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="preferred_messenger" :value="'Мессенджер (приоритет)'" />
                            <select id="preferred_messenger" name="preferred_messenger"
                                class="mt-1 block w-full rounded border px-3 py-2">
                                <option value="">—</option>
                                @foreach (['telegram', 'whatsapp', 'viber', 'skype', 'call', 'other'] as $m)
                                    <option value="{{ $m }}" @selected(old('preferred_messenger') === $m)>{{ ucfirst($m) }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('preferred_messenger')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="messenger_contact" :value="'Контакт в мессенджере'" />
                            <x-text-input id="messenger_contact" name="messenger_contact" type="text"
                                class="mt-1 block w-full" :value="old('messenger_contact')" />
                            <x-input-error :messages="$errors->get('messenger_contact')" class="mt-2" />
                        </div>
                    </div>

                    {{-- Паспорт РФ --}}
                    <div class="mt-4 border-t pt-4">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">
                            Паспорт РФ
                        </h3>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="passport_series" :value="'Серия паспорта'" />
                                <x-text-input id="passport_series" name="passport_series" type="text" maxlength="4"
                                    class="mt-1 block w-full" :value="old('passport_series')" />
                                <x-input-error :messages="$errors->get('passport_series')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="passport_number" :value="'Номер паспорта'" />
                                <x-text-input id="passport_number" name="passport_number" type="text" maxlength="6"
                                    class="mt-1 block w-full" :value="old('passport_number')" />
                                <x-input-error :messages="$errors->get('passport_number')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="passport_issued_at" :value="'Дата выдачи'" />
                                <x-text-input id="passport_issued_at" name="passport_issued_at" type="date"
                                    class="mt-1 block w-full" :value="old('passport_issued_at')" />
                                <x-input-error :messages="$errors->get('passport_issued_at')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="passport_department_code" :value="'Код подразделения'" />
                                <x-text-input id="passport_department_code" name="passport_department_code"
                                    type="text" placeholder="000-000" class="mt-1 block w-full" :value="old('passport_department_code')" />
                                <x-input-error :messages="$errors->get('passport_department_code')" class="mt-2" />
                            </div>

                            <div class="col-span-2">
                                <x-input-label for="passport_issued_by" :value="'Кем выдан'" />
                                <x-text-input id="passport_issued_by" name="passport_issued_by" type="text"
                                    class="mt-1 block w-full" :value="old('passport_issued_by')" />
                                <x-input-error :messages="$errors->get('passport_issued_by')" class="mt-2" />
                            </div>

                            <div class="col-span-2">
                                <x-input-label for="passport_birth_place" :value="'Место рождения'" />
                                <x-text-input id="passport_birth_place" name="passport_birth_place" type="text"
                                    class="mt-1 block w-full" :value="old('passport_birth_place')" />
                                <x-input-error :messages="$errors->get('passport_birth_place')" class="mt-2" />
                            </div>

                            <div class="col-span-2">
                                <x-input-label for="comment" :value="'Комментарий'" />
                                <textarea id="comment" name="comment" rows="3" class="mt-1 block w-full rounded border px-3 py-2">{{ old('comment') }}</textarea>
                                <x-input-error :messages="$errors->get('comment')" class="mt-2" />
                            </div>

                            <div class="flex items-center gap-3 mt-4">
                                <x-primary-button>Создать</x-primary-button>
                                <button type="button" class="px-3 py-2 border rounded hover:bg-gray-50"
                                    onclick="closeAddContactModal()">Отмена</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        function openAddContactModal() {
            document.getElementById('addContactModal').classList.remove('hidden');
            document.getElementById('addContactModal').classList.add('flex');
        }

        function closeAddContactModal() {
            document.getElementById('addContactModal').classList.add('hidden');
            document.getElementById('addContactModal').classList.remove('flex');
        }

        // Если были ошибки валидации при предыдущей попытке — откроем модал автоматически (и только для этой org)
        @if ($errors->any() && old('organization_id') == $organization->id)
            window.addEventListener('DOMContentLoaded', function() {
                openAddContactModal();
            });
        @endif
    </script>
@endsection
