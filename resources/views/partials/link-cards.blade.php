{{-- Part: Link Cards (grid + modal)
     Variables expected: $linkCards
--}}
<div class="bg-white rounded-xl shadow p-6">
    <div class="mb-4">
        <div class="text-sm font-medium text-gray-800">Карточки ссылок</div>
        <div class="text-xs text-gray-500">Перетаскивайте карточки для изменения порядка</div>
    </div>

    <div class="text-xs text-gray-500 mb-4">Добавьте ссылку через карточку «+».</div>

    <div id="link-cards-grid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <button type="button" id="link-card-add"
            class="link-card-add bg-white rounded-xl p-4 border border-dashed border-gray-300 shadow-sm hover:shadow-lg transition duration-200 transform hover:-translate-y-0.5 flex flex-col items-center justify-center text-center">
            <div class="w-12 h-12 rounded-full bg-indigo-50 border border-indigo-200 flex items-center justify-center">
                <span class="text-2xl text-indigo-600">+</span>
            </div>
            <div class="text-sm font-semibold text-gray-700 mt-2">Добавить</div>
        </button>

        @foreach ($linkCards as $card)
            <div class="link-card relative bg-gray-50 rounded-xl p-4 border border-gray-100 shadow-sm hover:shadow-lg transition duration-200 transform hover:-translate-y-0.5"
                draggable="true" data-id="{{ $card->id }}">

                <!-- Кнопка редактирования (карандаш) -->
                <button type="button" data-id="{{ $card->id }}" data-title="{{ e($card->title) }}"
                    data-url="{{ e($card->url) }}" data-icon="{{ e($card->icon) }}"
                    class="edit-card-btn absolute top-2 left-2 z-10 text-gray-400 hover:text-indigo-600 bg-white rounded-full w-6 h-6 flex items-center justify-center text-xs shadow">✎</button>

                <!-- Удаление -->
                <form method="POST" action="{{ route('link-cards.destroy', $card->id) }}"
                    class="absolute top-2 right-2 z-10">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="text-gray-400 hover:text-red-500 bg-white rounded-full w-6 h-6 flex items-center justify-center text-xs shadow">✕</button>
                </form>

                @php
                    $cardHost = parse_url($card->url ?? '', PHP_URL_HOST);
                    $cardHostAscii = $cardHost ? idn_to_ascii($cardHost, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46) : null;
                    $cardFavicon = $cardHostAscii
                        ? 'https://www.google.com/s2/favicons?sz=64&domain=' . urlencode($cardHostAscii)
                        : null;
                    $cardFaviconFallbacks = array_values(
                        array_filter([
                            $cardHostAscii ? 'https://faviconkit.com/' . $cardHostAscii . '/64' : null,
                            $cardHostAscii ? 'https://icons.duckduckgo.com/ip3/' . $cardHostAscii . '.ico' : null,
                        ]),
                    );
                @endphp

                <a href="{{ $card->url }}" target="_blank" rel="noopener noreferrer" draggable="false"
                    class="flex flex-col items-center text-center gap-2">
                    <div
                        class="w-12 h-12 rounded-full bg-indigo-50 border border-indigo-100 flex items-center justify-center overflow-hidden">
                        @if (!empty($card->icon))
                            <img src="{{ $card->icon }}" alt="{{ e($card->title) }}"
                                class="w-8 h-8 rounded-full object-cover shadow-sm" draggable="false" />
                        @elseif ($cardFavicon)
                            <img src="{{ $cardFavicon }}" alt="{{ e($card->title) }}"
                                class="w-8 h-8 rounded-full object-cover bg-white p-0.5 shadow-sm" draggable="false"
                                @if (!empty($cardFaviconFallbacks)) onerror='(function(img){const f=@json($cardFaviconFallbacks);const i=Number(img.dataset.fidx||0);if(i < f.length){img.dataset.fidx=i+1;img.src=f[i];}else{img.onerror=null;img.remove();}})(this)' @endif />
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M12 4.5a7.5 7.5 0 00-7.5 7.5v4.125A2.625 2.625 0 007.125 18h9.75A2.625 2.625 0 0019.5 16.125V12A7.5 7.5 0 0012 4.5z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M9.75 18v1.5a2.25 2.25 0 004.5 0V18" />
                            </svg>
                        @endif
                    </div>

                    <div class="text-sm font-semibold text-gray-900">{{ $card->title }}</div>
                </a>

                <div class="text-xs text-gray-500 break-all mt-1">{{ $card->url }}</div>
            </div>
        @endforeach

    </div>

    @if (($linkCards ?? collect())->isEmpty())
        <div class="text-sm text-gray-500 mt-4">Пока нет карточек. Добавьте первую ссылку.</div>
    @endif
</div>

{{-- Modal for create/edit (shared) --}}
<div id="link-card-modal" class="fixed inset-0 z-50 hidden">
    <div id="link-card-overlay" class="absolute inset-0 bg-black/50"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="w-full max-w-xl bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b">
                <div id="link-card-modal-title" class="text-lg font-semibold text-gray-800">Новая карточка</div>
                <button type="button" id="link-card-close" class="text-gray-500 hover:text-gray-700">✕</button>
            </div>

            <form id="link-card-form" method="POST" action="{{ route('link-cards.store') }}" class="p-5 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Название</label>
                    <input type="text" name="title" required placeholder="Название"
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 text-sm px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ссылка</label>
                    <input type="url" name="url" required placeholder="https://..."
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 text-sm px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Иконка</label>
                    <input type="text" name="icon" placeholder="Ссылка на иконку (опционально)"
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 text-sm px-3 py-2" />
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" id="link-card-cancel"
                        class="px-4 py-2 text-sm rounded-lg border border-gray-300 hover:bg-gray-50">Отмена</button>
                    <button type="submit" id="link-card-submit"
                        class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">Добавить</button>
                </div>
            </form>
        </div>
    </div>
</div>
