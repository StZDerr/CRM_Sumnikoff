@extends('layouts.app')

@section('title', 'Редактировать домен')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Редактировать домен</h1>
                <p class="text-sm text-gray-500">Ручной домен</p>
            </div>
        </div>

        <div class="rounded-xl bg-white p-5 shadow">
            <form method="POST" action="{{ route('domains.update', $domain) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm text-gray-500 mb-1">Домен</label>
                    <input type="text" name="name" value="{{ old('name', $domain->name) }}"
                        class="w-full border rounded px-3 py-2" required>
                    @error('name')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm text-gray-500 mb-1">Статус</label>
                    <select name="status" class="w-full border rounded px-3 py-2" required>
                        <option value="A" @selected(old('status', $domain->status) === 'A')>Активна</option>
                        <option value="N" @selected(old('status', $domain->status) === 'N')>Неактивна</option>
                        <option value="S" @selected(old('status', $domain->status) === 'S')>Приостановлена</option>
                    </select>
                    @error('status')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm text-gray-500 mb-1">Истекает</label>
                        <input type="date" name="expires_at"
                            value="{{ old('expires_at', optional($domain->expires_at)->format('Y-m-d')) }}"
                            class="w-full border rounded px-3 py-2">
                        @error('expires_at')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm text-gray-500 mb-1">Цена продления</label>
                        <input type="number" step="0.01" name="renew_price"
                            value="{{ old('renew_price', $domain->renew_price) }}" class="w-full border rounded px-3 py-2">
                        @error('renew_price')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm text-gray-500 mb-1">Валюта</label>
                        <input type="text" name="currency" value="{{ old('currency', $domain->currency ?? 'RUR') }}"
                            class="w-full border rounded px-3 py-2">
                        @error('currency')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm text-gray-500 mb-1">Проект</label>
                    <select name="project_id" class="w-full border rounded px-3 py-2">
                        <option value="">— Не выбран —</option>
                        @foreach ($projects as $project)
                            <option value="{{ $project->id }}" @selected((string) old('project_id', $domain->project_id) === (string) $project->id)>
                                {{ $project->title }}
                            </option>
                        @endforeach
                    </select>
                    @error('project_id')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="auto_renew" name="auto_renew" value="1" @checked(old('auto_renew', $domain->auto_renew))>
                    <label for="auto_renew" class="text-sm text-gray-600">Автопродление</label>
                </div>

                <div class="flex justify-end gap-2">
                    <a href="{{ route('domains.index') }}" class="px-3 py-2 text-sm border rounded">Отмена</a>
                    <button type="submit" class="px-3 py-2 text-sm bg-indigo-600 text-white rounded">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
@endsection
