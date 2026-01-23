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
                            <td class="p-3"><a href="{{ route('projects.show', $project) }}"
                                    class="text-indigo-600 hover:text-indigo-800 text-sm">Просмотр</a></td>
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
@endsection
