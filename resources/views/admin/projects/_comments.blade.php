{{-- Список комментариев (фрагмент) --}}
@php $items = $comments ?? $project->comments; @endphp

@if ($items->count())
    <div class="space-y-3" id="month-comments-list">
        @foreach ($items as $comment)
            @include('admin.projects._comment', ['comment' => $comment, 'project' => $project])
        @endforeach
    </div>
@else
    <div id="month-comments-list" class="text-sm text-gray-500">Пока нет комментариев.</div>
@endif
