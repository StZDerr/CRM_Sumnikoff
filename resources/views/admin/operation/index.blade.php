@extends('layouts.app')

@section('content')
    <div class="max-w-full mx-auto py-6">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-semibold">Операции</h1>

            <div class="flex gap-2">
                {{-- Доход --}}
                <a href="#" id="openPaymentOffcanvas" data-url="{{ route('payments.create') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white
                  hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Доход
                </a>

                {{-- Расход --}}
                <a href="#" id="openExpenseOffcanvas" data-url="{{ route('expenses.create') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white
                  hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                    </svg>
                    Расход
                </a>

                {{-- Выставить счёт --}}
                <a href="{{ route('invoices.create') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white
        hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6M7 4h10a2 2 0 012 2v14l-4-2-4 2-4-2-4 2V6a2 2 0 012-2z" />
                    </svg>
                    Выставить счёт
                </a>
            </div>

            <!-- Offcanvas: payment create -->
            <div id="paymentOffcanvas" class="fixed inset-0 z-50 hidden">
                <div class="fixed inset-0 bg-black bg-opacity-50" id="paymentOffcanvasBackdrop"></div>
                <div class="fixed right-0 top-0 h-full w-full sm:w-2/3 md:w-1/2 lg:w-1/3 bg-white transform translate-x-full transition-transform duration-300"
                    id="paymentOffcanvasPanel" role="dialog" aria-modal="true">
                    <div class="p-4 flex items-center justify-between border-b">
                        <h3 class="text-lg font-medium">Новый доход</h3>
                        <button id="closePaymentOffcanvas" class="text-gray-600 hover:text-gray-900">&times;</button>
                    </div>
                    <div id="paymentOffcanvasContent" class="p-4 overflow-auto h-full">
                        <div class="text-center text-gray-500">Загрузка...</div>
                    </div>
                </div>
            </div>

            <!-- Offcanvas: expense create -->
            <div id="expenseOffcanvas" class="fixed inset-0 z-50 hidden">
                <div class="fixed inset-0 bg-black bg-opacity-50" id="expenseOffcanvasBackdrop"></div>
                <div class="fixed right-0 top-0 h-full w-full sm:w-2/3 md:w-1/2 lg:w-1/3 bg-white transform translate-x-full transition-transform duration-300"
                    id="expenseOffcanvasPanel" role="dialog" aria-modal="true">
                    <div class="p-4 flex items-center justify-between border-b">
                        <h3 class="text-lg font-medium">Новый расход</h3>
                        <button id="closeExpenseOffcanvas" class="text-gray-600 hover:text-gray-900">&times;</button>
                    </div>
                    <div id="expenseOffcanvasContent" class="p-4 overflow-auto h-full">
                        <div class="text-center text-gray-500">Загрузка...</div>
                    </div>
                </div>
            </div>
        </div>


        <div class="bg-white shadow rounded p-4">
            <table class="w-full text-sm">
                <thead class="text-left text-xs text-gray-500">
                    <tr>
                        <th>Дата</th>
                        <th>Тип</th>
                        <th>Проект</th>
                        <th>Описание</th>
                        <th class="text-right">Сумма</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach ($operations as $op)
                        @php $m = $op['model']; @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="py-2">{{ optional($op['date'])->format('Y-m-d H:i') ?? '-' }}</td>
                            <td>{{ $op['type'] === 'payment' ? 'Поступление' : 'Расход' }}</td>
                            <td>{{ $m->project?->title ?? '-' }}</td>
                            <td class="max-w-xs truncate">
                                {{ $op['type'] === 'payment' ? $m->note ?? '' : $m->description ?? '' }}</td>
                            <td class="text-right {{ $op['type'] === 'payment' ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format($op['amount'], 2, '.', ' ') }} ₽
                            </td>
                            <td class="text-right">
                                @if ($op['type'] === 'payment')
                                    <a href="{{ route('payments.show', $m) }}"
                                        class="text-indigo-600 hover:underline">Открыть</a>
                                @else
                                    <a href="{{ route('expenses.show', $m) }}"
                                        class="text-indigo-600 hover:underline">Открыть</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-4">
                {{ $operations->links() }}
            </div>
        </div>
    </div>

    <script>
        (function() {
            const openBtn = document.getElementById('openPaymentOffcanvas');
            if (!openBtn) return;
            const panel = document.getElementById('paymentOffcanvasPanel');
            const container = document.getElementById('paymentOffcanvas');
            const content = document.getElementById('paymentOffcanvasContent');
            const backdrop = document.getElementById('paymentOffcanvasBackdrop');
            const closeBtn = document.getElementById('closePaymentOffcanvas');

            function open(url) {
                container.classList.remove('hidden');
                setTimeout(() => panel.classList.remove('translate-x-full'), 20);
                // fetch form (AJAX)
                fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html'
                        }
                    }).then(r => r.text())
                    .then(html => {
                        content.innerHTML = html;

                        // Инициализация Tom Select для загруженной формы
                        if (typeof window.initTomSelect === 'function') {
                            window.initTomSelect(content);
                        }

                        // Execute inline scripts from the loaded HTML (so initPaymentForm is defined)
                        const scripts = content.querySelectorAll('script');
                        scripts.forEach(s => {
                            try {
                                if (!s.src) {
                                    // Evaluate inline script content
                                    (0, eval)(s.textContent);
                                }
                            } catch (err) {
                                console.error('Error executing script from loaded form:', err);
                            }
                        });

                        // initialize payment form (if partial provided initialization function)
                        if (typeof initPaymentForm === 'function') {
                            try {
                                initPaymentForm(content);
                            } catch (e) {
                                console.error(e);
                            }
                        }

                        attachFormHandler();
                    })
                    .catch(() => {
                        content.innerHTML = '<div class="text-red-600">Ошибка загрузки формы</div>';
                    });
            }

            function close() {
                panel.classList.add('translate-x-full');
                setTimeout(() => container.classList.add('hidden'), 300);
            }

            function attachFormHandler() {
                const form = content.querySelector('form');
                if (!form) return;
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    // remove previous errors
                    content.querySelectorAll('.error-text').forEach(n => n.remove());

                    const fd = new FormData(form);
                    const action = form.getAttribute('action') || '{{ route('payments.store') }}';
                    fetch(action, {
                        method: form.getAttribute('method') || 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: fd,
                    }).then(async r => {
                        if (r.ok && (r.status === 201 || r.status === 200)) {
                            const json = await r.json();
                            close();
                            if (json.redirect) {
                                window.location.href = json.redirect;
                            } else {
                                window.location.reload();
                            }
                        } else if (r.status === 422) {
                            const json = await r.json();
                            const errs = json.errors || {};
                            Object.keys(errs).forEach(k => {
                                const el = form.querySelector('[name="' + k + '"]');
                                if (el) {
                                    let node = el.nextElementSibling;
                                    if (!node || !node.classList.contains('error-text')) {
                                        node = document.createElement('div');
                                        node.classList.add('text-sm', 'text-red-600',
                                            'error-text');
                                        el.after(node);
                                    }
                                    node.textContent = errs[k].join(', ');
                                }
                            });
                        } else {
                            const txt = await r.text();
                            content.innerHTML =
                                '<div class="text-red-600">Ошибка отправки формы</div>';
                        }
                    }).catch(() => {
                        content.innerHTML = '<div class="text-red-600">Ошибка сети</div>';
                    });
                });

                // attach cancel inside content
                const cancel = content.querySelector('#paymentOffcanvasCancel');
                if (cancel) {
                    cancel.addEventListener('click', function() {
                        backdrop.click();
                    });
                }
            }

            openBtn.addEventListener('click', function(e) {
                e.preventDefault();
                open(this.dataset.url);
            });
            if (closeBtn) closeBtn.addEventListener('click', close);
            if (backdrop) backdrop.addEventListener('click', close);
        })();

        // Expense Offcanvas Logic
        (function() {
            const openBtn = document.getElementById('openExpenseOffcanvas');
            if (!openBtn) return;
            const panel = document.getElementById('expenseOffcanvasPanel');
            const container = document.getElementById('expenseOffcanvas');
            const content = document.getElementById('expenseOffcanvasContent');
            const backdrop = document.getElementById('expenseOffcanvasBackdrop');
            const closeBtn = document.getElementById('closeExpenseOffcanvas');

            function open(url) {
                container.classList.remove('hidden');
                setTimeout(() => panel.classList.remove('translate-x-full'), 20);
                // fetch form (AJAX)
                fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html'
                        }
                    }).then(r => r.text())
                    .then(html => {
                        content.innerHTML = html;

                        // Инициализация Tom Select для загруженной формы
                        if (typeof window.initTomSelect === 'function') {
                            window.initTomSelect(content);
                        }

                        // Execute inline scripts from the loaded HTML
                        const scripts = content.querySelectorAll('script');
                        scripts.forEach(s => {
                            try {
                                if (!s.src) {
                                    (0, eval)(s.textContent);
                                }
                            } catch (err) {
                                console.error('Error executing script from loaded form:', err);
                            }
                        });

                        attachFormHandler();
                    })
                    .catch(() => {
                        content.innerHTML = '<div class="text-red-600">Ошибка загрузки формы</div>';
                    });
            }

            function close() {
                panel.classList.add('translate-x-full');
                setTimeout(() => container.classList.add('hidden'), 300);
            }

            function attachFormHandler() {
                const form = content.querySelector('form');
                if (!form) return;
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    // remove previous errors
                    content.querySelectorAll('.error-text').forEach(n => n.remove());

                    const fd = new FormData(form);
                    const action = form.getAttribute('action') || '{{ route('expenses.store') }}';
                    fetch(action, {
                        method: form.getAttribute('method') || 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: fd,
                    }).then(async r => {
                        if (r.ok && (r.status === 201 || r.status === 200)) {
                            const json = await r.json();
                            close();
                            if (json.redirect) {
                                window.location.href = json.redirect;
                            } else {
                                window.location.reload();
                            }
                        } else if (r.status === 422) {
                            const json = await r.json();
                            const errs = json.errors || {};
                            Object.keys(errs).forEach(k => {
                                const el = form.querySelector('[name="' + k + '"]');
                                if (el) {
                                    let node = el.nextElementSibling;
                                    if (!node || !node.classList.contains('error-text')) {
                                        node = document.createElement('div');
                                        node.classList.add('text-sm', 'text-red-600',
                                            'error-text');
                                        el.after(node);
                                    }
                                    node.textContent = errs[k].join(', ');
                                }
                            });
                        } else {
                            const txt = await r.text();
                            content.innerHTML =
                                '<div class="text-red-600">Ошибка отправки формы</div>';
                        }
                    }).catch(() => {
                        content.innerHTML = '<div class="text-red-600">Ошибка сети</div>';
                    });
                });

                // attach cancel inside content
                const cancel = content.querySelector('#expenseOffcanvasCancel');
                if (cancel) {
                    cancel.addEventListener('click', function() {
                        backdrop.click();
                    });
                }
            }

            openBtn.addEventListener('click', function(e) {
                e.preventDefault();
                open(this.dataset.url);
            });
            if (closeBtn) closeBtn.addEventListener('click', close);
            if (backdrop) backdrop.addEventListener('click', close);
        })();
    </script>
@endsection
