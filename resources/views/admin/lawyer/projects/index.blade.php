@extends('layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-semibold">Проекты — для юриста</h1>
        </div>

        <div class="bg-white rounded shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3 text-left">Проект</th>

                        @if (auth()->user()->isAdmin())
                            <th class="p-3 text-left">Юрист</th>
                        @endif

                        <th class="p-3 text-left">Организация</th>
                        <th class="p-3 text-left">Отправлено</th>
                        <th class="p-3 text-left">Заметка</th>
                        <th class="p-3 text-left">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projects as $assignment)
                        <tr class="border-t">
                            <td class="p-3">
                                <a href="{{ route('lawyer.projects.show', $assignment) }}"
                                    class="text-indigo-600 hover:underline">{{ $assignment->project->title }}</a>
                            </td>

                            @if (auth()->user()->isAdmin())
                                <td class="p-3">{{ $assignment->lawyer?->name ?? '-' }}</td>
                            @endif

                            <td class="p-3">{{ $assignment->project->organization->name_short ?? '-' }}</td>
                            <td class="p-3">
                                {{ optional($assignment->sent_at)->format('d.m.Y H:i') ?? ($assignment->sent_at ?? '-') }}
                                ({{ $assignment->sender?->name ?? '—' }})</td>
                            <td class="p-3">{{ $assignment->note ?? '-' }}</td>
                            <td class="p-3">
                                <form method="POST" action="{{ route('lawyer.projects.update', $assignment) }}"
                                    class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="processed">
                                    <button class="px-2 py-1 bg-green-600 text-white rounded text-sm">Отметить как
                                        обработано</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-4 text-gray-500">Нет назначенных проектов.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
