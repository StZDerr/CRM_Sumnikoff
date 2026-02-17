<div class="max-w-7xl mx-auto py-10 space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-indigo-600 to-indigo-500 text-white rounded-lg shadow p-8">
        <h1 class="text-3xl font-semibold">Добро пожаловать в CRM, {{ auth()->user()->name }}</h1>
        <p class="mt-2 text-indigo-100">Управляйте проектами, расходами и табелями — все важные инструменты под рукой.
        </p>
    </div>

    @include('partials.link-cards')

    <!-- Quick Access Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- "Мои проекты" можно скрыть через переменную $hide_projects (dev страница) --}}
        @unless ($hide_projects ?? false)
            <!-- Projects -->
            <div class="bg-white rounded-lg border shadow-sm hover:shadow-lg transition p-6 flex flex-col justify-between">
                <div class="flex items-start gap-4">
                    <div class="p-3 rounded bg-indigo-50 text-indigo-600">
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
                        <div class="font-semibold">Мои проекты
                            ({{ $projectsTotalCount ?? ($activeProjectsCount ?? auth()->user()->projects->count()) }})</div>
                        <div class="text-sm text-gray-500 mt-1">Список проектов, к которым вы причастны.</div>
                    </div>
                </div>
                <a href="{{ route('projects.index') }}"
                    class="mt-4 inline-flex items-center justify-center px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition">Перейти</a>
            </div>
        @endunless

        <!-- Expenses (frontend/dev variant: button should open domain-hosting expense modal) -->
        <div class="bg-white rounded-lg border shadow-sm hover:shadow-lg transition p-6 flex flex-col justify-between">
            <div class="flex items-start gap-4">
                <div class="p-3 rounded bg-green-50 text-green-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 12v-2" />
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="font-semibold">Расходы</div>
                    <div class="text-sm text-gray-500 mt-1">Создание и просмотр расходов (быстрый добавитель доменов).
                    </div>
                </div>
            </div>

            {{-- На странице dev показываем кнопку "Добавить", открывающую модалку оплаты доменов/хостинга --}}
            <button type="button" id="openDomainHostingCategoriesBtn"
                class="mt-4 inline-flex items-center justify-center px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">Добавить</button>
        </div>

        <!-- Attendance -->
        <div class="bg-white rounded-lg border shadow-sm hover:shadow-lg transition p-6 flex flex-col justify-between">
            <div class="flex items-start gap-4">
                <div class="p-3 rounded bg-yellow-50 text-yellow-600">
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
            <a href="{{ route('attendance.index') }}"
                class="mt-4 inline-flex items-center justify-center px-3 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700 transition">Перейти</a>
        </div>
    </div>

    <!-- Salary Chart -->
    <div class="bg-white rounded-lg border shadow-sm p-6 mt-6">
        <h3 class="text-lg font-medium mb-4">История выплат (12 мес.)</h3>
        <div class="w-full h-48">
            <canvas id="salaryChart"></canvas>
        </div>
    </div>

    @unless ($hide_projects ?? false)
        <!-- User projects (active / already closed) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <div class="bg-white rounded-lg border shadow-sm p-6">
                <h4 class="font-semibold mb-3">Активные проекты</h4>
                <div class="text-sm text-gray-700">
                    <ul class="list-disc list-inside">
                        @forelse($activeProjects as $p)
                            <li>
                                <a href="{{ route('projects.show', $p) }}"
                                    class="text-indigo-600 hover:underline">{{ $p->title }}</a>
                                @if ($p->status)
                                    <span class="ml-2 text-xs text-gray-500">({{ $p->status_label }})</span>
                                @endif
                            </li>
                        @empty
                            <li class="text-gray-500">Нет активных проектов</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <div class="bg-white rounded-lg border shadow-sm p-6">
                <h4 class="font-semibold mb-3">Проекты на паузе</h4>
                <div class="text-sm text-gray-700">
                    <ul class="list-disc list-inside">
                        @forelse($pausedProjects as $p)
                            <li>
                                <a href="{{ route('projects.show', $p) }}"
                                    class="text-indigo-600 hover:underline">{{ $p->title }}</a>
                                <span class="ml-2 text-xs text-gray-500">({{ $p->status_label }})</span>
                            </li>
                        @empty
                            <li class="text-gray-500">Нет проектов на паузе</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    @endunless

    {{-- Вставляем модалку оплаты доменов/хостинга (повторно — она используется на других страницах) --}}
    @include('admin.expenses._domain_hosting_modal')

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                try {
                    const labels = {!! json_encode($salaryLabels ?? []) !!};
                    const data = {!! json_encode($salaryData ?? []) !!};
                    const linkModal = document.getElementById('link-card-modal');
                    const linkModalTitle = document.getElementById('link-card-modal-title');
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
                                        callback: v => v.toLocaleString('ru-RU')
                                    }
                                }
                            },
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        label: ctx => (ctx.parsed.y || 0).toLocaleString('ru-RU') + ' ₽'
                                    }
                                }
                            }
                        }
                    });
                } catch (e) {
                    console.error('Salary chart error', e);
                }

                // link cards scripts (shared)
                @include('partials.link-cards-scripts')
            });
        </script>
    @endpush
</div>
