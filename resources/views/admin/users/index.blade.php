@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-semibold">Пользователи</h1>
            <a href="{{ route('users.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded">Создать</a>
        </div>

        <div class="bg-white rounded shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3 text-left">ID</th>
                        <th class="p-3 text-left">Имя</th>
                        <th class="p-3 text-left">Логин</th>
                        <th class="p-3 text-left">Email</th>
                        <th class="p-3 text-left">Роль</th>
                        <th class="p-3 text-left">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr class="border-t">
                            <td class="p-3">{{ $user->id }}</td>
                            <td class="p-3">{{ $user->name }}</td>
                            <td class="p-3">{{ $user->login }}</td>
                            <td class="p-3">{{ $user->email ?? '-' }}</td>
                            <td class="p-3">{{ $user->role === 'admin' ? 'Администратор' : 'Менеджер' }}</td>
                            <td class="p-3">
                                <a href="{{ route('users.edit', $user) }}"
                                    class="text-indigo-600 hover:underline">Редактировать</a>
                                <a href="#" class="text-indigo-600 hover:underline open-vacation"
                                    data-user-id="{{ $user->id }}" data-user-name="{{ e($user->name) }}">Отпуск</a>
                                <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline-block ms-3"
                                    onsubmit="return confirm('Удалить пользователя? Проекты пользователя будут перераспре')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 hover:underline">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </div>
    <!-- Vacation Offcanvas -->
    <div id="vacation-offcanvas" class="fixed inset-0 z-50 hidden">
        <div id="vacation-overlay" class="absolute inset-0 bg-black/50"></div>

        <div id="vacation-panel"
            class="absolute right-0 top-0 h-full w-full sm:w-96 bg-white shadow-lg transform translate-x-full transition-transform">
            <div class="p-4 flex items-center justify-between border-b">
                <h3 class="text-lg font-medium">Добавить отпуск — <span id="vacation-user-name"></span></h3>
                <button id="vacation-close" class="text-gray-600">✕</button>
            </div>

            <form id="vacation-form" action="{{ route('vacations.store') }}" method="POST" class="p-4">
                @csrf
                <input type="hidden" name="user_id" id="vacation-user-id" value="">

                <div class="mb-3">
                    <label class="block text-sm text-gray-600">С</label>
                    <input type="date" name="start_date" required class="w-full border rounded p-2" />
                </div>

                <div class="mb-3">
                    <label class="block text-sm text-gray-600">По</label>
                    <input type="date" name="end_date" required class="w-full border rounded p-2" />
                </div>

                <div class="mb-3">
                    <label class="block text-sm text-gray-600">Временный маркетолог (опционально)</label>
                    <select name="temp_marketer_id" class="w-full border rounded p-2">
                        <option value="">— назначить автоматически —</option>
                        @foreach ($marketers as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="block text-sm text-gray-600">Примечание (опционально)</label>
                    <textarea name="notes" rows="3" class="w-full border rounded p-2"></textarea>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" id="vacation-cancel" class="px-3 py-2 border rounded">Отмена</button>
                    <button type="submit" class="px-3 py-2 bg-indigo-600 text-white rounded">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const offcanvas = document.getElementById('vacation-offcanvas');
            const panel = document.getElementById('vacation-panel');
            const overlay = document.getElementById('vacation-overlay');
            const userNameEl = document.getElementById('vacation-user-name');
            const userIdInput = document.getElementById('vacation-user-id');
            const closeBtn = document.getElementById('vacation-close');
            const cancelBtn = document.getElementById('vacation-cancel');
            const form = document.getElementById('vacation-form');

            function openOffcanvas(userId, userName) {
                userIdInput.value = userId;
                userNameEl.textContent = userName;
                offcanvas.classList.remove('hidden');
                requestAnimationFrame(() => panel.classList.remove('translate-x-full'));
                form.querySelector('input[name="start_date"]')?.focus();
            }

            function closeOffcanvas() {
                panel.classList.add('translate-x-full');
                setTimeout(() => offcanvas.classList.add('hidden'), 240);
            }

            document.querySelectorAll('.open-vacation').forEach(el => {
                el.addEventListener('click', function(e) {
                    e.preventDefault();
                    const userId = this.dataset.userId;
                    const userName = this.dataset.userName;
                    openOffcanvas(userId, userName);
                });
            });

            overlay.addEventListener('click', closeOffcanvas);
            if (closeBtn) closeBtn.addEventListener('click', closeOffcanvas);
            if (cancelBtn) cancelBtn.addEventListener('click', closeOffcanvas);
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape') closeOffcanvas();
            });

            // AJAX submit
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                const fd = new FormData(form);
                try {
                    const res = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .content
                        },
                        body: fd
                    });
                    if (res.ok) {
                        // success — можно закрыть и показать сообщение
                        closeOffcanvas();
                        // опционально: обновить страницу или показать flash (здесь — перезагрузим список)
                        location.reload();
                    } else if (res.status === 422) {
                        const json = await res.json().catch(() => null);
                        alert(Object.values(json?.errors || {}).flat().join('\n'));
                    } else {
                        alert('Ошибка при сохранении отпуска.');
                    }
                } catch (err) {
                    console.error(err);
                    alert('Ошибка при сохранении отпуска.');
                }
            });
        });
    </script>
@endpush
