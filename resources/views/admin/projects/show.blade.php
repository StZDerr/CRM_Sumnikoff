@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto">
        {{-- Header --}}
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('projects.edit', $project) }}"
                class="inline-flex items-center px-4 py-2 rounded-md bg-indigo-600 text-white text-sm hover:bg-indigo-700">Редактировать</a>

            <a href="{{ route('invoices.index', ['project' => $project->id]) }}"
                class="inline-flex items-center px-3 py-2 border rounded-md text-sm hover:bg-gray-50">Счета проекта</a>

            <a href="{{ route('payments.index', ['project' => $project->id]) }}"
                class="inline-flex items-center px-3 py-2 border rounded-md text-sm hover:bg-gray-50">Поступления проекта</a>

            <a href="{{ route('projects.index') }}"
                class="inline-flex items-center px-3 py-2 border rounded-md text-sm hover:bg-gray-50">← Назад</a>

            <a href="{{ route('calendar.index', ['project' => $project->id]) }}"
                class="ml-auto inline-flex items-center px-3 py-2 border rounded-md text-sm hover:bg-gray-50">Календарь</a>
        </div>

        {{-- Main card --}}
        <div class="bg-white shadow rounded-lg p-6 space-y-6">
            {{-- Meta grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wide">Дата создания</div>
                    <div class="mt-1 font-medium text-gray-900">{{ $project->created_at?->format('Y-m-d H:i') ?? '-' }}
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
                    <div class="text-xs text-gray-500 uppercase tracking-wide">Город</div>
                    <div class="mt-1 font-medium text-gray-900">{{ $project->city ?? '-' }}</div>
                </div>

                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wide">Срок оплаты</div>
                    <div class="mt-1 font-medium text-gray-900">
                        {{ $project->payment_due_day ? $project->payment_due_day . ' число' : '-' }}</div>
                </div>

                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wide">Дата обновления</div>
                    <div class="mt-1 font-medium text-gray-900">{{ $project->updated_at?->format('Y-m-d H:i') ?? '-' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wide">Статус</div>
                    <div class="mt-1 font-medium text-gray-900">
                        {{ $project->deleted_at ? 'Удалён ' . $project->deleted_at->format('Y-m-d H:i') : 'Активен' }}
                    </div>
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
                    class="mb-4">
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
                    <x-input-error :messages="$errors->get('body')" class="mt-2" />
                    <div class="mt-3 flex justify-end">
                        <button class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Добавить</button>
                    </div>
                </form>
            @else
                <div class="text-sm text-gray-500 mb-4">Только авторизованные пользователи могут оставлять комментарии.</div>
            @endauth

            {{-- Comments list --}}
            <div id="comments-list" data-comments-url="{{ route('projects.comments.index', $project) }}"
                data-store-url="{{ route('projects.comments.store', $project) }}">
                @if ($project->comments->count())
                    <div class="space-y-3">
                        @foreach ($project->comments as $comment)
                            <div class="border rounded-md p-4 bg-white comment-item" data-id="{{ $comment->id }}">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $comment->user->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}
                                        </div>
                                    </div>

                                    @if (auth()->user() && (auth()->user()->isAdmin() || auth()->id() === $comment->user_id))
                                        <form class="delete-comment-form"
                                            action="{{ route('projects.comments.destroy', [$project, $comment]) }}"
                                            method="POST" onsubmit="return false;">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-sm text-red-600 hover:underline">Удалить</button>
                                        </form>
                                    @endif
                                </div>

                                <div class="mt-3 text-gray-800 whitespace-pre-line">{{ $comment->body }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-sm text-gray-500">Пока нет комментариев.</div>
                @endif
            </div>
        </div>
    </div>
    <!-- Comments JS moved to resources/js/projects/comments.js and is loaded via Vite (through resources/js/app.js) -->




@endsection
