@extends('layouts.app')

@section('title', 'Домены REG.RU')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Домены REG.RU</h1>
                <p class="text-sm text-gray-500">Список доменов и сроков истечения</p>
            </div>
            <div>
                <form method="POST" action="{{ route('domains.sync') }}" class="inline">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center rounded border border-indigo-600 px-3 py-2 text-sm text-indigo-600 hover:bg-indigo-50">
                        Синхронизировать
                    </button>
                </form>
                <a href="{{ route('domains.create') }}"
                    class="inline-flex items-center rounded bg-indigo-600 px-3 py-2 text-sm text-white hover:bg-indigo-700">
                    Добавить домен
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-4 text-emerald-700 text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="rounded-lg bg-red-50 border border-red-200 p-4 text-red-700 text-sm">
                {{ session('error') }}
            </div>
        @endif

        <div class="rounded-xl bg-white p-5 shadow">
            @if ($domains->isEmpty())
                <p class="text-sm text-gray-500">Нет данных для отображения.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 border-b">
                                <th class="py-2 pr-4">Домен</th>
                                <th class="py-2 pr-4">Истекает</th>
                                <th class="py-2 pr-4">Статус</th>
                                <th class="py-2 pr-4">Продление</th>
                                <th class="py-2 pr-4">Проект</th>
                                <th class="py-2 pr-4">Источник</th>
                                <th class="py-2 pr-4">ID услуги</th>
                                <th class="py-2 pr-4">Действия</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-800">
                            @foreach ($domains as $domain)
                                <tr class="border-b last:border-b-0 {{ $domain->project ? '' : 'bg-red-50' }}">
                                    <td class="py-2 pr-4 font-medium {{ $domain->project ? '' : 'text-red-600' }}">
                                        {{ $domain->name ?? '—' }}
                                    </td>
                                    <td class="py-2 pr-4">
                                        {{ $domain->expires_at ? $domain->expires_at->format('d.m.Y') : '—' }}
                                    </td>
                                    <td class="py-2 pr-4">
                                        {{ $domain->status_label }}
                                    </td>
                                    <td class="py-2 pr-4">
                                        @php
                                            $currencyMap = [
                                                'RUR' => '₽',
                                                'RUB' => '₽',
                                                'USD' => '$',
                                                'EUR' => '€',
                                                'UAH' => '₴',
                                            ];
                                            $currencySymbol =
                                                $currencyMap[$domain->currency ?? 'RUR'] ??
                                                ($domain->currency ?? 'RUR');
                                        @endphp
                                        {{ $domain->renew_price !== null ? number_format((float) $domain->renew_price, 0, '.', ' ') . ' ' . $currencySymbol : '—' }}
                                    </td>
                                    <td class="py-2 pr-4 {{ $domain->project ? '' : 'text-red-600' }}">
                                        @if ($domain->project)
                                            <a href="{{ route('projects.show', $domain->project) }}"
                                                class="text-indigo-600 hover:underline">
                                                {{ $domain->project->title }}
                                            </a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="py-2 pr-4">
                                        {{ $domain->provider === 'reg_ru' ? 'REG.RU' : 'Вручную' }}
                                    </td>
                                    <td class="py-2 pr-4">
                                        {{ $domain->provider_service_id ?? '—' }}
                                    </td>
                                    <td class="py-2 pr-4">
                                        @if ($domain->provider === 'reg_ru')
                                            <button type="button" class="text-indigo-600 hover:underline"
                                                onclick="openProjectModal('{{ $domain->id }}')">
                                                Привязать проект
                                            </button>
                                        @else
                                            <button type="button" class="text-emerald-600 hover:underline mr-2"
                                                data-domain-hosting-open data-domain-id="{{ $domain->id }}"
                                                data-renew-price="{{ $domain->renew_price ?? '' }}"
                                                data-expires-at="{{ optional($domain->expires_at)->format('Y-m-d') }}">
                                                Продлить на год
                                            </button>
                                            <a href="{{ route('domains.edit', $domain) }}"
                                                class="text-indigo-600 hover:underline">Изменить</a>
                                            <form action="{{ route('domains.destroy', $domain) }}" method="POST"
                                                class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="ml-2 text-red-600 hover:underline"
                                                    onclick="return confirm('Удалить домен?')">Удалить</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Модалки привязки проекта для доменов REG.RU --}}
    @foreach ($domains as $domain)
        @if ($domain->provider === 'reg_ru')
            <div id="projectModal-{{ $domain->id }}" class="fixed inset-0 z-50 hidden items-center justify-center">
                <div class="fixed inset-0 bg-black/50" onclick="closeProjectModal('{{ $domain->id }}')"></div>
                <div class="bg-white rounded shadow-lg w-full max-w-lg mx-4 z-10" role="dialog" aria-modal="true">
                    <div class="p-4 border-b flex items-center justify-between">
                        <h3 class="text-lg font-semibold">Привязка проекта</h3>
                        <button type="button" class="text-gray-600"
                            onclick="closeProjectModal('{{ $domain->id }}')">✕</button>
                    </div>
                    <form method="POST" action="{{ route('domains.update', $domain) }}" class="p-4 space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="block text-sm text-gray-500 mb-1">Проект</label>
                            <select name="project_id" class="w-full border rounded px-3 py-2 js-project-select">
                                <option value="">— Не выбран —</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}" @selected($domain->project_id === $project->id)>
                                        {{ $project->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="button" class="px-3 py-2 text-sm border rounded"
                                onclick="closeProjectModal('{{ $domain->id }}')">Отмена</button>
                            <button type="submit"
                                class="px-3 py-2 text-sm bg-indigo-600 text-white rounded">Сохранить</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endforeach

    @if (isset($domainHostingCategories) && $domainHostingCategories->count())
        @include('admin.expenses._domain_hosting_modal')
    @endif

    <script>
        function openProjectModal(id) {
            const modal = document.getElementById('projectModal-' + id);
            if (!modal) return;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeProjectModal(id) {
            const modal = document.getElementById('projectModal-' + id);
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    </script>
@endsection
