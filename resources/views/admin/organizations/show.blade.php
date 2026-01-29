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

                    <div class="flex items-center gap-3">
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
                </div>

                {{-- Add Document Modal --}}
                {{-- Add Document Modal (styled) --}}
                <div id="addDocumentModal" class="fixed inset-0 z-50 hidden items-center justify-center">
                    <div class="fixed inset-0 bg-black/50" onclick="closeAddDocumentModal()"></div>

                    <div class="bg-white rounded-xl shadow-lg w-full max-w-2xl mx-4 z-10 overflow-auto max-h-[90vh] border">
                        <div class="flex items-center justify-between px-5 py-4 border-b">
                            <div>
                                <div class="text-lg font-semibold text-gray-900">Добавить документ</div>
                                <div class="text-xs text-gray-500">
                                    {{ $organization->name_short ?: $organization->name_full }}</div>
                            </div>
                            <button type="button" class="text-gray-500 hover:text-gray-700"
                                onclick="closeAddDocumentModal()">✕</button>
                        </div>

                        <div class="p-6">
                            <form id="org-document-form"
                                action="{{ route('organizations.documents.store', $organization) }}" method="POST"
                                enctype="multipart/form-data" class="space-y-4">
                                @csrf
                                <input type="hidden" name="organization_id" value="{{ $organization->id }}">
                                <input type="hidden" name="form" value="add_document">

                                <div class="grid grid-cols-1 gap-3">
                                    <label class="block text-sm font-medium text-gray-700">Файлы</label>

                                    <div class="flex items-center gap-3">
                                        <label for="org-doc-input"
                                            class="inline-flex items-center gap-2 px-3 py-2 bg-white border rounded-md cursor-pointer hover:shadow-sm text-sm text-gray-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 15a4 4 0 004 4h10a4 4 0 004-4 4 4 0 00-4-4H7a4 4 0 00-4 4zM16 7l-4-4m0 0L8 7m4-4v11" />
                                            </svg>
                                            Выбрать файлы
                                        </label>

                                        <div class="text-sm text-gray-500">Макс. 5 МБ на файл. Поддерживаются: pdf, doc,
                                            docx, xls, xlsx, zip, txt, изображения.</div>
                                    </div>

                                    <input id="org-doc-input" type="file" name="documents[]" multiple
                                        accept="application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/zip,image/*,text/plain"
                                        class="hidden" />

                                    <div id="org-doc-selected" class="mt-2 space-y-2"></div>

                                    @if ($errors->any() && old('form') === 'add_document' && old('organization_id') == $organization->id)
                                        <div class="text-sm text-red-600 mt-2">
                                            @foreach ($errors->all() as $e)
                                                <div>{{ $e }}</div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <div class="flex items-center justify-end gap-3 mt-4">
                                    <button type="button" class="px-4 py-2 text-sm rounded border hover:bg-gray-50"
                                        onclick="closeAddDocumentModal()">Отмена</button>
                                    <button type="submit"
                                        class="px-4 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700">Загрузить</button>
                                </div>
                            </form>
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const input = document.getElementById('org-doc-input');
                                const list = document.getElementById('org-doc-selected');
                                if (!input || !list) return;

                                function humanSize(bytes) {
                                    if (!bytes) return '';
                                    if (bytes < 1024) return bytes + ' B';
                                    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
                                    return (bytes / 1048576).toFixed(2) + ' MB';
                                }

                                input.addEventListener('change', function() {
                                    list.innerHTML = '';
                                    Array.from(this.files).forEach(f => {
                                        const row = document.createElement('div');
                                        row.className = 'flex items-center gap-3 bg-gray-50 border rounded p-2';

                                        if (f.type && f.type.startsWith('image/')) {
                                            const img = document.createElement('img');
                                            img.src = URL.createObjectURL(f);
                                            img.className = 'h-12 w-12 object-cover rounded';
                                            row.appendChild(img);
                                        } else {
                                            const box = document.createElement('div');
                                            box.className =
                                                'h-12 w-12 bg-indigo-100 rounded flex items-center justify-center text-indigo-700 font-semibold';
                                            box.textContent = f.name.split('.').pop().toUpperCase();
                                            row.appendChild(box);
                                        }

                                        const meta = document.createElement('div');
                                        meta.className = 'flex-1 min-w-0';

                                        const name = document.createElement('div');
                                        name.className = 'text-sm text-gray-900 truncate';
                                        name.textContent = f.name;

                                        const info = document.createElement('div');
                                        info.className = 'text-xs text-gray-500';
                                        info.textContent = humanSize(f.size);

                                        meta.appendChild(name);
                                        meta.appendChild(info);

                                        row.appendChild(meta);

                                        list.appendChild(row);
                                    });
                                });

                                const label = document.querySelector('label[for="org-doc-input"]');
                                if (label) label.addEventListener('keydown', function(e) {
                                    if (e.key === 'Enter' || e.key === ' ') input.click();
                                });
                            });
                        </script>

                    </div>
                </div>
                @if ($contacts->count())
                    <div class="divide-y rounded border">
                        @foreach ($contacts as $contact)
                            <div class="flex items-center justify-between px-4 py-3">
                                <div>
                                    <div class="font-medium">{{ $contact->full_name ?: '—' }}</div>
                                    <div class="text-xs text-gray-500">
                                        {{ $contact->position ?? '-' }} • {{ $contact->phone ?? '-' }}
                                        @if ($contact->birth_date)
                                            • {{ \Illuminate\Support\Carbon::make($contact->birth_date)->format('d.m.Y') }}
                                        @endif
                                    </div>
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

            <hr>

            <div>
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-lg font-semibold">Документы</h2>
                    @if (auth()->user()->isAdmin())
                        <button type="button" onclick="openAddDocumentModal()"
                            class="inline-flex items-center gap-2 rounded-xl bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 active:scale-[0.98]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            Добавить документ
                        </button>
                    @endif
                </div>
                @if ($organization->documents && $organization->documents->count())
                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach ($organization->documents as $doc)
                            @php
                                $ext = strtolower(pathinfo($doc->path ?? '', PATHINFO_EXTENSION));
                                $sizeLabel = $doc->size
                                    ? ($doc->size < 1024
                                        ? $doc->size . ' B'
                                        : ($doc->size < 1048576
                                            ? round($doc->size / 1024, 1) . ' KB'
                                            : round($doc->size / 1048576, 2) . ' MB'))
                                    : '';
                                $date = $doc->created_at ? $doc->created_at->format('d.m.Y H:i') : '';
                            @endphp

                            <div class="bg-white border rounded-lg p-3 flex flex-col hover:shadow-md transition">
                                <div class="flex items-start gap-3">
                                    <div class="shrink-0">
                                        @if (str_starts_with($doc->mime ?? '', 'image/'))
                                            <a href="{{ route('documents.download', $doc) }}" class="inline-block">
                                                <img src="{{ $doc->url }}"
                                                    alt="{{ $doc->original_name ?? 'image' }}"
                                                    class="h-12 w-12 object-cover rounded" />
                                            </a>
                                        @else
                                            <div
                                                class="h-12 w-12 bg-indigo-100 rounded flex items-center justify-center text-indigo-700 font-semibold">
                                                {{ strtoupper($ext) }}</div>
                                        @endif
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <a href="{{ route('documents.download', $doc) }}"
                                            class="block font-medium text-indigo-600 truncate">{{ $doc->original_name ?? $doc->path }}</a>
                                        <div class="text-xs text-gray-500 mt-1">{{ strtoupper($ext) }} @if ($sizeLabel)
                                                · {{ $sizeLabel }}
                                                @endif @if ($date)
                                                    · {{ $date }}
                                                @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3 flex items-center justify-between">
                                    <div class="text-xs text-gray-500">
                                        {{ $doc->created_at ? $doc->created_at->format('d.m.Y') : '' }}</div>
                                    @if (auth()->user()->isAdmin())
                                        <div class="flex items-center gap-3">
                                            <a href="{{ route('documents.download', $doc) }}"
                                                class="text-indigo-600 hover:text-indigo-800" title="Скачать">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                                    <polyline points="7 10 12 15 17 10" />
                                                    <line x1="12" y1="15" x2="12" y2="3" />
                                                </svg>

                                            </a>

                                            <button type="button" class="text-red-600 doc-delete-btn"
                                                data-url="{{ route('documents.destroy', $doc) }}" title="Удалить">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="3 6 5 6 21 6" />
                                                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                                                    <path d="M10 11v6" />
                                                    <path d="M14 11v6" />
                                                    <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
                                                </svg>

                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-sm text-gray-500">Документы не добавлены.</div>
                @endif
            </div>
        </div>
    </div>

    <script>
        // Удаление документов через AJAX (для страницы организации — вверху карточки)
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.doc-delete-btn');
            if (!btn) return;
            if (!confirm('Удалить документ?')) return;

            const url = btn.getAttribute('data-url');
            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(r => {
                if (!r.ok) throw new Error('Ошибка при удалении');
                return r.json();
            }).then(() => {
                const item = btn.closest('.bg-white.border.rounded.p-2');
                if (item) item.remove();
            }).catch(err => {
                console.error(err);
                alert('Не удалось удалить документ.');
            });
        });
    </script>

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

                        <div>
                            <x-input-label for="birth_date" :value="'Дата рождения'" />
                            <x-text-input id="birth_date" name="birth_date" type="date" class="mt-1 block w-full"
                                :value="old('birth_date')" />
                            <x-input-error :messages="$errors->get('birth_date')" class="mt-2" />
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

        function openAddDocumentModal() {
            document.getElementById('addDocumentModal').classList.remove('hidden');
            document.getElementById('addDocumentModal').classList.add('flex');
        }

        function closeAddDocumentModal() {
            document.getElementById('addDocumentModal').classList.add('hidden');
            document.getElementById('addDocumentModal').classList.remove('flex');
        }

        // Если были ошибки валидации при предыдущей попытке — откроем модал автоматически (и только для этой org)
        @if ($errors->any() && old('organization_id') == $organization->id)
            window.addEventListener('DOMContentLoaded', function() {
                openAddContactModal();
            });
        @endif

        @if ($errors->any() && old('organization_id') == $organization->id && old('form') === 'add_document')
            window.addEventListener('DOMContentLoaded', function() {
                openAddDocumentModal();
            });
        @endif
    </script>
@endsection
