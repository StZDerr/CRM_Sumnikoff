@extends('layouts.app')

@section('title', 'Дашборд пользователя')

@section('content')
    <div class="space-y-6">


        {{-- Заголовок --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">
                    {{ $user->name }}
                </h1>
                <p class="text-sm text-gray-500">
                    Статистика по пользователю
                    {{ \Carbon\Carbon::createFromFormat('Y-m', $monthParam ?? now()->format('Y-m'))->translatedFormat('F Y') }}
                </p>
            </div>
            <!-- Форма выбора месяца (вверху страницы) -->
            <div class="flex items-center justify-end mt-4">
                <form method="GET" class="flex items-center gap-2">
                    <label class="text-sm text-gray-500 mr-2">Месяц</label>
                    <input type="month" name="month" value="{{ $monthParam ?? now()->format('Y-m') }}"
                        class="border rounded px-2 py-1 text-sm" />
                    <button class="ml-2 px-3 py-1 bg-indigo-600 text-white rounded text-sm">Показать</button>
                    <a href="{{ route('user.dashboard', $user) }}" class="ml-2 text-sm text-gray-500">Сброс</a>
                </form>
            </div>
        </div>



        {{-- KPI карточки --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

            {{-- Всего проектов (за месяц) --}}
            <div class="rounded-xl bg-white p-5 shadow">
                <p class="text-sm text-gray-500">Проектов в выбранном месяце</p>
                <p class="mt-2 text-3xl font-bold text-gray-800">{{ $projectsCount }}</p>
                <p class="mt-1 text-xs text-gray-400">учтены по истории назначения маркетологов</p>
            </div>

            {{-- Платные проекты --}}
            <div class="rounded-xl bg-white p-5 shadow">
                <p class="text-sm text-gray-500">Платные проекты</p>
                <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $paidProjectsCount ?? 0 }}</p>
                <p class="mt-1 text-xs text-gray-400">приносят доход</p>
            </div>

            {{-- Бартерные --}}
            <div class="rounded-xl bg-white p-5 shadow">
                <p class="text-sm text-gray-500">Бартерные проекты</p>
                <p class="mt-2 text-3xl font-bold text-yellow-500">{{ $barterProjectsCount }}</p>
                <p class="mt-1 text-xs text-gray-400">без денег</p>
            </div>

            {{-- Наши --}}
            <div class="rounded-xl bg-white p-5 shadow">
                <p class="text-sm text-gray-500">Наши проекты</p>
                <p class="mt-2 text-3xl font-bold text-blue-600">{{ $ownProjectsCount ?? 0 }}</p>
                <p class="mt-1 text-xs text-gray-400">внутренние</p>
            </div>
        </div>

        {{-- Финансы --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

            {{-- Ожидаемая прибыль --}}
            <div class="rounded-xl bg-white p-5 shadow">
                <p class="text-sm text-gray-500">Ожидаемая прибыль</p>
                <p class="mt-2 text-2xl font-bold text-gray-800">
                    {{ $expectedProfit }} ₽
                </p>
                <p class="mt-1 text-xs text-gray-400">
                    активные и закрытые в этом месяце (без бартеров и своих)
                </p>
            </div>

            {{-- Получено --}}
            <div class="rounded-xl bg-white p-5 shadow">
                <p class="text-sm text-gray-500">Получено денег</p>
                <p class="mt-2 text-2xl font-bold text-emerald-600">
                    {{ number_format($moneyReceived ?? 0, 2, '.', ' ') }} ₽
                </p>
                <p class="mt-1 text-xs text-gray-400">
                    оплачено клиентами на основе поступленний
                </p>
            </div>

            {{-- Долг --}}
            <div class="rounded-xl bg-white p-5 shadow">
                <p class="text-sm text-gray-500">Долг клиентов</p>
                <p class="mt-2 text-2xl font-bold {{ $clientDebt > 0 ? 'text-red-500' : 'text-emerald-600' }}">
                    {{ number_format($clientDebt, 0, '.', ' ') }} ₽
                </p>
                <p class="mt-1 text-xs text-gray-400">
                    накопительный долг на конец месяца (платные проекты)
                </p>
            </div>
        </div>

        {{-- Блок активности --}}
        <div class="rounded-xl bg-white p-5 shadow">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">
                Активность
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                <div>
                    <p class="text-gray-500">Активных проектов</p>
                    <p class="text-xl font-bold text-gray-800">{{ $activeProjectsCount }}</p>
                    <p class="mt-1 text-xs text-gray-400">
                        проекты, активные на конец месяца
                    </p>
                </div>

                <div>
                    <p class="text-gray-500">Закрыто за месяц</p>
                    <p class="text-xl font-bold text-gray-800">{{ $closedProjectsCount }}</p>
                    <p class="mt-1 text-xs text-gray-400">
                        пользователь был назначен в момент закрытия
                    </p>
                </div>

                <div>
                    <p class="text-gray-500">Средний чек по поступлениям</p>
                    @if ($averageCheck !== null)
                        <p class="text-xl font-bold text-gray-800">{{ number_format($averageCheck, 0, '.', ' ') }} ₽</p>
                        <p class="mt-1 text-xs text-gray-400">
                            {{ $paymentsCount }}
                            {{ trans_choice('поступление|поступления|поступлений', $paymentsCount) }}, с вычитом налогов
                        </p>
                    @else
                        <p class="text-sm text-gray-500">Не было поступлений за период когда пользователь был на проекте.
                        </p>
                    @endif
                </div>
            </div>
        </div>

    </div>

@endsection
