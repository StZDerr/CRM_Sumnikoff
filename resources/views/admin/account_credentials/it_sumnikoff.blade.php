@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">Доступы IT Sumnikoff</h1>
        <a href="{{ route('account-credentials.createItSumnikoff') }}"
            class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">+ Добавить
            доступ</a>
    </div>

    @if (session('success'))
        <div class="mb-4 p-3 rounded bg-emerald-100 text-emerald-800">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="p-3 text-left">Название</th>
                    <th class="p-3 text-left">Логин</th>
                    <th class="p-3 text-left">Пароль</th>
                    <th class="p-3 text-left">Статус</th>
                    <th class="p-3 text-left">Действия</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($accountCredential as $cred)
                    <tr class="border-t">
                        <td class="p-3">{{ $cred->name }}</td>
                        <td class="p-3">
                            <button type="button" class="text-gray-800 hover:text-indigo-700 underline"
                                data-copy-text="{{ $cred->login }}">{{ $cred->login ?: '—' }}</button>
                        </td>
                        <td class="p-3">
                            <button type="button" class="text-gray-500 hover:text-gray-800 underline" data-password-toggle
                                data-password="{{ $cred->password }}">••••••••</button>
                        </td>
                        <td class="p-3">{{ $cred->status == 'active' ? 'Действующий' : 'Stop List' }}</td>
                        <td class="p-3 space-x-2">
                            <a href="{{ route('account-credentials.showItSumnikoff', $cred) }}"
                                class="text-blue-600 hover:underline">Просмотр</a>
                            @if (auth()->user()->isAdmin() || auth()->user()->isProjectManager())
                                <a href="{{ route('account-credentials.editItSumnikoff', $cred) }}"
                                    class="text-indigo-600 hover:underline">Редактировать</a>
                                <form action="{{ route('account-credentials.destroy', $cred) }}" method="POST"
                                    class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline"
                                        onclick="return confirm('Удалить доступ?')">Удалить</button>
                                </form>
                            @endif

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-4 text-gray-500">Пока нет доступов IT Sumnikoff.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div id="copyToast"
        class="fixed bottom-4 right-4 z-50 hidden rounded-lg bg-emerald-600 px-4 py-2 text-sm text-white shadow-lg">
        Скопировано</div>

    <script>
        const toast = document.getElementById('copyToast');

        function showToast(text) {
            if (!toast) return;
            toast.textContent = text || 'Скопировано';
            toast.classList.remove('hidden');
            clearTimeout(window.__copyToastTimer);
            window.__copyToastTimer = setTimeout(() => toast.classList.add('hidden'), 1500);
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

        document.querySelectorAll('[data-copy-text]').forEach(btn => btn.addEventListener('click', () => copyText(btn
            .dataset.copyText || '')));
        document.querySelectorAll('[data-password-toggle]').forEach(btn => btn.addEventListener('click', () => {
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
        }));
    </script>
@endsection
