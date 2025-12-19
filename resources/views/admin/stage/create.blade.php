@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold">Новый этап</h1>
            <a href="{{ route('stages.index') }}" class="inline-flex items-center px-3 py-2 border rounded hover:bg-gray-50">←
                Назад</a>
        </div>

        <div class="bg-white shadow rounded p-6">
            <form action="{{ route('stages.store') }}" method="POST" class="space-y-6">
                @csrf
                @include('admin.stage._form')
                <div class="flex items-center gap-3">
                    <x-primary-button>Создать</x-primary-button>
                    <a href="{{ route('stages.index') }}"
                        class="inline-flex items-center px-3 py-2 border rounded hover:bg-gray-50">Отмена</a>
                </div>
            </form>
        </div>
    </div>
@endsection
