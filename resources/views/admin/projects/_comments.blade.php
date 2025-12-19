{{-- Список комментариев (фрагмент) --}}
@if ($project->comments->count())
    <div class="space-y-3">
        @foreach ($project->comments as $comment)
            @include('admin.projects._comment', ['comment' => $comment, 'project' => $project])
        @endforeach
    </div>
@else
    <div class="text-sm text-gray-500">Пока нет комментариев.</div>
@endif
