@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto py-6">
        <h1 class="text-2xl font-semibold mb-4">Проекты по месяцам</h1>

        <div class="overflow-x-auto">
            @if (empty($project->contract_date) || empty($months))
                <div class="bg-yellow-50 border border-yellow-200 rounded p-4 text-sm text-gray-700">
                    У проекта не задана дата заключения договора — нет данных для календаря.
                </div>
            @else
                <table class="min-w-full border-collapse border border-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border border-gray-200 p-2 text-left">Проект</th>
                            @foreach ($months as $m)
                                <th class="border border-gray-200 p-2 text-center">{{ $m['label'] }}</th>
                            @endforeach
                            <th class="border border-gray-200 p-2 text-center">К оплате</th>
                            <th class="border border-gray-200 p-2 text-center">Оплачено</th>
                            <th class="border border-gray-200 p-2 text-center">Разница</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr class="hover:bg-gray-50">
                            <td class="border border-gray-200 p-2 font-medium">{{ $project->title }}</td>

                            @foreach ($months as $m)
                                @php
                                    $key = $m['ym'];
                                    $paid = (float) ($paymentsByMonth[$key] ?? 0);
                                    $expected = (float) ($project->contract_amount ?? 0);
                                    $diff = $paid - $expected;
                                @endphp

                                <td class="border border-gray-200 p-2 text-center" data-tippy
                                    data-tippy-content="Оплачено: {{ number_format($paid, 2, '.', ' ') }} ₽<br>Ожидалось: {{ number_format($expected, 2, '.', ' ') }} ₽">
                                    @if ($expected <= 0)
                                        —
                                    @else
                                        @if ($diff > 0)
                                            <span
                                                class="text-green-600 font-semibold">+{{ number_format($diff, 2, '.', ' ') }}
                                                ₽</span>
                                        @elseif ($diff < 0)
                                            <span
                                                class="text-red-600 font-semibold">-{{ number_format(abs($diff), 2, '.', ' ') }}
                                                ₽</span>
                                        @else
                                            <span class="text-gray-900 font-semibold">0.00 ₽</span>
                                        @endif
                                    @endif
                                </td>
                            @endforeach

                            {{-- Используем данные из БД для итогов --}}
                            <td class="border border-gray-200 p-2 text-center font-medium">
                                @if (!is_null($project->debt))
                                    {{ number_format($project->debt, 2, '.', ' ') }} ₽
                                    @if ($project->debt_calculated_at)
                                        <div class="text-xs text-gray-400">as of
                                            {{ \Illuminate\Support\Carbon::make($project->debt_calculated_at)->format('Y-m-d H:i') }}
                                        </div>
                                    @endif
                                @else
                                    —
                                @endif
                            </td>

                            <td class="border border-gray-200 p-2 text-center font-medium">
                                @if (!is_null($project->received_total))
                                    {{ number_format($project->received_total, 2, '.', ' ') }} ₽
                                    @if ($project->received_calculated_at)
                                        <div class="text-xs text-gray-400">as of
                                            {{ \Illuminate\Support\Carbon::make($project->received_calculated_at)->format('Y-m-d H:i') }}
                                        </div>
                                    @endif
                                @else
                                    —
                                @endif
                            </td>

                            @php
                                $bal =
                                    $project->balance ??
                                    (isset($project->debt, $project->received_total)
                                        ? $project->debt - $project->received_total
                                        : null);
                                $diffClass =
                                    $bal < 0 ? 'text-red-600' : ($bal > 0 ? 'text-green-600' : 'text-gray-900');
                            @endphp
                            <td class="border border-gray-200 p-2 text-center font-semibold {{ $diffClass }}">
                                @if (!is_null($bal))
                                    {{ number_format($bal, 2, '.', ' ') }} ₽
                                    @if ($project->balance_calculated_at)
                                        <div class="text-xs text-gray-400">as of
                                            {{ \Illuminate\Support\Carbon::make($project->balance_calculated_at)->format('Y-m-d H:i') }}
                                        </div>
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
