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

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
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
                        <input type="text" name="socials[{{ $i }}][url]" placeholder="Ссылка или логин"
                            value="{{ $social['url'] ?? '' }}"
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
        });
    </script>
</section>
