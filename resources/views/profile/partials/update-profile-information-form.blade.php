<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            Информация профиля
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Обновите информацию вашего аккаунта и адрес электронной почты.') }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        {{-- Основные данные --}}
        <div>
            <h3 class="text-lg font-medium text-gray-800 mb-3">Основная информация</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Имя</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                        class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                        required>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Логин</label>
                    <input type="text" name="login" value="{{ old('login', $user->login) }}"
                        class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                        required>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                        class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                        required>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Дата рождения</label>
                    <input type="date" name="birth_date"
                        value="{{ old('birth_date', $user->birth_date ? $user->birth_date->format('Y-m-d') : '') }}"
                        class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Должность</label>
                    <input type="text" name="position" value="{{ old('position', $user->position) }}"
                        placeholder="Например: Старший маркетолог"
                        class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Вид работы</label>
                    <input type="text" name="work_type" value="{{ old('work_type', $user->work_type) }}"
                        placeholder="Офис / Удалённо / Гибрид"
                        class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Где показывать виджет рабочего дня</label>
                    <select name="work_time_widget"
                        class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="popup" @selected(old('work_time_widget', $user->work_time_widget ?? 'popup') === 'popup')>Всплывающее окно (по аватарке)</option>
                        <option value="sidebar" @selected(old('work_time_widget', $user->work_time_widget ?? 'popup') === 'sidebar')>В сайдбаре (рядом с навигацией)</option>
                    </select>
                    <div class="text-xs text-gray-500 mt-1">Каждый пользователь может выбрать, где отображать свой
                        рабочий день.</div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Аватар (изображение)</label>

                    <div class="flex items-center gap-4">
                        <div id="avatarPreview"
                            class="h-20 w-20 rounded-full bg-gray-50 border border-gray-200 overflow-hidden flex items-center justify-center">
                            @if ($user->avatar)
                                <img id="avatarImg" src="{{ asset('storage/' . $user->avatar) }}" alt="Аватар"
                                    class="h-full w-full object-cover" />
                            @else
                                <svg class="h-10 w-10 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14c-4.418 0-8 1.79-8 4v1h16v-1c0-2.21-3.582-4-8-4z" />
                                </svg>
                            @endif
                        </div>

                        <div class="flex-1">
                            <div class="flex gap-2 items-center">
                                <label for="avatarInput"
                                    class="inline-flex items-center gap-2 px-3 py-2 bg-white border rounded-md text-sm text-gray-700 hover:bg-gray-50 cursor-pointer">
                                    <svg class="h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 16v-4a4 4 0 018 0v4m-5 4h2" />
                                    </svg>
                                    <span>Выбрать файл</span>
                                </label>

                                <button type="button" id="removeAvatarBtn"
                                    class="inline-flex items-center gap-2 px-3 py-2 bg-red-50 border border-red-100 text-red-600 rounded-md hover:bg-red-100 {{ $user->avatar ? '' : 'hidden' }}">
                                    Удалить
                                </button>
                            </div>

                            <p id="avatarFilename" class="mt-2 text-xs text-gray-500">
                                {{ $user->avatar ? basename($user->avatar) : 'PNG, JPG, до 2 МБ' }}</p>

                            @error('avatar')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror

                            <input id="avatarInput" type="file" name="avatar" accept="image/*" class="sr-only">

                            @if ($user->avatar)
                                <input type="checkbox" name="remove_avatar" id="remove_avatar" class="sr-only"
                                    value="1">
                                <label for="remove_avatar"
                                    class="mt-2 inline-flex items-center gap-2 cursor-pointer text-sm text-gray-600">Удалить
                                    текущий аватар</label>
                            @endif

                            <div class="text-xs text-gray-500 mt-2">Файл будет сохранён в папке
                                <code>storage/app/public/avatars</code>.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Социальные сети --}}
        <div>
            <h3 class="text-lg font-medium text-gray-800 mb-3">Социальные сети</h3>

            <div id="socialsContainer" class="space-y-2">
                @php
                    $oldSocials = old('socials', $user->socials->toArray());
                @endphp

                @forelse($oldSocials as $i => $social)
                    <div class="flex gap-2 items-center">
                        <select name="socials[{{ $i }}][platform]"
                            class="rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">—</option>
                            <option value="vk" @selected(($social['platform'] ?? '') === 'vk')>ВК</option>
                            <option value="telegram" @selected(($social['platform'] ?? '') === 'telegram')>ТГ</option>
                            <option value="maks" @selected(($social['platform'] ?? '') === 'maks')>МАКС</option>
                        </select>
                        <input type="text" name="socials[{{ $i }}][url]"
                            placeholder="Ссылка или логин" value="{{ $social['url'] ?? '' }}"
                            class="flex-1 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <button type="button"
                            class="removeSocial px-2 py-1 text-red-600 hover:bg-red-100 rounded">×</button>
                    </div>
                @empty
                    <div class="flex gap-2 items-center">
                        <select name="socials[0][platform]"
                            class="rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">—</option>
                            <option value="vk">ВК</option>
                            <option value="telegram">ТГ</option>
                            <option value="maks">МАКС</option>
                        </select>
                        <input type="text" name="socials[0][url]" placeholder="Ссылка или логин"
                            class="flex-1 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <button type="button"
                            class="removeSocial px-2 py-1 text-red-600 hover:bg-red-100 rounded">×</button>
                    </div>
                @endforelse
            </div>

            <button type="button" id="addSocial"
                class="mt-2 px-3 py-1 text-sm bg-gray-200 rounded-md hover:bg-gray-300 transition">Добавить
                соцсеть</button>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Сохранить</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600">{{ __('Сохранено.') }}</p>
            @endif
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('socialsContainer');
            const addBtn = document.getElementById('addSocial');

            if (addBtn && container) {
                addBtn.addEventListener('click', () => {
                    const index = container.children.length;
                    const div = document.createElement('div');
                    div.className = 'flex gap-2 items-center';
                    div.innerHTML = `
                        <select name="socials[${index}][platform]" class="rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">—</option>
                            <option value="vk">ВК</option>
                            <option value="telegram">ТГ</option>
                            <option value="maks">МАКС</option>
                        </select>
                        <input type="text" name="socials[${index}][url]" placeholder="Ссылка или логин" class="flex-1 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <button type="button" class="removeSocial px-2 py-1 text-red-600 hover:bg-red-100 rounded">×</button>
                    `;
                    container.appendChild(div);
                });

                container.addEventListener('click', (e) => {
                    if (e.target.classList.contains('removeSocial')) {
                        e.target.parentElement.remove();
                    }
                });
            }

            // Avatar preview + remove
            const avatarInput = document.getElementById('avatarInput');
            const avatarPreview = document.getElementById('avatarPreview');
            const avatarImg = document.getElementById('avatarImg');
            const avatarFilename = document.getElementById('avatarFilename');
            const removeAvatarBtn = document.getElementById('removeAvatarBtn');
            const removeAvatarCheckbox = document.getElementById('remove_avatar');

            if (avatarInput) {
                avatarInput.addEventListener('change', (ev) => {
                    const file = avatarInput.files && avatarInput.files[0];
                    if (!file) return;
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        if (avatarImg) {
                            avatarImg.src = e.target.result;
                        } else {
                            const img = document.createElement('img');
                            img.id = 'avatarImg';
                            img.className = 'h-full w-full object-cover';
                            img.src = e.target.result;
                            avatarPreview.innerHTML = '';
                            avatarPreview.appendChild(img);
                        }
                        if (removeAvatarCheckbox) removeAvatarCheckbox.checked = false;
                        if (removeAvatarBtn) removeAvatarBtn.classList.remove('hidden');
                        if (avatarFilename) avatarFilename.textContent = file.name;
                    };
                    reader.readAsDataURL(file);
                });
            }

            if (removeAvatarBtn) {
                removeAvatarBtn.addEventListener('click', () => {
                    if (removeAvatarCheckbox) removeAvatarCheckbox.checked = true;
                    if (avatarInput) avatarInput.value = '';
                    if (avatarImg) {
                        avatarPreview.innerHTML =
                            '<svg class="h-10 w-10 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14c-4.418 0-8 1.79-8 4v1h16v-1c0-2.21-3.582-4-8-4z"/></svg>';
                    }
                    if (avatarFilename) avatarFilename.textContent = 'Аватар будет удалён';
                    removeAvatarBtn.classList.add('hidden');
                });
            }
        });
    </script>
</section>
