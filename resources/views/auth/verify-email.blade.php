<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        Спасибо за регистрацию! Прежде чем продолжить, подтвердите ваш email, перейдя по ссылке, которую мы вам
        отправили. Если вы не получили письмо, мы можем выслать его снова.
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600">
            Новая ссылка для подтверждения была отправлена на указанный адрес электронной почты.
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    Отправить подтверждающее письмо
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit"
                class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Выйти
            </button>
        </form>
    </div>
</x-guest-layout>
