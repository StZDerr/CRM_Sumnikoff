<div class="h-full flex flex-col bg-gray-900 text-white">
    <!-- Logo -->
    <div class="px-4 py-6 flex items-center justify-center">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <img src="{{ asset('images/favicon/favicon-32x32-1.png') }}" alt="IT Sumnikoff" class="h-8 w-8" />
            <span class="font-semibold text-lg">IT Sumnikoff</span>
        </a>
    </div>

    <div class="border-b border-white/20 mx-4"></div>

    <!-- Links -->
    <nav class="flex-1 px-2 py-4 overflow-y-auto space-y-1">
        <div id="work-time-widget" class="mx-1 mb-3 rounded-lg border border-white/10 bg-white/5 p-3"
            data-state-url="{{ route('work-time.state') }}" data-start-day-url="{{ route('work-time.start-day') }}"
            data-start-break-url="{{ route('work-time.start-break') }}"
            data-end-break-url="{{ route('work-time.end-break') }}"
            data-save-report-url="{{ route('work-time.save-report') }}"
            data-end-day-url="{{ route('work-time.end-day') }}" data-edit-day-url="{{ url('work-time/work-days') }}"
            data-add-break-url="{{ url('work-time/work-days') }}" data-update-break-url="{{ url('work-time/breaks') }}"
            data-delete-break-url="{{ url('work-time/breaks') }}">
            <div class="text-xs text-white/70">Рабочий день</div>
            <div id="wt-status" class="mt-1 text-sm font-semibold text-emerald-300">Не начат</div>

            <div class="mt-2 text-xs text-white/80">Работа: <span id="wt-work-time" class="font-mono">00:00:00</span>
            </div>
            <div class="text-xs text-white/80">Пауза: <span id="wt-break-time" class="font-mono">00:00:00</span></div>

            <div class="mt-3 flex flex-wrap gap-2">
                <button id="wt-btn-start" type="button"
                    class="rounded bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-500">
                    Начать рабочий день
                </button>

                <button id="wt-btn-pause" type="button"
                    class="hidden rounded bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-400">
                    Поставить на паузу
                </button>

                <button id="wt-btn-resume" type="button"
                    class="hidden rounded bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">
                    Снять паузу
                </button>

                <button id="wt-btn-end" type="button"
                    class="hidden rounded bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-500">
                    Закончить рабочий день
                </button>

                <button id="wt-btn-edit" type="button"
                    class="hidden rounded border border-white/20 px-3 py-1.5 text-xs font-semibold text-white hover:bg-white/10">
                    Редактировать
                </button>
            </div>
        </div>
        {{-- Dashboard --}}
        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
            Dashboard
        </x-nav-link>



        @if (auth()->user()->isAdmin() || auth()->user()->isProjectManager() || auth()->user()->isMarketer())
            {{-- Пользователи: admin + project manager + marketer --}}
            <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                Сотрудники
            </x-nav-link>
        @endif


        {{-- Специальности: только admin --}}
        @if (auth()->user()->isAdmin())
            <x-nav-link :href="route('specialties.index')" :active="request()->routeIs('specialties.*')">
                Специальности
            </x-nav-link>
        @endif

        {{-- Табель: только admin --}}
        @if (auth()->user()->isAdmin())
            @php $activeAttendance = request()->routeIs('attendance.*'); @endphp
            <x-buttons-dropdawn :active="$activeAttendance" title="Табель">
                <x-dropdown-link :href="route('attendance.index')" :active="request()->routeIs('attendance.index')">
                    Табель посещаемости
                </x-dropdown-link>

                <x-dropdown-link :href="route('attendance.approvals')" :active="request()->routeIs('attendance.approvals')">
                    Табели на согласовании
                </x-dropdown-link>

                {{-- <x-dropdown-link :href="route('attendance.payable')" :active="request()->routeIs('attendance.payable')">
                    Табели на Аванс
                </x-dropdown-link> --}}

                <x-dropdown-link :href="route('attendance.advance')" :active="request()->routeIs('attendance.advance')">
                    Табели на оплату Зарплаты
                </x-dropdown-link>

                <x-dropdown-link :href="route('attendance.paid')" :active="request()->routeIs('attendance.paid')">
                    Табели оплаченные
                </x-dropdown-link>

                <x-dropdown-link :href="route('attendance.rejected')" :active="request()->routeIs('attendance.rejected')">
                    Табели отклоненные
                </x-dropdown-link>
            </x-buttons-dropdawn>
        @elseif (auth()->user()->isProjectManager() || auth()->user()->isMarketer())
            <x-nav-link :href="route('attendance.index')" :active="request()->routeIs('attendance.index')">
                Табель посещаемости
            </x-nav-link>
            <x-nav-link :href="route('attendance.paid')" :active="request()->routeIs('attendance.paid')">
                Табели оплаченные
            </x-nav-link>
        @endif

        {{-- Финансы: только admin --}}
        @if (auth()->user()->isAdmin())
            @php
                $activeFinance = request()->routeIs(
                    'bank-accounts.*',
                    'invoice-statuses.*',
                    'payment-categories.*',
                    'payment-methods.*',
                    'expense-categories.*',
                    'monthly-expenses.*',
                );
            @endphp
            <x-buttons-dropdawn :active="$activeFinance" title="Финансы">
                <x-dropdown-link :href="route('bank-accounts.index')" :active="request()->routeIs('bank-accounts.*')">
                    Банковские счета
                </x-dropdown-link>

                <x-dropdown-link :href="route('invoice-statuses.index')" :active="request()->routeIs('invoice-statuses.*')">
                    Статусы счетов
                </x-dropdown-link>

                <x-dropdown-link :href="route('payment-categories.index')" :active="request()->routeIs('payment-categories.*')">
                    Категории поступлений
                </x-dropdown-link>

                <x-dropdown-link :href="route('expense-categories.index')" :active="request()->routeIs('expense-categories.*')">
                    Статьи расходов
                </x-dropdown-link>
                <x-dropdown-link :href="route('monthly-expenses.index')" :active="request()->routeIs('monthly-expenses.*')">
                    Ежемесячные расходы
                </x-dropdown-link>
                <x-dropdown-link :href="route('payment-methods.index')" :active="request()->routeIs('payment-methods.*')">
                    Способы оплаты
                </x-dropdown-link>
            </x-buttons-dropdawn>
        @endif

        {{-- Проекты --}}
        @php
            $activeProjects = request()->routeIs(
                'projects.*',
                'calendar.*',
                'stages.*',

                'importances.*',
            );
        @endphp
        @if (auth()->user()->isAdmin())
            {{-- ADMIN: выпадающий список --}}
            <x-buttons-dropdawn :active="$activeProjects" title="Проекты">
                <x-dropdown-link :href="route('projects.index')" :active="request()->routeIs('projects.index')">
                    Проекты
                </x-dropdown-link>

                <x-dropdown-link :href="route('projects.arrears')" :active="request()->routeIs('projects.arrears')">
                    Закрытые проекты
                </x-dropdown-link>

                <x-dropdown-link :href="route('projects.debtors')" :active="request()->routeIs('projects.debtors')">
                    Проекты должники
                </x-dropdown-link>

                <x-dropdown-link :href="route('lawyer.projects.index')" :active="request()->routeIs('lawyer.projects.index')">
                    Проекты отправленные юристу
                </x-dropdown-link>

                <x-dropdown-link :href="route('calendar.all-projects')" :active="request()->routeIs('calendar.all-projects')">
                    Календарь выплат
                </x-dropdown-link>

                <x-dropdown-link :href="route('stages.index')" :active="request()->routeIs('stages.*')">
                    Этапы
                </x-dropdown-link>

                <x-dropdown-link :href="route('importances.index')" :active="request()->routeIs('importances.*')">
                    Уровень важности
                </x-dropdown-link>
            </x-buttons-dropdawn>
            <x-nav-link :href="route('account-credentials.itSumnikoff')" :active="request()->routeIs('account-credentials.itSumnikoff')">
                Доступы Наши
            </x-nav-link>

            <x-nav-link :href="route('domains.index')" :active="request()->routeIs('domains.*')">
                Домены
            </x-nav-link>
        @elseif (auth()->user()->isProjectManager() || auth()->user()->isMarketer())
            {{-- НЕ admin: обычная кнопка --}}
            @if (auth()->user()->isProjectManager())
                <x-nav-link :href="route('account-credentials.itSumnikoff')" :active="request()->routeIs('account-credentials.itSumnikoff')">
                    Доступы Наши
                </x-nav-link>
            @endif

            <x-nav-link :href="route('domains.index')" :active="request()->routeIs('domains.*')">
                Домены
            </x-nav-link>
            <x-nav-link :href="route('projects.index')" :active="request()->routeIs('projects.*')">
                Проекты
            </x-nav-link>
        @else
            <x-nav-link :href="route('lawyer.projects.index')" :active="request()->routeIs('lawyer.projects.index')">
                Проекты отправленные юристу
            </x-nav-link>
        @endif



        {{-- Организации: только admin --}}
        @if (auth()->user()->isAdmin())
            @php
                $activeOrgs = request()->routeIs(
                    'organizations.*',
                    'contacts.*',
                    'campaign-sources.*',
                    'campaign-statuses.*',
                );
            @endphp
            <x-buttons-dropdawn :active="$activeOrgs" title="Организации">
                <x-dropdown-link :href="route('organizations.index')" :active="request()->routeIs('organizations.*')">
                    Организации
                </x-dropdown-link>

                <x-dropdown-link :href="route('contacts.index')" :active="request()->routeIs('contacts.*')">
                    Контакты организаций
                </x-dropdown-link>

                <x-dropdown-link :href="route('campaign-sources.index')" :active="request()->routeIs('campaign-sources.*')">
                    Источники организаций
                </x-dropdown-link>

                <x-dropdown-link :href="route('campaign-statuses.index')" :active="request()->routeIs('campaign-statuses.*')">
                    Статусы организаций
                </x-dropdown-link>
            </x-buttons-dropdawn>
        @else
            <x-nav-link :href="route('organizations.index')" :active="request()->routeIs('organizations.*')">
                Организации
            </x-nav-link>
        @endif

        @if (auth()->user()->isAdmin() || auth()->user()->isProjectManager() || auth()->user()->isMarketer())
            {{-- Операции: только admin --}}
            <x-nav-link :href="route('operation.index')" :active="request()->routeIs('operation.index')">
                Операции
            </x-nav-link>
        @endif
    </nav>

    <div class="border-t border-white/20 mx-4 my-3"></div>

    <div id="wt-end-modal" class="fixed inset-0 z-[1000] hidden">
        <div class="absolute inset-0 bg-black/60" data-wt-close-end="1"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="w-full max-w-lg rounded-xl bg-white p-5 text-gray-900 shadow-xl">
                <div class="mb-4 text-lg font-semibold">Завершение рабочего дня</div>

                <label class="mb-1 block text-sm font-medium text-gray-700">Время окончания</label>
                <input id="wt-end-at" type="datetime-local" class="w-full rounded border-gray-300 text-sm" />

                <label class="mb-1 mt-4 block text-sm font-medium text-gray-700">Что сделано за день</label>
                <textarea id="wt-end-report" rows="4" class="w-full rounded border-gray-300 text-sm"
                    placeholder="Кратко опишите результат за день"></textarea>

                <div class="mt-4 flex justify-end gap-2">
                    <button type="button" data-wt-close-end="1"
                        class="rounded border border-gray-300 px-3 py-1.5 text-sm hover:bg-gray-50">Выйти</button>
                    <button id="wt-save-report" type="button"
                        class="rounded bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-500">Сохранить
                        инфу</button>
                    <button id="wt-end-save" type="button"
                        class="rounded bg-rose-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-rose-500">Завершить</button>
                </div>
            </div>
        </div>
    </div>

    <div id="wt-confirm-modal" class="fixed inset-0 z-[1000] hidden">
        <div class="absolute inset-0 bg-black/60" data-wt-close-confirm="1"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="w-full max-w-md rounded-xl bg-white p-5 text-gray-900 shadow-xl">
                <div class="mb-4 text-lg font-semibold">Подтверждение</div>
                <div class="mb-4 text-sm text-gray-700">Вы уверены что хотите завершить рабочий день?</div>
                <div class="flex justify-end gap-2">
                    <button type="button" data-wt-close-confirm="1"
                        class="rounded border border-gray-300 px-3 py-1.5 text-sm hover:bg-gray-50">Нет</button>
                    <button id="wt-confirm-yes" type="button"
                        class="rounded bg-rose-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-rose-500">Да</button>
                </div>
            </div>
        </div>
    </div>

    <div id="wt-edit-modal" class="fixed inset-0 z-[1000] hidden">
        <div class="absolute inset-0 bg-black/60" data-wt-close-edit="1"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="w-full max-w-4xl rounded-xl bg-white p-5 text-gray-900 shadow-xl max-h-[90vh] overflow-y-auto">
                <div class="mb-4 text-lg font-semibold">Редактирование времени</div>

                <div class="rounded-lg border p-4">
                    <div class="mb-2 text-sm font-semibold">Редактировать окончание дня</div>
                    <div class="grid gap-2 md:grid-cols-3">
                        <input id="wt-edit-day-ended-at" type="datetime-local"
                            class="rounded border-gray-300 text-sm" />
                        <input id="wt-edit-day-comment" type="text"
                            class="rounded border-gray-300 text-sm md:col-span-2"
                            placeholder="Комментарий, почему меняете время" />
                    </div>
                    <div class="mt-2 flex justify-end">
                        <button id="wt-edit-day-save" type="button"
                            class="rounded bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-500">Сохранить
                            окончание</button>
                    </div>
                </div>

                <div class="mt-4 rounded-lg border p-4">
                    <div class="mb-2 text-sm font-semibold">Паузы</div>
                    <div id="wt-breaks-list" class="space-y-3"></div>

                    <div class="mt-4 rounded border border-dashed p-3">
                        <div class="mb-2 text-sm font-medium">Добавить паузу</div>
                        <div class="grid gap-2 md:grid-cols-3">
                            <input id="wt-add-break-start" type="datetime-local"
                                class="rounded border-gray-300 text-sm" />
                            <input id="wt-add-break-end" type="datetime-local"
                                class="rounded border-gray-300 text-sm" />
                            <input id="wt-add-break-comment" type="text" class="rounded border-gray-300 text-sm"
                                placeholder="Комментарий" />
                        </div>
                        <div class="mt-2 flex justify-end">
                            <button id="wt-add-break-save" type="button"
                                class="rounded bg-emerald-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-emerald-500">Добавить
                                паузу</button>
                        </div>
                    </div>
                </div>

                <div class="mt-4 rounded-lg border p-4">
                    <div class="mb-2 text-sm font-semibold">История изменений</div>
                    <div id="wt-edits-list" class="space-y-2 text-sm text-gray-700"></div>
                </div>

                <div class="mt-4 flex justify-end">
                    <button type="button" data-wt-close-edit="1"
                        class="rounded border border-gray-300 px-3 py-1.5 text-sm hover:bg-gray-50">Закрыть</button>
                </div>
            </div>
        </div>
    </div>

    <!-- work-time JS moved to `resources/js/work-time.js` and is loaded via Vite (imported from `resources/js/app.js`) -->

    <div class="px-4 py-2">
        <div class="text-xs text-white/70 mb-2">Полезные ссылки</div>
        <div class="flex flex-col space-y-1">
            <a href="https://disk.360.yandex.ru/d/qruYSh-6HXnx7g" target="_blank" rel="noopener noreferrer"
                class="flex items-center gap-2 text-sm text-white/90 hover:bg-white/10 rounded px-2 py-1">
                Документы
            </a>
            <a href="https://itsgpro.ru/" target="_blank" rel="noopener noreferrer"
                class="flex items-center gap-2 text-sm text-white/90 hover:bg-white/10 rounded px-2 py-1">
                IT Sumnikoff
            </a>

            <a href="https://курсы-веб.рф/" target="_blank" rel="noopener noreferrer"
                class="flex items-center gap-2 text-sm text-white/90 hover:bg-white/10 rounded px-2 py-1">
                Курсы Веб
            </a>

            <a href="https://disk.360.yandex.ru/client/disk" target="_blank" rel="noopener noreferrer"
                class="flex items-center gap-2 text-sm text-white/90 hover:bg-white/10 rounded px-2 py-1">
                Диск 360
            </a>
        </div>
    </div>


    <!-- Account / actions at bottom -->
    <div class="px-4 py-3 border-t border-white/10">
        <div class="font-medium truncate">{{ Auth::user()->name }}</div>
        <div class="mt-3 flex">
            <button title="Уведомления" class="w-1/3 flex items-center justify-center p-2 hover:bg-white/10 rounded">
                <!-- Bell Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118.6 14.6V11a6 6 0 10-12 0v3.6c0 .538-.214 1.055-.595 1.405L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            </button>

            <a href="{{ route('profile.edit') }}" title="Профиль"
                class="w-1/3 flex items-center justify-center p-2 hover:bg-white/10 rounded">
                <!-- User Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                    class="bi bi-person" viewBox="0 0 16 16">
                    <path
                        d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4Zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10Z" />
                </svg>
            </a>

            <form method="POST" action="{{ route('logout') }}" class="w-1/3">
                @csrf
                <button type="submit" title="Выйти"
                    class="w-full flex items-center justify-center p-2 hover:bg-white/10 rounded">
                    <!-- Logout Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>
