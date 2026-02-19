@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <h1 class="text-2xl font-semibold text-gray-800">Аккаунты Avito</h1>

            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('avito.accounts.sync-all') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-full bg-gray-700 text-white text-sm shadow">
                        Синхронизировать все
                    </button>
                </form>

                    <button id="open-avito-modal" type="button"
                        class="px-4 py-2 rounded-full bg-indigo-600 text-white text-sm shadow">
                        Добавить аккаунт
                    </button>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-xl border border-green-200 bg-green-50 text-green-800 px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 text-red-800 px-4 py-3 text-sm">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 text-red-800 px-4 py-3 text-sm">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($accounts as $account)
                @php
                    $profile = $account->profile_data ?? [];
                    $stats = $account->stats_data ?? [];
                    $operations = (array) data_get($stats, 'operations', []);

                    $sumOnAccount = (float) data_get($stats, 'sum_on_account', 0);
                    $advance = (float) data_get($stats, 'advance', 0);
                    $viewsToday = (int) data_get($stats, 'views_today', 0);
                    $contactsToday = (int) data_get($stats, 'contacts_today', 0);
                @endphp

                @php $cardClass = $account->project ? 'bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden' : 'bg-white rounded-2xl border border-red-300 bg-red-50 shadow-sm overflow-hidden'; @endphp
                <div class="{{ $cardClass }}">
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">{{ $account->label }}</h3>

                                @unless ($account->project)
                                    <div class="mt-2 inline-block text-xs bg-red-100 text-red-700 px-2 py-1 rounded">Не привязан
                                        к проекту</div>
                                @endunless

                                @if ($account->project)
                                    <div class="text-xs text-gray-500 mt-1">Проект: <a
                                            href="{{ route('projects.show', $account->project) }}"
                                            class="text-indigo-600 hover:underline">{{ $account->project->title }}</a></div>

                                    <div class="text-xs text-gray-500 mt-1">Ответственный:
                                        <span class="font-medium text-gray-800">
                                            @if ($account->project->marketer)
                                                <a href="{{ route('user.dashboard', $account->project->marketer) }}"
                                                    class="text-indigo-600 hover:underline">{{ $account->project->marketer->name }}</a>
                                            @else
                                                —
                                            @endif
                                        </span>
                                    </div>
                                @else
                                    @if (auth()->user()?->isAdmin() || auth()->user()?->isProjectManager())
                                        <div class="mt-2">
                                            <button type="button"
                                                class="attach-project-open px-3 py-1 rounded-full bg-emerald-50 text-emerald-600 text-xs"
                                                data-account-id="{{ $account->id }}"
                                                data-account-label="{{ $account->label }}">Привязать проект</button>
                                        </div>
                                    @endif
                                @endif

                                <div class="text-xs text-gray-400 mt-1">
                                    Avito ID:
                                    <span class="text-gray-600">{{ data_get($profile, 'id', '—') }}</span>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('avito.accounts.sync', $account) }}">
                                @csrf
                                <button class="px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-full text-xs font-medium">
                                    Обновить
                                </button>
                            </form>
                        </div>

                        <div class="mt-4 grid grid-cols-3 gap-4 text-sm text-gray-500">
                            <div>
                                <div class="text-xs">Сумма на аккаунте</div>
                                <div class="text-base font-semibold text-gray-800 mt-1">
                                    {{ number_format($sumOnAccount, 0, ',', ' ') }} ₽
                                </div>
                                <div class="mt-1 text-[11px] text-gray-400">(Кошелёк:
                                    {{ number_format((float) data_get($stats, 'wallet', 0), 2, ',', ' ') }} ₽ + Аванс:
                                    {{ number_format((float) data_get($stats, 'advance', 0), 2, ',', ' ') }} ₽)</div>
                            </div>
                            <div>
                                <div class="text-xs">Аванс</div>
                                <div class="text-base font-semibold text-orange-500 mt-1">
                                    {{ number_format($advance, 0, ',', ' ') }} ₽
                                </div>
                            </div>
                            <div>
                                <div class="text-xs">Траты (сегодня)</div>
                                <div class="text-base font-semibold text-red-500 mt-1">
                                    {{ number_format((float) data_get($stats, 'spending_today', 0), 2, ',', ' ') }} ₽
                                </div>
                                <div class="mt-1 text-[11px] text-gray-400">
                                    Размещение:
                                    {{ number_format((float) data_get($stats, 'spending_placement_today', 0), 2, ',', ' ') }}
                                    ₽
                                </div>
                                <div class="text-[11px] text-gray-400">
                                    Целевые просмотры:
                                    {{ number_format((float) data_get($stats, 'spending_views_today', 0), 2, ',', ' ') }} ₽
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 bg-gray-50 rounded-lg p-2">
                            <div class="grid grid-cols-5 gap-2 text-center">
                                <div class="bg-white rounded-lg p-1 shadow-sm overflow-hidden">
                                    <div class="text-[10px] text-gray-400">ПРОСМОТРЫ</div>
                                    <div class="text-sm font-medium leading-tight text-indigo-600">
                                        {{ number_format($viewsToday, 0, ',', ' ') }}</div>
                                </div>

                                <div class="bg-white rounded-lg p-1 shadow-sm overflow-hidden">
                                    <div class="text-[10px] text-gray-400">КОНТАКТЫ</div>
                                    <div class="text-sm font-medium leading-tight text-green-600">
                                        {{ number_format($contactsToday, 0, ',', ' ') }}</div>
                                </div>

                                <div class="bg-white rounded-lg p-1 shadow-sm overflow-hidden">
                                    <div class="text-[10px] text-gray-400">CTR</div>
                                    <div class="text-sm font-medium leading-tight text-purple-600">
                                        {{ number_format((float) data_get($stats, 'ctr', 0), 2, ',', ' ') }}%</div>
                                </div>

                                <div class="bg-white rounded-lg p-1 shadow-sm overflow-hidden">
                                    <div class="text-[10px] text-gray-400">Траты/день</div>
                                    <div class="text-sm font-medium leading-tight text-red-500">
                                        {{ number_format((float) data_get($stats, 'spending_per_day', 0), 0, ',', ' ') }} ₽
                                    </div>
                                    <div class="mt-1 text-[10px] text-gray-400">среднее за
                                        {{ data_get($stats, 'spending_period_days', 7) }} дн.</div>
                                </div>

                                <div class="bg-white rounded-lg p-1 shadow-sm overflow-hidden">
                                    <div class="text-[10px] text-gray-400">ЦЕНА/КОНТ.</div>
                                    <div class="text-sm font-medium leading-tight text-gray-700">
                                        {{ number_format((float) data_get($stats, 'cost_per_contact', 0), 0, ',', ' ') }} ₽
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 bg-gray-50 rounded-lg p-3">
                            <div class="flex items-center justify-between mb-2">
                                <div class="text-xs font-semibold text-gray-500">Последние операции (14 дней)</div>
                                <button type="button" class="text-xs text-indigo-600 hover:underline toggle-ops"
                                    data-account-id="{{ $account->id }}" aria-expanded="false">Показать</button>
                            </div>

                            <div id="ops-{{ $account->id }}" class="hidden">
                                @if (!empty($operations))
                                    <div class="space-y-2">
                                        @foreach (array_slice($operations, 0, 3) as $operation)
                                            @php
                                                $opDate = data_get($operation, 'updated_at');
                                                $opName = data_get($operation, 'operation_name', 'Операция');
                                                $opType = (string) data_get($operation, 'operation_type', '');
                                                $opAmount = (float) data_get($operation, 'amount_rub', 0);
                                                $isCredit =
                                                    str_contains(mb_strtolower($opType), 'аванс') ||
                                                    str_contains(mb_strtolower($opType), 'пополн');
                                            @endphp

                                            <div class="bg-white rounded-lg p-2.5 border border-gray-100">
                                                <div class="flex items-center justify-between gap-2">
                                                    <div class="text-xs text-gray-700 truncate">{{ $opName }}</div>
                                                    <div
                                                        class="text-xs font-semibold {{ $isCredit ? 'text-green-600' : 'text-red-500' }}">
                                                        {{ $isCredit ? '+' : '-' }}{{ number_format($opAmount, 2, ',', ' ') }}
                                                        ₽
                                                    </div>
                                                </div>
                                                <div class="mt-1 text-[11px] text-gray-400">
                                                    {{ $opDate ? \Illuminate\Support\Carbon::parse($opDate)->format('d.m.Y H:i') : '—' }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-xs text-gray-400">Операций за выбранный период не найдено.</div>
                                @endif
                            </div>
                        </div>

                        @if (data_get($stats, 'error'))
                            <div class="mt-4 rounded-lg border border-red-200 bg-red-50 text-red-700 text-xs px-3 py-2">
                                {{ data_get($stats, 'error') }}
                            </div>
                        @endif
                    </div>

                    <div class="border-t border-gray-100 px-5 py-3 text-xs text-gray-400">
                        Обновлено: {{ optional($account->last_synced_at)->format('d.m.Y H:i') ?? '—' }}
                    </div>
                </div>
            @empty
                <div
                    class="col-span-full bg-white rounded-2xl border border-dashed border-gray-200 p-10 text-center text-gray-500">
                    Аккаунтов Avito пока нет. Добавьте первый аккаунт.
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $accounts->links() }}
        </div>

            <dialog id="avito-modal" class="rounded-2xl p-0 w-full max-w-md backdrop:bg-black/40">
                <div class="bg-white rounded-2xl overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-800">Добавить аккаунт Avito</h2>
                        <button id="close-avito-modal" type="button" class="text-gray-400 hover:text-gray-600">✕</button>
                    </div>

                    <form method="POST" action="{{ route('avito.accounts.store') }}" class="p-5 space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Название в CRM</label>
                            <input name="label" type="text" value="{{ old('label') }}" required
                                class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none" />
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Client ID</label>
                            <input name="client_id" type="text" value="{{ old('client_id') }}" required
                                class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none" />
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Client Secret</label>
                            <input name="client_secret" type="password" value="{{ old('client_secret') }}" required
                                class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none" />
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2">
                            <button id="cancel-avito-modal" type="button"
                                class="px-4 py-2 rounded-lg border border-gray-200 text-sm text-gray-600">Отмена</button>
                            <button type="submit"
                                class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm">Сохранить</button>
                        </div>
                    </form>
                </div>
            </dialog>

        {{-- Attach project modal (available to admin / project_manager) --}}
        @if (auth()->user()?->isAdmin() || auth()->user()?->isProjectManager())
            <dialog id="attach-project-modal" class="rounded-2xl p-0 w-full max-w-md backdrop:bg-black/40">
                <div class="bg-white rounded-2xl overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h2 id="attach-project-title" class="text-lg font-semibold text-gray-800">Привязать проект</h2>
                        <button id="close-attach-project-modal" type="button"
                            class="text-gray-400 hover:text-gray-600">✕</button>
                    </div>

                    <form id="attach-project-form" method="POST" action="#" class="p-5 space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Выберите проект</label>
                            <select name="project_id" id="attach-project-select" required
                                class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none">
                                <option value="">— выбрать проект —</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2">
                            <button id="cancel-attach-project-modal" type="button"
                                class="px-4 py-2 rounded-lg border border-gray-200 text-sm text-gray-600">Отмена</button>
                            <button type="submit"
                                class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm">Сохранить</button>
                        </div>
                    </form>
                </div>
            </dialog>
        @endif
    </div>

        <script>
            (function() {
                const modal = document.getElementById('avito-modal');
                const openBtn = document.getElementById('open-avito-modal');
                const closeBtn = document.getElementById('close-avito-modal');
                const cancelBtn = document.getElementById('cancel-avito-modal');

                if (!modal || !openBtn) {
                    return;
                }

                openBtn.addEventListener('click', () => modal.showModal());
                closeBtn?.addEventListener('click', () => modal.close());
                cancelBtn?.addEventListener('click', () => modal.close());

                modal.addEventListener('click', (event) => {
                    const rect = modal.getBoundingClientRect();
                    const isInside = (
                        event.clientX >= rect.left &&
                        event.clientX <= rect.right &&
                        event.clientY >= rect.top &&
                        event.clientY <= rect.bottom
                    );

                    if (!isInside) {
                        modal.close();
                    }
                });

                @if ($errors->any())
                    modal.showModal();
                @endif
            })();
        </script>

    @if (auth()->user()?->isAdmin() || auth()->user()?->isProjectManager())
        <script>
            (function() {
                const attachModal = document.getElementById('attach-project-modal');
                const attachForm = document.getElementById('attach-project-form');
                const attachSelect = document.getElementById('attach-project-select');
                const openBtns = document.querySelectorAll('.attach-project-open');
                const closeBtn = document.getElementById('close-attach-project-modal');
                const cancelBtn = document.getElementById('cancel-attach-project-modal');
                const title = document.getElementById('attach-project-title');

                if (!attachModal || !attachForm) return;

                openBtns.forEach(btn => {
                    btn.addEventListener('click', () => {
                        const accountId = btn.getAttribute('data-account-id');
                        const accountLabel = btn.getAttribute('data-account-label');
                        attachForm.action = `/avito/accounts/${accountId}/attach-project`;
                        title.textContent = `Привязать проект — ${accountLabel}`;
                        attachSelect.value = '';
                        attachModal.showModal();
                    });
                });

                closeBtn?.addEventListener('click', () => attachModal.close());
                cancelBtn?.addEventListener('click', () => attachModal.close());

                attachModal.addEventListener('click', (event) => {
                    const rect = attachModal.getBoundingClientRect();
                    const isInside = (
                        event.clientX >= rect.left &&
                        event.clientX <= rect.right &&
                        event.clientY >= rect.top &&
                        event.clientY <= rect.bottom
                    );

                    if (!isInside) attachModal.close();
                });
            })();
        </script>
    @endif

    <script>
        (function() {
            // Toggle operations block per account (available to all authenticated users)
            document.querySelectorAll('.toggle-ops').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.getAttribute('data-account-id');
                    const container = document.getElementById('ops-' + id);
                    if (!container) return;

                    const isHidden = container.classList.toggle('hidden');
                    btn.setAttribute('aria-expanded', (!isHidden).toString());
                    btn.textContent = isHidden ? 'Показать' : 'Скрыть';
                });
            });
        })();
    </script>
@endsection
