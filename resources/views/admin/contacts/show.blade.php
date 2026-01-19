@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold">Контакт: {{ $contact->full_name ?: '—' }}</h1>
            <a href="{{ route('contacts.index') }}"
                class="inline-flex items-center px-3 py-2 border rounded hover:bg-gray-50">← Назад</a>
        </div>

        <div class="bg-white shadow rounded p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <div class="text-sm text-gray-500">Фамилия</div>
                    <div class="font-medium">{{ $contact->last_name ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Имя</div>
                    <div class="font-medium">{{ $contact->first_name ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Отчество</div>
                    <div class="font-medium">{{ $contact->middle_name ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Должность</div>
                    <div class="font-medium">{{ $contact->position ?? '-' }}</div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Телефон</div>
                    <div class="font-medium">{{ $contact->phone ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Email</div>
                    <div class="font-medium">{{ $contact->email ?? '-' }}</div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Мессенджер (приоритет)</div>
                    <div class="font-medium">
                        {{ $contact->preferred_messenger ? ucfirst($contact->preferred_messenger) : '-' }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Контакт в мессенджере</div>
                    <div class="font-medium">{{ $contact->messenger_contact ?? '-' }}</div>
                </div>

                <div class="col-span-2">
                    <div class="text-sm text-gray-500">Комментарий</div>
                    <div class="font-medium whitespace-pre-line">{{ $contact->comment ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Создал</div>
                    <div class="font-medium">
                        {{ $contact->createdBy?->name ?? '-' }}
                        @if ($contact->created_at)
                            <div class="text-xs text-gray-400 mt-1">{{ $contact->created_at->format('d.m.Y H:i') }}</div>
                        @endif
                    </div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Последнее обновление</div>
                    <div class="font-medium">
                        {{ $contact->updatedBy?->name ?? '-' }}
                        @if ($contact->updated_at)
                            <div class="text-xs text-gray-400 mt-1">{{ $contact->updated_at->format('d.m.Y H:i') }}</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Паспорт РФ -->
            @if (
                $contact->passport_series ||
                    $contact->passport_number ||
                    $contact->passport_issued_at ||
                    $contact->passport_issued_by ||
                    $contact->passport_department_code ||
                    $contact->passport_birth_place)
                <hr class="my-4">

                <h2 class="text-lg font-semibold mb-3">Паспорт РФ</h2>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="text-sm text-gray-500">Серия</div>
                        <div class="font-medium">{{ $contact->passport_series ?? '-' }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Номер</div>
                        <div class="font-medium">{{ $contact->passport_number ?? '-' }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Дата выдачи</div>
                        <div class="font-medium">{{ $contact->passport_issued_at?->format('d.m.Y') ?? '-' }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Код подразделения</div>
                        <div class="font-medium">{{ $contact->passport_department_code ?? '-' }}</div>
                    </div>

                    <div class="col-span-2">
                        <div class="text-sm text-gray-500">Кем выдан</div>
                        <div class="font-medium">{{ $contact->passport_issued_by ?? '-' }}</div>
                    </div>

                    <div class="col-span-2">
                        <div class="text-sm text-gray-500">Место рождения</div>
                        <div class="font-medium">{{ $contact->passport_birth_place ?? '-' }}</div>
                    </div>
                </div>
            @endif



            <div class="flex gap-3">
                <a href="{{ route('contacts.edit', $contact) }}"
                    class="inline-flex items-center px-3 py-2 border rounded hover:bg-gray-50">Редактировать</a>
                @if (auth()->user()->isAdmin())
                    <form action="{{ route('contacts.destroy', $contact) }}" method="POST"
                        onsubmit="return confirm('Удалить контакт?');">
                        @csrf
                        @method('DELETE')
                        <button class="text-red-600 hover:text-red-800 px-3 py-2 border rounded">Удалить</button>
                    </form>
                @endif
            </div>

        </div>
    </div>
@endsection
