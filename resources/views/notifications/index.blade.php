<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800">🔔 Уведомления</h2>

            <div class="flex items-center gap-3">
                <div class="text-sm text-gray-500">Всего: <span
                        class="font-semibold text-gray-800">{{ $notifications->total() }}</span></div>
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    @method('PATCH')
                    <button class="px-3 py-1 text-sm bg-gray-100 rounded hover:bg-gray-200">Отметить все
                        прочитанными</button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow p-5">
                <div class="divide-y space-y-3">
                    @forelse($notifications as $n)
                        <div
                            class="flex items-start gap-4 py-3 rounded-md px-3 {{ $n->isRead() ? 'bg-white text-gray-500 opacity-75' : 'bg-indigo-50 border border-indigo-100' }}">
                            <div class="mt-1">
                                @if (str_contains($n->type, 'removed') || str_contains($n->type, 'danger'))
                                    <div
                                        class="h-10 w-10 rounded-full bg-rose-100 text-rose-700 flex items-center justify-center text-sm font-semibold">
                                        ✕</div>
                                @elseif (str_contains($n->type, 'assigned'))
                                    <div
                                        class="h-10 w-10 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm font-semibold">
                                        ✓</div>
                                @elseif (str_contains($n->type, 'updated'))
                                    <div
                                        class="h-10 w-10 rounded-full bg-yellow-100 text-yellow-700 flex items-center justify-center text-sm font-semibold">
                                        !</div>
                                @else
                                    <div
                                        class="h-10 w-10 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-sm font-semibold">
                                        i</div>
                                @endif
                            </div>

                            <div class="flex-1">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <div
                                            class="text-sm font-semibold {{ $n->isRead() ? 'text-gray-500' : 'text-gray-800' }}">
                                            {{ $n->title }}</div>
                                        <div
                                            class="text-sm mt-1 whitespace-pre-line {{ $n->isRead() ? 'text-gray-500' : 'text-gray-600' }}">
                                            {{ $n->message }}
                                        </div>
                                        @if ($n->project)
                                            <a href="{{ route('projects.show', $n->project) }}"
                                                class="inline-block mt-2 text-sm text-indigo-600 hover:text-indigo-700">Открыть
                                                проект</a>
                                        @endif
                                    </div>

                                    <div class="text-xs text-gray-400 whitespace-nowrap">
                                        {{ optional($n->created_at)->diffForHumans() ?? '' }}</div>
                                </div>

                                <div class="mt-3 flex gap-2">
                                    @if (!$n->isRead())
                                        <form method="POST" action="{{ route('notifications.read', $n) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="text-sm text-gray-500 hover:text-gray-700">Отметить
                                                прочит.</button>
                                        </form>
                                    @endif

                                    @unless (auth()->user()->isMarketer())
                                        <form method="POST" action="{{ route('notifications.destroy', $n) }}"
                                            onsubmit="return confirm('Скрыть уведомление?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-sm text-gray-500 hover:text-gray-700">Удалить</button>
                                        </form>
                                    @endunless
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-gray-500">Нет уведомлений — хорошая новость! 🎉</div>
                    @endforelse
                </div>

                @if ($notifications->hasPages())
                    <div class="mt-5">{{ $notifications->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
