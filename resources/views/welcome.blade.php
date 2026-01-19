@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-10">
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="p-8 bg-gradient-to-r from-indigo-600 to-indigo-500 text-white">
                <h1 class="text-3xl font-semibold">Добро пожаловать в CRM</h1>
                <p class="mt-2 text-indigo-100">Управляйте проектами, расходами и табелями — все важные инструменты под
                    рукой.</p>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="p-6 bg-white rounded-lg border">
                    <div class="flex items-start gap-4">
                        <div class="p-3 rounded bg-indigo-50 text-indigo-600">
                            <!-- Projects Icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-6a2 2 0 012-2h6" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 15V7a2 2 0 00-2-2h-6" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h7" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold">Мои проекты</div>
                            <div class="text-sm text-gray-500 mt-1">Список проектов, к которым вы причастны.</div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('projects.index') }}"
                            class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Перейти</a>
                    </div>
                </div>

                <div class="p-6 bg-white rounded-lg border">
                    <div class="flex items-start gap-4">
                        <div class="p-3 rounded bg-green-50 text-green-600">
                            <!-- Expenses Icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 12v-2" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold">Расходы</div>
                            <div class="text-sm text-gray-500 mt-1">Создание и просмотр расходов.</div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('operation.index') }}"
                            class="inline-flex items-center px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700">Перейти</a>
                    </div>
                </div>

                <div class="p-6 bg-white rounded-lg border">
                    <div class="flex items-start gap-4">
                        <div class="p-3 rounded bg-yellow-50 text-yellow-600">
                            <!-- Attendance Icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold">Табель</div>
                            <div class="text-sm text-gray-500 mt-1">Просмотр истории табелей и деталей по оплатам.</div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('attendance.index') }}"
                            class="inline-flex items-center px-3 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">Перейти</a>
                    </div>
                </div>


                <div class="col-span-1 md:col-span-3 mt-2 p-6 bg-white rounded-lg border">
                    <h3 class="text-lg font-medium">История выплат (12 мес.)</h3>
                    <div class="mt-4">
                        <div class="w-full h-48">
                            <canvas id="salaryChart" width="400" height="180"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-span-1 md:col-span-3 mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 bg-white rounded-lg border">
                        <h4 class="font-semibold">Последний оплаченный табель</h4>
                        @if (isset($lastPaid) && $lastPaid)
                            <div class="mt-3 text-sm text-gray-700">
                                <div><strong>Месяц:</strong>
                                    {{ \Carbon\Carbon::parse($lastPaid->month)->locale('ru')->isoFormat('MMMM YYYY') }}
                                </div>
                                <div class="mt-2"><strong>Итоговая ЗП:</strong>
                                    {{ number_format($lastPaid->total_salary, 0, '', ' ') }} ₽</div>
                                <div class="mt-3">
                                    <a href="{{ route('attendance.show', $lastPaid) }}"
                                        class="inline-flex items-center px-3 py-1 rounded bg-indigo-600 text-white text-sm">Открыть</a>
                                </div>

                                @if ($lastPaid->projectBonuses->count())
                                    <div class="mt-3">
                                        <div class="text-sm text-gray-500">Премии по проектам:</div>
                                        <ul class="mt-2 text-sm">
                                            @foreach ($lastPaid->projectBonuses as $pb)
                                                <li>{{ $pb->project?->title ?? 'Проект #' . $pb->project_id }}:
                                                    {{ number_format($pb->bonus_amount, 0, '', ' ') }} ₽</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                            </div>
                        @else
                            <div class="mt-3 text-sm text-gray-500">Оплаченные табели за период не найдены.</div>
                        @endif
                    </div>

                    <div class="p-4 bg-white rounded-lg border">
                        <h4 class="font-semibold">Ожидаемая ЗП за текущий месяц</h4>
                        <div class="mt-3 text-sm text-gray-700">
                            <div><strong>Месяц:</strong> {{ \Carbon\Carbon::now()->locale('ru')->isoFormat('MMMM YYYY') }}
                            </div>
                            <div class="mt-2"><strong>Базовая ЗП:</strong>
                                {{ number_format($expected['base_salary'] ?? 0, 0, '', ' ') }} ₽</div>
                            <div><strong>Обычные дни:</strong> {{ $expected['ordinary_days'] ?? 22 }}</div>
                            <div class="mt-2 text-sm text-gray-500">Премии по проектам:</div>
                            <ul class="mt-2 text-sm">
                                @forelse($expected['projectBonuses'] ?? [] as $pb)
                                    <li>{{ $pb['project']->title ?? 'Проект #' . ($pb['project']->id ?? ($pb['project_id'] ?? '?')) }}:
                                        {{ number_format($pb['bonus_amount'] ?? 0, 0, '', ' ') }} ₽</li>
                                @empty
                                    <li class="text-gray-500">Нет проектов</li>
                                @endforelse
                            </ul>

                            <div class="mt-3"><strong>Итоговая ожидаемая ЗП:</strong>
                                {{ number_format($expected['total_expected'] ?? 0, 0, '', ' ') }} ₽</div>
                        </div>
                    </div>
                </div>

                <div class="col-span-1 md:col-span-3 mt-4 text-sm text-gray-500">
                    <p>Это временная приветственная страница. В будущем здесь появятся персональные метрики и быстрые
                        действия для вашей роли.</p>
                </div>

                @push('scripts')
                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                    <script>
                        (function() {
                            try {
                                const labels = {!! json_encode($salaryLabels ?? []) !!};
                                const data = {!! json_encode($salaryData ?? []) !!};

                                const canvas = document.getElementById('salaryChart');
                                if (!canvas) return;
                                const ctx = canvas.getContext('2d');
                                new Chart(ctx, {
                                    type: 'line',
                                    data: {
                                        labels: labels,
                                        datasets: [{
                                            label: 'Выплаченная ЗП, ₽',
                                            data: data,
                                            borderColor: '#4F46E5',
                                            backgroundColor: 'rgba(79,70,229,0.08)',
                                            tension: 0.3,
                                            pointRadius: 3,
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        scales: {
                                            y: {
                                                beginAtZero: true,
                                                ticks: {
                                                    callback: function(value) {
                                                        return value.toLocaleString('ru-RU');
                                                    }
                                                }
                                            }
                                        },
                                        plugins: {
                                            tooltip: {
                                                callbacks: {
                                                    label: function(context) {
                                                        const v = context.parsed.y || 0;
                                                        return v.toLocaleString('ru-RU') + ' ₽';
                                                    }
                                                }
                                            }
                                        }
                                    }
                                });
                            } catch (e) {
                                console.error('Salary chart error', e);
                            }
                        })();
                    </script>
                @endpush

                @push('scripts')
                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                    <script>
                        (function() {
                            try {
                                const labels = {!! json_encode($salaryLabels ?? []) !!};
                                const data = {!! json_encode($salaryData ?? []) !!};

                                const canvas = document.getElementById('salaryChart');
                                if (!canvas) return;
                                const ctx = canvas.getContext('2d');
                                new Chart(ctx, {
                                    type: 'line',
                                    data: {
                                        labels: labels,
                                        datasets: [{
                                            label: 'Выплаченная ЗП, ₽',
                                            data: data,
                                            borderColor: '#4F46E5',
                                            backgroundColor: 'rgba(79,70,229,0.08)',
                                            tension: 0.3,
                                            pointRadius: 3,
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        scales: {
                                            y: {
                                                beginAtZero: true,
                                                ticks: {
                                                    callback: function(value) {
                                                        return value.toLocaleString('ru-RU');
                                                    }
                                                }
                                            }
                                        },
                                        plugins: {
                                            tooltip: {
                                                callbacks: {
                                                    label: function(context) {
                                                        const v = context.parsed.y || 0;
                                                        return v.toLocaleString('ru-RU') + ' ₽';
                                                    }
                                                }
                                            }
                                        }
                                    }
                                });
                            } catch (e) {
                                console.error('Salary chart error', e);
                            }
                        })();
                    </script>
                @endpush
            </div>
        </div>
    </div>
@endsection
