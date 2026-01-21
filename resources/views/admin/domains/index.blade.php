@extends('layouts.app')

@section('title', 'Домены REG.RU')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Домены REG.RU</h1>
                <p class="text-sm text-gray-500">Список доменов и сроков истечения</p>
            </div>
        </div>

        @if ($error)
            <div class="rounded-lg bg-red-50 border border-red-200 p-4 text-red-700 text-sm">
                {{ $error }}
            </div>
        @endif

        <div class="rounded-xl bg-white p-5 shadow">
            @if (empty($domains))
                <p class="text-sm text-gray-500">Нет данных для отображения.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 border-b">
                                <th class="py-2 pr-4">Домен</th>
                                <th class="py-2 pr-4">Истекает</th>
                                <th class="py-2 pr-4">Статус</th>
                                <th class="py-2 pr-4">ID услуги</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-800">
                            @foreach ($domains as $domain)
                                <tr class="border-b last:border-b-0">
                                    <td class="py-2 pr-4 font-medium">
                                        {{ $domain['dname'] ?? '—' }}
                                    </td>
                                    <td class="py-2 pr-4">
                                        {{ isset($domain['expiration_date']) ? \Carbon\Carbon::parse($domain['expiration_date'])->format('d.m.Y') : '—' }}
                                    </td>
                                    <td class="py-2 pr-4">
                                        {{ $domain['state'] ?? '—' }}
                                    </td>
                                    <td class="py-2 pr-4">
                                        {{ $domain['service_id'] ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
