@extends('layouts.app')

@section('content')
    <h1 class="text-xl font-bold mb-4">Доступы проекта {{ $project->title }}</h1>

    {{-- Панель кнопок добавления разных типов доступа --}}
    <div class="flex flex-wrap gap-2 mb-4">
        <a href="{{ route('account-credentials.createSite', ['project' => $project, 'type' => 'website_user']) }}"
            class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">
            + Доступ
        </a>
        <a href="{{ route('account-credentials.createBD', ['project' => $project, 'type' => 'database']) }}"
            class="inline-flex items-center px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">
            + Доступ к БД
        </a>
        <a href="{{ route('account-credentials.createSSH', ['project' => $project, 'type' => 'ssh']) }}"
            class="inline-flex items-center px-4 py-2 rounded-lg bg-purple-600 text-white text-sm font-medium hover:bg-purple-700">
            + Доступ к SSH
        </a>
        <a href="{{ route('account-credentials.createFTP', ['project' => $project, 'type' => 'ftp']) }}"
            class="inline-flex items-center px-4 py-2 rounded-lg bg-green-600 text-white text-sm font-medium hover:bg-green-700">
            + Доступ к FTP
        </a>
    </div>

    {{-- Таблица с доступами --}}
    <table class="min-w-full divide-y divide-gray-200 border">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Тип</th>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Название</th>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Логин</th>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Пароль</th>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Организация</th>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Статус</th>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Действия</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach ($credentials as $cred)
                <tr>
                    <td class="px-4 py-2 capitalize">{{ str_replace('_', ' ', $cred->type) }}</td>
                    <td class="px-4 py-2">{{ $cred->name }}</td>
                    <td class="px-4 py-2">
                        <button type="button" class="text-gray-800 hover:text-indigo-700 underline"
                            data-copy-text="{{ $cred->login }}">
                            {{ $cred->login ?: '—' }}
                        </button>
                    </td>
                    <td class="px-4 py-2">
                        <button type="button" class="text-gray-500 hover:text-gray-800 underline" data-password-toggle
                            data-password="{{ $cred->password }}">
                            ••••••••
                        </button>
                    </td>
                    <td class="px-4 py-2">{{ $cred->organization->name_full ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $cred->status == 'active' ? 'Действующий' : 'Stop List' }}</td>
                    <td class="px-4 py-2 space-x-1">
                        <a href="{{ route('account-credentials.show', $cred) }}"
                            class="text-blue-600 hover:underline">Просмотр</a>
                        <a href="{{ route('account-credentials.edit', $cred) }}"
                            class="text-indigo-600 hover:underline">Редактировать</a>
                        <form action="{{ route('account-credentials.destroy', $cred) }}" method="POST"
                            class="inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline"
                                onclick="return confirm('Удалить доступ?')">Удалить</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $credentials->links() }}

    <div id="copyToast"
        class="fixed bottom-4 right-4 z-50 hidden rounded-lg bg-emerald-600 px-4 py-2 text-sm text-white shadow-lg">
        Скопировано
    </div>

    <script>
        const toast = document.getElementById('copyToast');

        function showToast(text) {
            if (!toast) return;
            toast.textContent = text || 'Скопировано';
            toast.classList.remove('hidden');
            clearTimeout(window.__copyToastTimer);
            window.__copyToastTimer = setTimeout(() => {
                toast.classList.add('hidden');
            }, 1500);
        }

        async function copyText(value) {
            if (!value) return;
            try {
                await navigator.clipboard.writeText(value);
                showToast('Скопировано');
            } catch (e) {
                const textarea = document.createElement('textarea');
                textarea.value = value;
                textarea.style.position = 'fixed';
                textarea.style.opacity = '0';
                document.body.appendChild(textarea);
                textarea.select();
                try {
                    document.execCommand('copy');
                    showToast('Скопировано');
                } finally {
                    document.body.removeChild(textarea);
                }
            }
        }

        document.querySelectorAll('[data-copy-text]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const val = btn.dataset.copyText || '';
                if (!val) return;
                copyText(val);
            });
        });

        document.querySelectorAll('[data-password-toggle]').forEach((btn) => {
            btn.addEventListener('click', () => {
                if (btn.dataset.revealed === '1') {
                    const val = btn.dataset.password || '';
                    if (!val) return;
                    copyText(val);
                    return;
                }
                if (!confirm('Открыть пароль?')) return;
                btn.textContent = btn.dataset.password || '—';
                btn.classList.remove('text-gray-500');
                btn.classList.add('text-gray-900');
                btn.dataset.revealed = '1';
            });
        });
    </script>
@endsection
