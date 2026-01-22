@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold">Категории расходов</h1>
            <a href="{{ route('expense-categories.create') }}"
                class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                Добавить
            </a>
        </div>

        <div class="bg-white shadow rounded">
            <div class="p-4 border-b">
                <p class="text-sm text-gray-600">Перетащите элементы для изменения порядка. Изменения сохраняются
                    автоматически.</p>
            </div>

            <div id="expense-categories-list" data-reorder-url="{{ route('expense-categories.reorder') }}" class="divide-y"
                role="list">
                @forelse ($items as $item)
                    <div data-id="{{ $item->id }}" class="flex items-center gap-4 px-4 py-3 hover:bg-gray-50"
                        role="listitem">
                        <div class="drag-handle cursor-grab text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8 9h.01M8 15h.01M12 9h.01M12 15h.01M16 9h.01M16 15h.01" />
                            </svg>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="font-medium text-gray-900">{{ $item->title }}</div>
                                    @if ($item->is_office)
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                            Офис
                                        </span>
                                    @endif
                                    @if ($item->is_salary)
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            ЗП
                                        </span>
                                    @endif
                                    @if ($item->is_domains_hosting)
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4 transition-transform duration-200 hover:scale-110"
                                                fill="none" viewBox="0 0 26 27" stroke="currentColor" stroke-width="2">
                                                <path d="M2.33301 17.0908H23.6663" stroke="currentColor"
                                                    stroke-linecap="round" stroke-linejoin="round" />
                                                <path d="M2.33301 9.09082H23.6663" stroke="currentColor"
                                                    stroke-linecap="round" stroke-linejoin="round" />
                                                <path
                                                    d="M13 25.0908C19.6274 25.0908 25 19.7182 25 13.0908C25 6.4634 19.6274 1.09082 13 1.09082C6.37258 1.09082 1 6.4634 1 13.0908C1 19.7182 6.37258 25.0908 13 25.0908Z"
                                                    stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" />
                                                <path
                                                    d="M8.56543 14.0908C8.76021 17.3432 9.89724 20.3411 11.71 22.8145C11.5867 22.9501 11.4622 23.085 11.334 23.2158L11.2451 23.3057C9.30961 20.7035 8.09464 17.5335 7.89648 14.0908H8.56543ZM18.1045 14.0908C17.9063 17.5332 16.6911 20.7026 14.7559 23.3047L14.667 23.2148C14.5387 23.0839 14.4133 22.9501 14.29 22.8145C16.1029 20.341 17.2417 17.3435 17.4365 14.0908H18.1045ZM14.7549 2.87598C16.6906 5.47823 17.9063 8.64792 18.1045 12.0908H17.4365C17.2417 8.83789 16.1032 5.83978 14.29 3.36621C14.4132 3.23071 14.5389 3.09757 14.667 2.9668L14.7549 2.87598ZM11.334 2.9668C11.462 3.09741 11.587 3.23088 11.71 3.36621C9.89698 5.83969 8.76028 8.83812 8.56543 12.0908H7.89648C8.09468 8.64813 9.30967 5.47814 11.2451 2.87598L11.334 2.9668Z"
                                                    fill="black" stroke="currentColor" stroke-width="2" />
                                            </svg>
                                            Домены
                                        </span>
                                    @endif
                                    <div class="text-xs text-gray-500">{{ $item->slug ?? '-' }}</div>
                                </div>
                                <div class="text-sm text-gray-400">#{{ $item->sort_order }}</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="{{ route('expense-categories.edit', $item) }}"
                                class="text-indigo-600 hover:text-indigo-800 text-sm p-2">Редактировать</a>

                            <form action="{{ route('expense-categories.destroy', $item) }}" method="POST"
                                onsubmit="return confirm('Удалить категорию?');">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 hover:text-red-800 text-sm p-2">Удалить</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-6 text-gray-500">Пока нет категорий расходов.</div>
                @endforelse
            </div>

            <div class="p-4">{{ $items->links() }}</div>
        </div>
    </div>
@endsection
