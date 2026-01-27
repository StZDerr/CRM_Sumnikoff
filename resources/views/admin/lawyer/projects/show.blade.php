@extends('layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto space-y-6">

        {{-- Header --}}
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">
                    {{ $project->title }}
                </h1>
                <div class="text-sm text-gray-500 mt-1">
                    Отправлено {{ $projectLawyer->sent_at?->format('d.m.Y H:i') ?? '—' }}
                    · {{ $projectLawyer->sender?->name ?? '—' }}
                </div>
            </div>

            @if ($projectLawyer->status !== 'closed')
                <form method="POST" action="{{ route('lawyer.projects.update', $projectLawyer) }}">
                    @csrf
                    @method('PATCH')
                    @if ($projectLawyer->status === 'processed')
                        <input type="hidden" name="status" value="closed">
                        <button class="px-4 py-2 bg-gray-700 text-white rounded-md text-sm hover:bg-gray-800">
                            ✖ Закрыть проект
                        </button>
                    @else
                        <input type="hidden" name="status" value="processed">
                        <button class="px-4 py-2 bg-green-600 text-white rounded-md text-sm hover:bg-green-700">
                            ✔ Взято в работу
                        </button>
                    @endif
                </form>
            @endif
        </div>

        {{-- Project info --}}
        <div class="bg-white rounded-lg shadow p-5 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <div class="text-xs uppercase text-gray-500">Организация</div>
                <div class="font-medium mt-1">
                    <a href="{{ route('lawyer.projects.organization', $projectLawyer) }}"
                        class="text-indigo-600 hover:text-indigo-800 hover:underline transition">
                        {{ $project->organization->name_short ?? '—' }}
                    </a>
                </div>
            </div>

            <div>
                <div class="text-xs uppercase text-gray-500">Проект</div>
                <div class="font-medium mt-1">
                    <a href="{{ route('lawyer.projects.project', $projectLawyer) }}"
                        class="text-indigo-600 hover:text-indigo-800 hover:underline transition">
                        {{ $project->title ?? '—' }}
                    </a>
                </div>
            </div>

            <div>
                <div class="text-xs uppercase text-gray-500">Дата закрытия проекта</div>
                <div class="font-medium mt-1">
                    {{ $project->closed_at?->format('d.m.Y') ?? '—' }}
                </div>
            </div>

            <div>
                <div class="text-xs uppercase text-gray-500">Статус</div>
                <div class="mt-1">
                    @php
                        $statusMap = [
                            'pending' => [
                                'label' => 'На рассмотрении',
                                'class' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                            ],
                            'processed' => [
                                'label' => 'Обработано',
                                'class' => 'bg-green-100 text-green-700 border-green-200',
                            ],
                            'closed' => [
                                'label' => 'Закрыт',
                                'class' => 'bg-gray-200 text-gray-800 border-gray-300',
                            ],
                            'reopened' => [
                                'label' => 'Переоткрыто',
                                'class' => 'bg-blue-100 text-blue-700 border-blue-200',
                            ],
                            'cancelled' => ['label' => 'Отменено', 'class' => 'bg-red-100 text-red-700 border-red-200'],
                        ];

                        $status = $statusMap[$projectLawyer->status] ?? [
                            'label' => ucfirst($projectLawyer->status ?? 'Неизвестно'),
                            'class' => 'bg-gray-100 text-gray-700 border-gray-200',
                        ];
                    @endphp

                    <span class="inline-flex px-2 py-1 text-xs rounded border {{ $status['class'] }}">
                        {{ $status['label'] }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Comments --}}
        <div class="bg-white rounded-lg shadow p-5">
            <h2 class="text-lg font-semibold mb-4">Комментарии по делу</h2>

            <div class="space-y-4">
                @forelse($comments as $comment)
                    <div
                        class="border-l-4 pl-4
                    {{ $comment->user?->role === 'lawyer' ? 'border-indigo-500' : 'border-gray-300' }}">

                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <span class="font-medium text-gray-800">
                                {{ $comment->user?->name ?? 'Удалённый пользователь' }}
                            </span>
                            <span class="text-xs text-gray-400">
                                {{ $comment->created_at->format('d.m.Y H:i') }}
                            </span>
                        </div>

                        <div class="mt-2 text-gray-800 whitespace-pre-line">
                            {{ $comment->body }}
                        </div>

                        {{-- Files --}}
                        @if (!empty($comment->files))
                            <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach ($comment->files as $f)
                                    @php
                                        $url = asset('storage/' . $f->path);
                                        $ext = strtolower(pathinfo($f->path, PATHINFO_EXTENSION));
                                        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                    @endphp

                                    <a href="{{ $url }}" target="_blank"
                                        class="flex items-center gap-3 border rounded p-2 hover:bg-gray-50">
                                        @if ($isImage)
                                            <img src="{{ $url }}" class="h-14 w-14 rounded object-cover">
                                        @else
                                            <div
                                                class="h-14 w-14 flex items-center justify-center bg-gray-100 rounded text-xs">
                                                .{{ $ext }}
                                            </div>
                                        @endif
                                        <div class="text-sm text-gray-700 truncate">
                                            {{ $f->original_name ?? basename($f->path) }}
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-gray-500 text-sm">
                        Комментариев пока нет
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Comment form --}}
        <div class="bg-white rounded-lg shadow p-5">
            <h3 class="font-semibold mb-3">Добавить комментарий</h3>

            <form method="POST" action="{{ route('projects.lawyer-comments.store', $project) }}"
                enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div>
                    <label for="comment" class="sr-only">Комментарий</label>
                    <textarea id="comment" name="comment" rows="5"
                        class="w-full border rounded-md px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                        placeholder="Опишите ситуацию или оставьте комментарий..." required>{{ old('comment') }}</textarea>
                    @error('comment')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="inline-flex items-center gap-3">
                        <span
                            class="inline-flex items-center px-3 py-2 bg-white border rounded-md text-sm text-gray-700 hover:bg-gray-50 cursor-pointer">
                            <svg class="h-4 w-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828L18 9.828a4 4 0 10-5.656-5.656L6.343 10.172a6 6 0 108.486 8.486L20 13.657"></path>
                            </svg>
                            <span class="ml-2">Прикрепить файл</span>
                        </span>

                        <input id="lawyer-files" type="file" name="files[]" multiple class="hidden"
                            accept=".pdf,.doc,.docx,.png,.jpg,.jpeg,.gif,.webp" />
                    </label>

                    <div id="selected-files" class="mt-2 text-sm text-gray-600"></div>

                    @error('files.*')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-between items-center">
                    <a href="{{ route('lawyer.projects.index') }}" class="text-sm text-gray-600 hover:underline">
                        ← Назад к списку
                    </a>

                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="ml-2">Отправить комментарий</span>
                    </button>
                </div>
            </form>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const fileInput = document.getElementById('lawyer-files');
                    const list = document.getElementById('selected-files');

                    if (!fileInput || !list) return;

                    fileInput.addEventListener('change', function () {
                        const files = Array.from(this.files);
                        if (files.length === 0) {
                            list.textContent = '';
                            return;
                        }

                        list.innerHTML = files.map(f => {
                            const name = f.name.length > 40 ? f.name.slice(0, 37) + '...' : f.name;
                            return `<div class="flex items-center gap-2">
                                        <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10v10H7z"></path>
                                        </svg>
                                        <span class="truncate">${name}</span>
                                    </div>`;
                        }).join('');
                    });
                });
            </script>
        </div>

    </div>
@endsection
