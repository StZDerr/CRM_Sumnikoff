<div class="border rounded-lg p-4 bg-white shadow-sm comment-item" data-id="{{ $comment->id }}">
    <!-- Верхняя часть: пользователь и дата -->
    <div class="flex items-start justify-between">
        <div>
            <div class="font-semibold text-gray-900">{{ $comment->user->name }}</div>
            <div class="text-xs text-gray-400 mt-0.5">{{ $comment->created_at->diffForHumans() }}</div>
        </div>

        @if (auth()->user() && (auth()->user()->isAdmin() || auth()->id() === $comment->user_id))
            <form class="delete-comment-form" action="{{ route('projects.comments.destroy', [$project, $comment]) }}"
                method="POST" onsubmit="return false;">
                @csrf
                @method('DELETE')
                <button class="text-xs text-red-600 hover:text-red-800 hover:underline transition">Удалить</button>
            </form>
        @endif
    </div>

    <!-- Фотографии комментария -->
    @if ($comment->photos->count())
        <div class="mt-3 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
            @foreach ($comment->photos as $photo)
                <a href="{{ Storage::url($photo->path) }}"
                    class="glightbox block group overflow-hidden rounded-lg shadow-sm hover:shadow-md transition"
                    data-gallery="project-{{ $project->id }}" data-title="{{ e($photo->original_name) }}">
                    <img src="{{ Storage::url($photo->path) }}" alt="{{ $photo->original_name }}"
                        class="w-full h-28 object-contain rounded-lg bg-gray-100">
                </a>
            @endforeach
        </div>
    @endif

    <!-- Текст комментария -->
    <div class="mt-3 text-gray-800 whitespace-pre-line leading-relaxed">
        {{ $comment->body }}
    </div>
</div>
