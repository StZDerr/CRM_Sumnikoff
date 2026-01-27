@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-semibold">Проекты — должники</h1>
            <a href="{{ route('projects.index') }}"
                class="inline-flex items-center px-4 py-2 rounded-md border border-indigo-500 text-indigo-600 text-sm font-medium hover:bg-indigo-50 transition">←
                К списку проектов</a>
        </div>

        <div class="bg-white rounded shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3 text-left">Проект</th>
                        <th class="p-3 text-left">Организация</th>
                        <th class="p-3 text-left">Маркетолог</th>
                        <th class="p-3 text-left">Дата закрытия</th>
                        <th class="p-3 text-left">Счета</th>
                        <th class="p-3 text-left">Оплачено</th>
                        <th class="p-3 text-left">Долг</th>
                        <th class="p-3 text-left">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($projects as $project)
                        @php
                            $invoicesTotal = $project->invoices()->sum('amount');
                            $paymentsTotal = $project->payments()->sum('amount');
                            $debt = $invoicesTotal - $paymentsTotal;
                        @endphp

                        <tr class="border-t">
                            <td class="p-3"><a href="{{ route('projects.show', $project) }}"
                                    class="text-indigo-600 hover:underline">{{ $project->title }}</a></td>
                            <td class="p-3">
                                @if ($project->organization)
                                    <a href="{{ route('organizations.show', $project->organization) }}"
                                        class="text-indigo-600 hover:underline">
                                        {{ $project->organization->name_short ?? ($project->organization->name_full ?? '-') }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="p-3">{{ $project->marketer?->name ?? '-' }}</td>
                            <td class="p-3">{{ $project->closed_at?->format('d.m.Y') ?? '—' }}</td>
                            <td class="p-3">{{ number_format($invoicesTotal, 0, '.', ' ') }} ₽</td>
                            <td class="p-3">{{ number_format($paymentsTotal, 0, '.', ' ') }} ₽</td>
                            <td class="p-3">
                                @if ($debt > 0)
                                    <button type="button"
                                        class="inline-flex items-center px-2 py-1 bg-red-600 text-white rounded text-sm"
                                        data-tippy
                                        data-tippy-content="Счета: {{ number_format($invoicesTotal, 0, '.', ' ') }} ₽<br>Оплачено: {{ number_format($paymentsTotal, 0, '.', ' ') }} ₽<br>Долг: {{ number_format($debt, 0, '.', ' ') }} ₽">Долг:
                                        {{ number_format($debt, 0, '.', ' ') }} ₽</button>
                                @else
                                    <span class="text-sm text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="p-3">
                                <a href="{{ route('projects.show', $project) }}"
                                    class="text-indigo-600 hover:text-indigo-800 text-sm mr-3">Просмотр</a>

                                @if ($project->lawyerAssignments->isNotEmpty())
                                    <span class="text-sm text-gray-600 ml-3">Уже отправленно</span>
                                @else
                                    <button type="button"
                                        class="text-sm inline-flex items-center px-2 py-1 bg-indigo-600 text-white rounded"
                                        data-send-to-lawyer data-project-id="{{ $project->id }}"
                                        data-project-title="{{ $project->title }}">
                                        Отправить юристу
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-4 text-gray-500">Нет проектов с долгом.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $projects->links() }}</div>
    </div>

    <!-- Modal: Send to lawyer -->
    <div id="sendToLawyerModal" class="fixed inset-0 z-50 items-center justify-center hidden">
        <div class="absolute inset-0 bg-black opacity-40" data-modal-close></div>
        <div class="relative bg-white rounded shadow max-w-lg mx-auto my-10 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold modal-title">Отправить проект</h3>
                <button type="button" class="text-gray-500" data-modal-close>✕</button>
            </div>

            <form id="sendToLawyerForm" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="block text-sm text-gray-700">Юрист</label>
                    <select name="user_id" class="w-full border rounded px-2 py-1" required>
                        <option value="">— Выберите юриста —</option>
                        @foreach ($lawyers as $lawyer)
                            <option value="{{ $lawyer->id }}">{{ $lawyer->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="block text-sm text-gray-700">Заметка (опционально)</label>
                    <textarea name="note" class="w-full border rounded px-2 py-1" rows="3"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-2">
                    <button type="button" class="px-3 py-1 rounded border" data-modal-close>Отмена</button>
                    <button type="submit" class="px-3 py-1 rounded bg-indigo-600 text-white">Отправить</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('[data-send-to-lawyer]');
            if (btn) {
                const projectId = btn.dataset.projectId;
                const title = btn.dataset.projectTitle;
                const modal = document.getElementById('sendToLawyerModal');
                modal.querySelector('.modal-title').textContent = 'Отправить: ' + title;
                const form = document.getElementById('sendToLawyerForm');
                form.action = '/projects/' + projectId + '/send-to-lawyer';
                modal.classList.remove('hidden');
            }

            if (e.target.closest('[data-modal-close]')) {
                const modal = e.target.closest('#sendToLawyerModal');
                if (modal) modal.classList.add('hidden');
            }
        });
    </script>
@endsection
