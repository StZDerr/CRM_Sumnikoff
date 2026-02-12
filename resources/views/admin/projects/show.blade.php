@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto">
        {{-- Header --}}
        <div class="flex items-center gap-3 mb-6">
            {{-- Кнопка Назад --}}
            <a href="{{ route('projects.index') }}"
                class="inline-flex items-center gap-1 px-4 py-2 rounded-lg bg-green-600 text-white text-sm font-medium shadow hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-400 mr-auto">
                ← Назад
            </a>

            @can('update', $project)
                <a href="{{ route('projects.edit', $project) }}"
                    class="inline-flex items-center gap-1 px-4 py-2 rounded-lg bg-gradient-to-r from-indigo-600 to-indigo-500 text-white text-sm font-medium shadow hover:from-indigo-700 hover:to-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    Редактировать
                </a>
            @endcan

            @if (auth()->user()->isAdmin())
                <a href="{{ route('invoices.index', ['project' => $project->id]) }}"
                    class="inline-flex items-center gap-1 px-3 py-2 rounded-md border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">
                    Счета
                </a>

                <a href="{{ route('payments.index', ['project' => $project->id]) }}"
                    class="inline-flex items-center gap-1 px-3 py-2 rounded-md border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">
                    Поступления
                </a>

                <a href="{{ route('calendar.index', ['project' => $project->id]) }}"
                    class="inline-flex items-center gap-1 px-3 py-2 rounded-md border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">
                    Календарь
                </a>

                <a href="{{ route('projects.userHistory', $project) }}"
                    class="inline-flex items-center gap-1 px-3 py-2 rounded-md border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">
                    История
                </a>
            @endif

            <a href="{{ route('account-credentials.index', ['project' => $project->id]) }}"
                class="inline-flex items-center gap-1 px-3 py-2 rounded-md border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">
                Доступы проекта
            </a>
        </div>


        {{-- Main card --}}
        <div class="bg-white shadow rounded-lg p-6 space-y-6">
            {{-- Meta grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wide">Название проекта</div>
                    <div class="mt-1 font-medium text-gray-900">
                        {{ $project->title ?? '-' }}
                    </div>
                </div>


                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wide">Организация</div>
                    <div class="mt-1 font-medium text-gray-900">
                        {{ $project->organization?->name_short ?? ($project->organization?->name_full ?? '-') }}
                    </div>
                </div>

                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wide">Маркетолог</div>
                    <div class="mt-1 font-medium text-gray-900">{{ $project->marketer?->name ?? '-' }}</div>
                </div>

                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wide">Важность</div>
                    <div class="mt-1 font-medium text-gray-900">{{ $project->importance?->name ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wide">Город</div>
                    <div class="mt-1 font-medium text-gray-900">{{ $project->city ?? '-' }}</div>
                </div>
                @if (auth()->user()->isAdmin())
                    <div>
                        <div class="text-xs text-gray-500 uppercase tracking-wide">Тип оплаты</div>
                        <div class="mt-1 font-medium text-gray-900">{{ $project->paymentMethod?->title ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 uppercase tracking-wide">Сумма договора</div>
                        <div class="mt-1 font-medium text-gray-900">
                            {{ $project->contract_amount ? number_format($project->contract_amount, 2, '.', ' ') . ' ₽' : '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500 uppercase tracking-wide">Дата заключения договора</div>
                        <div class="mt-1 font-medium text-gray-900">
                            {{ \Illuminate\Support\Carbon::make($project->contract_date)?->format('Y-m-d') ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500 uppercase tracking-wide">Дата закрытия проекта</div>
                        <div class="mt-1 font-medium text-gray-900">
                            {{ \Illuminate\Support\Carbon::make($project->closed_at)?->format('Y-m-d') ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500 uppercase tracking-wide">Дата обновления</div>
                        <div class="mt-1 font-medium text-gray-900">{{ $project->updated_at?->format('Y-m-d H:i') ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500 uppercase tracking-wide">Дата создания</div>
                        <div class="mt-1 font-medium text-gray-900">{{ $project->created_at?->format('Y-m-d H:i') ?? '-' }}
                        </div>
                    </div>
                @endif

                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wide">Дата отчета</div>
                    <div class="mt-1 font-medium text-gray-900">
                        {{ $project->report_date?->format('Y-m-d') ?? '-' }}
                    </div>
                </div>


                {{-- <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wide">Срок оплаты</div>
                    <div class="mt-1 font-medium text-gray-900">
                        {{ $project->payment_due_day ? $project->payment_due_day . ' число' : '-' }}</div>
                </div> --}}

                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wide">Статус проекта</div>

                    @php
                        $statusMap = [
                            'in_progress' => [
                                'label' => 'В работе',
                                'class' => 'bg-green-100 text-green-800 border-green-300',
                            ],
                            'paused' => [
                                'label' => 'На паузе',
                                'class' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                            ],
                            'stopped' => [
                                'label' => 'Остановлен',
                                'class' => 'bg-red-100 text-red-800 border-red-300',
                            ],
                        ];

                        $status = $statusMap[$project->status] ?? [
                            'label' => 'Неизвестно',
                            'class' => 'bg-gray-100 text-gray-800 border-gray-300',
                        ];
                    @endphp

                    <span
                        class="inline-flex items-center px-3 py-1 mt-1 text-sm font-medium rounded-full border {{ $status['class'] }}">
                        {{ $status['label'] }}
                    </span>
                </div>

                <div class="md:col-span-2">
                    <div class="text-xs text-gray-500 uppercase tracking-wide">Комментарий</div>
                    <div class="mt-1 font-medium text-gray-900 whitespace-pre-line">{{ $project->comment ?? '-' }}</div>
                </div>
            </div>

            {{-- Stages --}}
            <div>
                <h2 class="text-lg font-semibold mb-3">Виды продвижения</h2>
                <div class="bg-gray-50 border border-gray-100 rounded p-4">
                    @if ($project->stages->count())
                        <ol class="list-decimal list-inside space-y-2">
                            @foreach ($project->stages->sortBy('pivot.sort_order') as $stage)
                                <li class="text-gray-900">{{ $stage->name }}</li>
                            @endforeach
                        </ol>
                    @else
                        <div class="text-sm text-gray-500">Этапы не заданы.</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Comments card --}}
        <div class="mt-6 bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold mb-4">Комментарии</h2>

            {{-- Add comment form --}}
            @auth
                <form id="comment-form" action="{{ route('projects.comments.store', $project) }}" method="POST"
                    enctype="multipart/form-data" class="mb-4">
                    @csrf
                    <textarea name="body" rows="3"
                        class="w-full border border-gray-200 rounded p-3 focus:ring-2 focus:ring-indigo-200"
                        placeholder="Оставьте комментарий...">{{ old('body') }}</textarea>
                    <input type="file" name="photos[]" accept="image/*" multiple
                        class="mt-1 block w-full text-sm text-gray-500
               file:mr-4 file:py-2 file:px-4
               file:rounded file:border-0
               file:text-sm file:font-semibold
               file:bg-indigo-600 file:text-white
               hover:file:bg-indigo-700
               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" />
                    <x-input-error :messages="$errors->get('photos')" class="mt-2" />
                    <x-input-error :messages="$errors->get('photos.*')" class="mt-2" />

                    <input type="file" name="documents[]"
                        accept="application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/zip,text/plain"
                        multiple
                        class="mt-2 block w-full text-sm text-gray-500
                        file:mr-4 file:py-2 file:px-4
                        file:rounded file:border-0
                        file:text-sm file:font-semibold
                        file:bg-gray-100 file:text-gray-800
                        hover:file:bg-gray-200
                        focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" />
                    <x-input-error :messages="$errors->get('documents')" class="mt-2" />
                    <x-input-error :messages="$errors->get('documents.*')" class="mt-2" />

                    <x-input-error :messages="$errors->get('body')" class="mt-2" />
                    <div class="mt-3 flex justify-end">
                        <button class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Добавить</button>
                    </div>
                </form>
            @else
                <div class="text-sm text-gray-500 mb-4">Только авторизованные пользователи могут оставлять комментарии.</div>
            @endauth

            {{-- Comments list --}}
            {{-- ===================== Комментарии ===================== --}}
            <section class="bg-white rounded-2xl border p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    Комментарии
                    <span class="text-sm text-gray-400 font-normal">
                        ({{ $project->comments->count() }})
                    </span>
                </h3>

                <div id="comments-list" data-comments-url="{{ route('projects.comments.index', $project) }}"
                    data-store-url="{{ route('projects.comments.store', $project) }}">

                    @if ($project->comments->count())
                        <div class="space-y-3">
                            @foreach ($project->comments as $comment)
                                <div class="rounded-xl border bg-gray-50 p-4 comment-item" data-id="{{ $comment->id }}">
                                    <div class="flex gap-3">
                                        {{-- Avatar --}}
                                        <div
                                            class="h-9 w-9 flex items-center justify-center rounded-full
                                       bg-blue-100 text-blue-700 text-sm font-semibold">
                                            {{ mb_strtoupper(mb_substr($comment->user?->name ?? ($comment->user_name ?? '—'), 0, 1)) }}

                                        </div>

                                        <div class="flex-1">
                                            <div class="flex items-start justify-between">
                                                <div>
                                                    <div class="font-medium text-gray-900">
                                                        {{ $comment->user?->name ?? ($comment->user_name ?? 'Удалённый пользователь') }}
                                                    </div>
                                                    <div class="text-xs text-gray-500">
                                                        {{ $comment->created_at->diffForHumans() }}
                                                    </div>
                                                </div>

                                                @if (auth()->user() && (auth()->user()->isAdmin() || auth()->id() === $comment->user_id))
                                                    <form class="delete-comment-form"
                                                        action="{{ route('projects.comments.destroy', [$project, $comment]) }}"
                                                        method="POST" onsubmit="return false;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="text-gray-400 hover:text-red-600 transition text-sm"
                                                            title="Удалить комментарий">
                                                            ✕
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>

                                            <div class="mt-2 text-gray-800 whitespace-pre-line leading-relaxed">
                                                {{ $comment->body }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-sm text-gray-500">
                            Пока нет комментариев.
                        </div>
                    @endif
                </div>
            </section>

            {{-- ===================== Домены ===================== --}}
            <section class="mt-8 bg-gray-50 rounded-2xl border p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    Домены проекта
                    <span class="text-sm text-gray-400 font-normal">
                        ({{ $project->domains->count() }})
                    </span>
                </h3>

                <div id="domains-list">
                    @if ($project->domains->count())
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($project->domains as $domain)
                                <div class="border rounded-xl p-4 bg-white shadow-sm hover:shadow-md transition">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                {{-- <span class="text-lg">🌐</span> --}}
                                                <a href="https://{{ $domain->name }}" target="_blank"
                                                    class="text-blue-600 font-semibold hover:underline break-all">
                                                    {{ $domain->name }}
                                                </a>
                                            </div>

                                            <div class="mt-1 text-xs text-gray-500">
                                                Добавлен {{ $domain->created_at->diffForHumans() }}
                                            </div>
                                        </div>

                                        <span
                                            class="text-xs px-2 py-1 rounded-full font-medium {{ $domain->status_color }}">
                                            {{ $domain->status_label }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-sm text-gray-500">
                            Домены не заданы.
                        </div>
                    @endif
                </div>
            </section>


        </div>
    </div>
    @if (auth()->user()->isAdmin())
        <form method="POST" action="{{ route('projects.destroy', $project) }}" class="inline-block"
            onsubmit="return confirm('Удалить проект? Это действие необратимо.');">
            @csrf
            @method('DELETE')
            <button type="submit"
                class="inline-flex items-center gap-1 px-3 py-2 rounded-md border border-red-300 text-sm text-red-600 hover:bg-red-50">
                Удалить проект
            </button>
        </form>
    @endif
    <!-- Comments JS moved to resources/js/projects/comments.js and is loaded via Vite (through resources/js/app.js) -->
@endsection
