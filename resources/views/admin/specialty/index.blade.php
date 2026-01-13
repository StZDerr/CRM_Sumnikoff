@extends('layouts.app')

@section('content')
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold">Специальности</h1>
            <a href="{{ route('specialties.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded shadow-sm hover:bg-indigo-500">
                Добавить специальность
            </a>
        </div>

        <div class="bg-white shadow-sm rounded overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Название</th>
                        <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Оклад</th>
                        <th class="px-4 py-3 text-center text-sm font-medium text-gray-700">Вкл</th>
                        <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Действия</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($specialties as $s)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $s->name }}</div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="text-gray-700">{{ number_format($s->salary, 0, '.', ' ') }} ₽</div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($s->active)
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded text-xs bg-green-50 text-green-700">Да</span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded text-xs bg-gray-50 text-gray-500">Нет</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('specialties.edit', $s) }}"
                                        class="text-indigo-600 hover:underline text-sm">Изменить</a>

                                    <form action="{{ route('specialties.destroy', $s) }}" method="POST"
                                        onsubmit="return confirm('Удалить специальность?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline text-sm">Удалить</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-gray-500">Нет специальностей.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="p-4">
                {{ $specialties->links() }}
            </div>
        </div>
    </div>
@endsection
