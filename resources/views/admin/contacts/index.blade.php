@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold">Контакты</h1>
            <a href="{{ route('contacts.create') }}"
                class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Добавить</a>
        </div>

        <div class="bg-white shadow rounded">
            <div class="p-4 border-b flex items-center justify-between gap-4">
                <form method="GET" action="{{ route('contacts.index') }}" class="flex items-center gap-2">
                    <input type="search" name="q" value="{{ $q ?? '' }}"
                        placeholder="Поиск по имени, телефону, email..." class="border rounded px-3 py-2 w-72 text-sm" />
                    <button class="px-3 py-2 bg-gray-100 rounded text-sm">Поиск</button>
                </form>
                <div class="text-sm text-gray-500">Всего: {{ $contacts->total() }}</div>
            </div>

            <div class="divide-y">
                @forelse($contacts as $contact)
                    <div class="flex items-center gap-4 px-4 py-3 hover:bg-gray-50">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-medium text-gray-900">{{ $contact->full_name ?: '—' }}</div>
                                    <div class="text-xs text-gray-500">
                                        {{ $contact->position ? $contact->position . ' • ' : '' }}
                                        {{ $contact->phone ? $contact->phone . ' • ' : '' }}
                                        {{ $contact->email ?? '-' }}
                                    </div>
                                </div>

                            </div>

                            @if ($contact->preferred_messenger || $contact->messenger_contact)
                                <div class="text-xs text-gray-500 mt-2">
                                    <strong>Мессенджер:</strong> {{ ucfirst($contact->preferred_messenger ?? '-') }}
                                    @if ($contact->messenger_contact)
                                        — {{ $contact->messenger_contact }}
                                    @endif
                                </div>
                            @endif
                            @if ($contact->comment)
                                <div class="text-xs text-gray-500 mt-1">{{ Str::limit($contact->comment, 140) }}</div>
                            @endif
                        </div>

                        <div class="text-sm text-gray-400">
                            {{ $contact->organization?->name_short ?: $contact->organization?->name_full ?? '-' }}
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="{{ route('contacts.show', $contact) }}"
                                class="text-indigo-600 hover:text-indigo-800 text-sm p-2">Просмотр</a>
                            <a href="{{ route('contacts.edit', $contact) }}"
                                class="text-indigo-600 hover:text-indigo-800 text-sm p-2">Редактировать</a>

                            <form action="{{ route('contacts.destroy', $contact) }}" method="POST"
                                onsubmit="return confirm('Удалить контакт?');">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 hover:text-red-800 text-sm p-2">Удалить</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-6 text-gray-500">Пока нет контактов.</div>
                @endforelse
            </div>

            <div class="p-4">
                {{ $contacts->links() }}
            </div>
        </div>
    </div>
@endsection
