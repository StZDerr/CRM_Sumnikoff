<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        Забыли пароль? Введите ваш логин: если для аккаунта указан Email, мы отправим на него ссылку для сброса пароля.
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Login -->
        <div>
            <x-input-label for="login" :value="'Логин'" />
            <x-text-input id="login" class="block mt-1 w-full" type="text" name="login" :value="old('login')" required
                autofocus />
            <x-input-error :messages="$errors->get('login')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                Отправить ссылку для сброса пароля
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
