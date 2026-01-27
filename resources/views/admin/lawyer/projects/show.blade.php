@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-semibold"><a href="{{ route('lawyer.projects.project', $projectLawyer) }}"
                    class="text-indigo-600 hover:underline">{{ $project->title }}</a></h1>
            <div class="text-sm text-gray-500">Отправлено: {{ $projectLawyer->sent_at?->format('d.m.Y H:i') ?? '-' }}</div>
        </div>

        <div class="bg-white rounded shadow p-4 mb-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <div class="text-sm text-gray-500">Организация</div>
                    <div class="font-medium">
                        @if ($project->organization)
                            <a href="{{ route('lawyer.projects.organization', $projectLawyer) }}"
                                class="text-indigo-600 hover:underline">{{ $project->organization->name_short ?? $project->organization->name_full }}</a>
                        @else
                            -
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Дата закрытия</div>
                    <div class="font-medium">{{ $project->closed_at?->format('d.m.Y') ?? '—' }}</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded shadow p-4 mb-4">
            <h3 class="font-semibold mb-2">Комментарии по делу</h3>

            @forelse($comments as $comment)
                <div class="border rounded p-3 mb-3">
                    <div class="text-sm text-gray-600">{{ $comment->user?->name ?? 'Удалённый пользователь' }} · <span
                            class="text-xs text-gray-400">{{ $comment->role ?? '—' }}</span> ·
                        {{ $comment->created_at->format('d.m.Y H:i') }}</div>

                    <div class="mt-2">{!! nl2br(e($comment->body)) !!}</div>

                    {{-- legacy single file (backwards compatibility) --}}
                    @if ($comment->file_path)
                        <div class="mt-2"><a href="{{ asset('storage/' . $comment->file_path) }}" target="_blank"
                                class="text-indigo-600 hover:underline text-sm">Скачать файл</a></div>
                    @endif

                    {{-- new: files attached to lawyer comment (multiple) --}}
                    @if (!empty($comment->files) && count($comment->files))
                        <div class="mt-2 flex flex-col gap-2">
                            @foreach ($comment->files as $f)
                                @php
                                    $url = asset('storage/' . $f->path);
                                    $ext = strtolower(pathinfo($f->path, PATHINFO_EXTENSION));
                                    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg']);
                                @endphp

                                <div class="flex items-center gap-3">
                                    @if ($isImage)
                                        <a href="{{ $url }}" download class="inline-block">
                                            <img src="{{ $url }}" alt="{{ $f->original_name }}"
                                                class="h-16 rounded">
                                        </a>
                                    @else
                                        <div
                                            class="w-16 h-16 flex items-center justify-center bg-gray-50 rounded text-xs text-gray-500">
                                            Файл</div>
                                    @endif

                                    <div>
                                        <div class="text-sm"><a href="{{ $url }}" download
                                                class="text-indigo-600 hover:underline">Скачать
                                                {{ $f->original_name ?? basename($f->path) }}</a></div>
                                        <div class="text-xs text-gray-500">.{{ $ext }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-gray-500">Пока нет комментариев.</div>
            @endforelse

            <hr class="my-4">

            {{-- Flash / Errors --}}
            @if ($errors->any())
                <div class="mb-3 p-3 rounded bg-red-50 text-red-700">
                    <strong>Ошибка:</strong>
                    <div class="text-sm">{{ $errors->first() }}</div>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-3 p-3 rounded bg-red-50 text-red-700">{{ session('error') }}</div>
            @endif

            @if (session('success'))
                <div class="mb-3 p-3 rounded bg-green-50 text-green-700">{{ session('success') }}</div>
            @endif

            {{-- Форма: комментарий юриста (сохраняется в project_lawyer_comments) --}}
            <form method="POST" action="{{ route('projects.lawyer-comments.store', $project) }}"
                enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="redirect" value="{{ url()->current() }}">

                <div class="mb-3">
                    <label class="block text-sm text-gray-700">Комментарий (юрист/админ)</label>
                    <textarea name="comment" rows="4" class="w-full border rounded px-2 py-1" required>{{ old('comment') }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="block text-sm text-gray-700">Файлы (опционально; можно несколько)</label>

                    <div class="flex items-center gap-3">
                        <label for="lawyer_files"
                            class="inline-flex items-center px-3 py-2 bg-gray-100 border rounded cursor-pointer hover:bg-gray-200 text-sm">Выбрать
                            файлы</label>
                        <span id="lawyerFilesSelected" class="text-sm text-gray-600">Нет файлов</span>
                    </div>

                    <input id="lawyer_files" type="file" name="files[]" class="hidden" multiple
                        accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx,.zip,.txt,.rtf">

                    <div class="text-sm text-gray-500 mt-1">Разрешены: jpg, png, gif, webp, pdf, doc, docx, xls, xlsx, zip,
                        txt, rtf (макс 10 МБ/файл)</div>

                    @if ($errors->has('files') || $errors->has('files.*'))
                        <div class="text-sm text-red-600 mt-1">{{ $errors->first('files.*') ?? $errors->first('files') }}
                        </div>
                    @endif
                </div>

                <div class="flex justify-end space-x-2">
                    <a href="{{ route('lawyer.projects.index') }}" class="px-3 py-1 border rounded">← Назад</a>
                    <button class="px-3 py-1 bg-indigo-600 text-white rounded">Отправить комментарий</button>
                </div>
            </form>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                        const lf = document.getElementById('lawyer_files');
                        const lfl = document.getElementById('lawyerFilesSelected');

                        if (lf) {
                            lf.addEventListener('change', function() {
                                const names = Array.from(this.files).map(f => f.name).join(', ');
                                lfl.textContent = names || 'Нет файлов';
                            });
        </script>

        <form method="POST" action="{{ route('lawyer.projects.update', $projectLawyer) }}" class="mt-2">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="processed">
            <button class="px-3 py-1 bg-green-600 text-white rounded">Отметить как обработано</button>
        </form>

    </div>
@endsection
