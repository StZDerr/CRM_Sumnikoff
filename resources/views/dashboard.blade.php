<x-app-layout>
    {{-- ================= HEADER ================= --}}
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800">
                📈 Динамика доходов
            </h2>

            <form method="GET" class="flex items-center gap-2">
                <input type="month" name="month" value="{{ $monthParam ?? now()->format('Y-m') }}"
                    class="border rounded-lg px-3 py-1.5 text-sm
                           focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />

                <button
                    class="px-4 py-1.5 bg-indigo-600 text-white text-sm rounded-lg
                           hover:bg-indigo-700 transition">
                    Показать
                </button>

                @if (request('period') === 'all')
                    <a href="{{ url()->current() }}" class="text-sm text-gray-500 hover:text-gray-700">Текущий месяц</a>
                @else
                    <a href="{{ url()->current() }}?period=all" class="text-sm text-gray-500 hover:text-gray-700">За всё
                        время</a>
                @endif

                <a href="{{ route('dashboard') }}" class="text-sm text-gray-500 hover:text-gray-700">Сброс</a>
            </form>
        </div>
    </x-slot>

    {{-- ================= CONTENT ================= --}}
    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- ===== KPI CARDS ===== --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl shadow p-4">
                    <div class="text-xs text-gray-500">Выбранный месяц</div>
                    <div class="text-lg font-semibold mt-1">
                        {{ $monthLabel }}
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow p-4">
                    <div class="text-xs text-gray-500">Доход за месяц</div>
                    <div class="text-2xl font-bold mt-1 text-indigo-600">
                        {{ number_format($monthTotal, 2, '.', ' ') }} ₽
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow p-4">
                    <div class="text-xs text-gray-500">Расход за месяц</div>
                    <div class="text-2xl font-bold mt-1 text-red-600">
                        -{{ number_format($monthTotalExpense ?? 0, 2, '.', ' ') }} ₽
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow p-4">
                    <div class="text-xs text-gray-500">Баланс (Доход − Расход)</div>
                    @php $net = $monthTotal - ($monthTotalExpense ?? 0); @endphp
                    <div class="text-2xl font-bold mt-1 {{ $net >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($net, 2, '.', ' ') }} ₽
                    </div>
                </div>
            </div>

            {{-- ===== CHART ===== --}}
            <div class="bg-white rounded-xl shadow p-6">
                <div class="mb-4">
                    <div class="text-sm font-medium text-gray-800">
                        Поступления по дням
                    </div>
                    <div class="text-xs text-gray-500">
                        Финансовая динамика за выбранный месяц
                    </div>
                </div>

                <div class="h-100">
                    <canvas id="incomeChart"></canvas>
                </div>
            </div>

            {{-- ===== TOP PROJECTS ===== --}}
            <div class="bg-white rounded-xl shadow p-6 mt-6">
                <div class="mb-4">
                    <div class="text-sm font-medium text-gray-800">Самые доходные проекты (топ 5)</div>
                    <div class="text-xs text-gray-500">
                        {{ request('period') === 'all' ? 'Сумма поступлений за весь период' : 'Сумма поступлений за выбранный месяц' }}
                    </div>
                </div>

                <div class="h-72">
                    <canvas id="topProjectsChart"></canvas>
                </div>
            </div>

            {{-- ===== ACTIVE PROJECTS ===== --}}
            <div class="bg-white rounded-xl shadow p-6 mt-6">
                <div class="mb-4">
                    <div class="text-sm font-medium text-gray-800">Активные проекты</div>
                    <div class="text-xs text-gray-500">Количество проектов в работе (закрытые не считаются)</div>
                </div>

                <div class="h-60">
                    <canvas id="activeProjectsChart"></canvas>
                </div>
            </div>

            {{-- ===== DEBTORS ===== --}}
            <div class="bg-white rounded-xl shadow p-6 mt-6">
                <div class="mb-4">
                    <div class="text-sm font-medium text-gray-800">Должники</div>
                    <div class="text-xs text-gray-500">Топ-10 должников (balance &lt; 0) — показываем абсолютные
                        значения задолженности</div>
                </div>

                <div class="h-60">
                    <canvas id="debtorsChart"></canvas>
                </div>
            </div>

        </div>
    </div>

    {{-- ================= SCRIPTS ================= --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        (function() {

            const labels = {!! json_encode($labels) !!};
            const income = {!! json_encode($incomeData) !!};
            const expense = {!! json_encode($expenseData) !!};
            const yMax = {{ (int) $yMax }};
            const yMin = {{ (int) $yMin }};
            const yStep = {{ (int) $step }};

            const canvas = document.getElementById('incomeChart');
            if (!canvas) {
                console.error('Chart canvas #incomeChart not found');
                return;
            }
            const ctx = canvas.getContext('2d');
            if (!ctx) {
                console.error('Could not get 2D context for #incomeChart');
                return;
            }

            // --- Top projects data ---
            const topLabels = {!! json_encode($topProjectsLabels ?? []) !!};
            const topData = {!! json_encode($topProjectsData ?? []) !!};
            const topMax = {{ (int) ($topMaxChart ?? 0) }};
            const topStep = {{ (int) ($topStep ?? 1000) }};

            // If all data arrays are empty, show console and do nothing
            const hasData = (Array.isArray(income) && income.some(v => Number(v) !== 0)) || (Array.isArray(expense) &&
                expense.some(v => Number(v) !== 0));
            if (!hasData) {
                // draw empty axes with zero line
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: []
                    },
                    options: {
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
                console.info('No income/expense data for selected month');
                return;
            }

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                            label: 'Доходы',
                            data: income,
                            borderColor: '#10B981',
                            backgroundColor: 'rgba(16,185,129,0.12)',
                            tension: 0.3,
                            fill: true,
                        },
                        {
                            label: 'Расходы',
                            data: expense,
                            borderColor: '#EF4444',
                            backgroundColor: 'rgba(239,68,68,0.08)',
                            tension: 0.3,
                            fill: true,
                        },

                    ]
                },
                options: {
                    scales: {
                        y: {
                            min: yMin,
                            max: yMax,
                            ticks: {
                                stepSize: yStep,
                                callback: function(val) {
                                    if (val === 0) return '0k';
                                    return (val / 1000) + 'k';
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    const v = ctx.parsed.y;
                                    return ctx.dataset.label + ': ' + Number(v).toLocaleString('ru-RU', {
                                        minimumFractionDigits: 2
                                    }) + ' ₽';
                                }
                            }
                        }
                    }
                }
            });

            // --- Top projects chart (horizontal bars) ---
            const topCanvas = document.getElementById('topProjectsChart');
            if (topCanvas) {
                const topCtx = topCanvas.getContext('2d');
                try {
                    new Chart(topCtx, {
                        type: 'bar',
                        data: {
                            labels: topLabels,
                            datasets: [{
                                label: 'Доходы',
                                data: topData,
                                backgroundColor: '#3B82F6'
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: {
                                    min: 0,
                                    max: topMax,
                                    ticks: {
                                        stepSize: topStep,
                                        callback: function(v) {
                                            if (v === 0) return '0';
                                            return (v / 1000) + 'k';
                                        }
                                    }
                                },
                                y: {
                                    ticks: {
                                        autoSkip: false
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(ctx) {
                                            const v = ctx.parsed.x;
                                            return Number(v).toLocaleString('ru-RU', {
                                                minimumFractionDigits: 2
                                            }) + ' ₽';
                                        }
                                    }
                                }
                            }
                        }
                    });
                } catch (e) {
                    console.error('Top projects chart error', e);
                }
            }

            // --- Active projects chart (line) ---
            const activeCanvas = document.getElementById('activeProjectsChart');
            if (activeCanvas) {
                const activeCtx = activeCanvas.getContext('2d');
                try {
                    const activeData = {!! json_encode($activeData ?? []) !!};
                    const activeStep = {{ (int) ($activeStep ?? 1) }};

                    new Chart(activeCtx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Активные проекты',
                                data: activeData,
                                borderColor: '#F59E0B',
                                backgroundColor: 'rgba(245,158,11,0.08)',
                                tension: 0.2,
                                fill: true,
                                pointRadius: 3
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: activeStep,
                                        precision: 0
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(ctx) {
                                            return ctx.dataset.label + ': ' + Number(ctx.parsed.y)
                                                .toLocaleString('ru-RU') + ' шт';
                                        }
                                    }
                                }
                            }
                        }
                    });
                } catch (e) {
                    console.error('Active projects chart error', e);
                }
            }

            // --- Debtors (vertical bar) ---
            const debtorsCanvas = document.getElementById('debtorsChart');
            if (debtorsCanvas) {
                const dCtx = debtorsCanvas.getContext('2d');
                try {
                    const dLabels = {!! json_encode($debtorLabels ?? []) !!};
                    const dData = {!! json_encode($debtorData ?? []) !!};
                    const dMax = {{ (int) ($debtorMaxChart ?? 0) }};
                    const dStep = {{ (int) ($debtorStep ?? 10000) }};

                    const hasDebtors = Array.isArray(dData) && dData.some(v => Number(v) > 0);
                    if (!hasDebtors) {
                        // draw blank axes
                        new Chart(dCtx, {
                            type: 'bar',
                            data: {
                                labels: dLabels,
                                datasets: []
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false
                            }
                        });
                    } else {
                        new Chart(dCtx, {
                            type: 'bar',
                            data: {
                                labels: dLabels,
                                datasets: [{
                                    label: 'Задолженность',
                                    data: dData,
                                    backgroundColor: '#EF4444'
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        min: 0,
                                        max: dMax,
                                        ticks: {
                                            stepSize: dStep,
                                            callback: function(v) {
                                                if (v === 0) return '0';
                                                return (v / 1000) + 'k';
                                            }
                                        }
                                    },
                                    x: {
                                        ticks: {
                                            autoSkip: false
                                        }
                                    }
                                },
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(ctx) {
                                                // Show as negative (debtor)
                                                return '-' + Number(ctx.parsed.y).toLocaleString('ru-RU', {
                                                    minimumFractionDigits: 2
                                                }) + ' ₽';
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }
                } catch (e) {
                    console.error('Debtors chart error', e);
                }
            }

        })();
    </script>
</x-app-layout>
