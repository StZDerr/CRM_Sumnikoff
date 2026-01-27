<div class="border rounded-lg p-4 bg-white shadow-sm comment-item" data-id="{{ $comment->id }}">
    <!-- Верхняя часть: пользователь и дата -->
    <div class="flex items-start justify-between">
        <div>
            <div class="font-semibold text-gray-900">{{ $comment->user->name }}</div>
            <div class="text-xs text-gray-400 mt-0.5">{{ $comment->created_at->diffForHumans() }}</div>
        </div>

        @if (auth()->user() && (auth()->user()->isAdmin() || auth()->id() === $comment->user_id))
            <div class="flex items-center gap-3">
                <form class="delete-comment-form" action="{{ route('projects.comments.destroy', [$project, $comment]) }}"
                    method="POST" onsubmit="return false;">
                    @csrf
                    @method('DELETE')
                    <button class="text-xs text-red-600 hover:text-red-800 hover:underline transition">Удалить</button>
                </form>

                <button class="text-xs text-indigo-600 hover:text-indigo-800 edit-comment-btn" type="button"
                    data-update-url="{{ route('projects.comments.update', [$project, $comment]) }}">
                    Редактировать
                </button>
            </div>
        @endif

    </div>

    @php
        [$images, $docs] = $comment->photos->partition(function ($p) {
            $ext = strtolower(pathinfo($p->path, PATHINFO_EXTENSION));
            return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
        });
    @endphp

    @if ($images->count())
        <div class="mt-3 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
            @foreach ($images as $photo)
                <div class="relative file-item" data-id="{{ $photo->id }}">
                    <a href="{{ Storage::url($photo->path) }}"
                        class="glightbox block group overflow-hidden rounded-lg shadow-sm hover:shadow-md transition"
                        data-gallery="project-{{ $project->id }}" data-title="{{ e($photo->original_name) }}">
                        <img src="{{ Storage::url($photo->path) }}" alt="{{ $photo->original_name }}"
                            class="w-full h-28 object-contain rounded-lg bg-gray-100">
                    </a>

                    @if (auth()->user() && (auth()->user()->isAdmin() || auth()->id() === $comment->user_id))
                        <form class="delete-file-form absolute top-2 right-2"
                            action="{{ route('projects.comments.files.destroy', [$project, $comment, $photo]) }}"
                            method="POST" onsubmit="return false;">
                            @csrf
                            @method('DELETE')
                            <button class="text-white bg-black/50 rounded-full p-1 hover:bg-black/70"
                                title="Удалить файл">✕</button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    @if ($docs->count())
        <div class="mt-3">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Файлы</h4>
            <ul class="space-y-2">
                @foreach ($docs as $doc)
                    <li class="file-item" data-id="{{ $doc->id }}">
                        <div
                            class="inline-flex items-center gap-3 p-3 rounded-lg border border-gray-100 hover:bg-gray-50 transition w-full">
                            <a href="{{ Storage::url($doc->path) }}" target="_blank" rel="noopener"
                                class="flex items-center gap-3 flex-grow">
                                <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 7h6l4 4v6a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2z" />
                                </svg>
                                <div class="text-sm text-gray-700">{{ $doc->original_name }}</div>
                            </a>

                            <div class="ml-3 flex items-center gap-3">
                                <div class="text-xs text-gray-400">
                                    {{ strtoupper(pathinfo($doc->path, PATHINFO_EXTENSION)) }}</div>

                                @if (auth()->user() && (auth()->user()->isAdmin() || auth()->id() === $comment->user_id))
                                    <form class="delete-file-form"
                                        action="{{ route('projects.comments.files.destroy', [$project, $comment, $doc]) }}"
                                        method="POST" onsubmit="return false;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-xs text-red-600 hover:text-red-800">Удалить</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Текст комментария -->
    <div class="mt-3 text-gray-800 whitespace-pre-line leading-relaxed comment-body">
        {{ $comment->body }}
    </div>
</div>
