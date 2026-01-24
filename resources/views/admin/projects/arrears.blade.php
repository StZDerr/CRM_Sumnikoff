@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-semibold">
                Проекты, закрытые ранее {{ $today->format('d.m.Y') }}
            </h1>
            <a href="{{ route('projects.index') }}"
                class="inline-flex items-center px-4 py-2 rounded-md border border-indigo-500 text-indigo-600 text-sm font-medium hover:bg-indigo-50 transition">
                ← К списку проектов
            </a>
        </div>

        <div class="bg-white rounded shadow overflow-hidden">
            @php
                $paymentTypes = [
                    'paid' => 'Коммерческий',
                    'barter' => 'Бартер',
                    'own' => 'Свой',
                ];
            @endphp

            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3 text-left">Проект</th>
                        <th class="p-3 text-left">Организация</th>
                        <th class="p-3 text-left">Маркетолог</th>
                        <th class="p-3 text-left">Дата закрытия</th>
                        <th class="p-3 text-left">Тип</th>
                        <th class="p-3 text-left">Баланс</th>
                        <th class="p-3 text-left">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($projects as $project)
                        <tr class="border-t">
                            <td class="p-3">
                                <a href="{{ route('projects.show', $project) }}" class="text-indigo-600 hover:underline">
                                    {{ $project->title }}
                                </a>
                            </td>
                            <td class="p-3">
                                {{ $project->organization?->name_short ?? ($project->organization?->name_full ?? '-') }}
                            </td>
                            <td class="p-3">
                                {{ $project->marketer?->name ?? '-' }}
                            </td>
                            <td class="p-3">
                                {{ $project->closed_at?->format('d.m.Y') ?? '-' }}
                            </td>
                            <td class="p-3">
                                {{ $paymentTypes[$project->payment_type] ?? ($project->payment_type ?? '—') }}
                            </td>
                            <td class="p-3">
                                @php
                                    $invoicesTotal = $project->invoices_total;
                                    $paymentsTotal = $project->payments_total;
                                    $bal = $paymentsTotal - $invoicesTotal;
                                    $hasInvoices = $invoicesTotal > 0;
                                @endphp

                                @if ($hasInvoices && $bal < 0)
                                    <button type="button"
                                        class="inline-flex items-center px-2 py-1 bg-red-600 text-white rounded text-sm"
                                        data-tippy
                                        data-tippy-content="Счета: {{ number_format($invoicesTotal, 0, '.', ' ') }} ₽<br>Оплачено: {{ number_format($paymentsTotal, 0, '.', ' ') }} ₽<br>Долг: {{ number_format(abs($bal), 0, '.', ' ') }} ₽">
                                        Долг
                                    </button>
                                @elseif ($hasInvoices && $bal > 0)
                                    <button type="button"
                                        class="inline-flex items-center px-2 py-1 bg-green-600 text-white rounded text-sm"
                                        data-tippy
                                        data-tippy-content="Счета: {{ number_format($invoicesTotal, 0, '.', ' ') }} ₽<br>Оплачено: {{ number_format($paymentsTotal, 0, '.', ' ') }} ₽<br>Переплата: {{ number_format($bal, 0, '.', ' ') }} ₽">
                                        Переплата
                                    </button>
                                @elseif ($hasInvoices && round($bal, 2) == 0)
                                    <button type="button"
                                        class="inline-flex items-center px-2 py-1 bg-green-600 text-white rounded text-sm"
                                        data-tippy
                                        data-tippy-content="Счета: {{ number_format($invoicesTotal, 0, '.', ' ') }} ₽<br>Оплачено: {{ number_format($paymentsTotal, 0, '.', ' ') }} ₽">
                                        Оплачено
                                    </button>
                                @elseif (!$hasInvoices && $paymentsTotal > 0)
                                    <button type="button"
                                        class="inline-flex items-center px-2 py-1 bg-blue-600 text-white rounded text-sm"
                                        data-tippy
                                        data-tippy-content="Нет счетов<br>Оплачено: {{ number_format($paymentsTotal, 0, '.', ' ') }} ₽">
                                        Без счёта
                                    </button>
                                @else
                                    <span class="text-sm text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="p-3">
                                <a href="{{ route('projects.show', $project) }}"
                                    class="text-indigo-600 hover:text-indigo-800 text-sm">Просмотр</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-4 text-gray-500">Нет проектов, закрытых ранее сегодняшнего дня.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $projects->links() }}
        </div>
    </div>
@endsection
