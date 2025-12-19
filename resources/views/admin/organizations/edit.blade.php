@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold">Редактировать организацию</h1>
            <a href="{{ route('organizations.index') }}"
                class="inline-flex items-center px-3 py-2 border rounded hover:bg-gray-50">← Назад</a>
        </div>

        <div class="bg-white shadow rounded p-6">
            <form action="{{ route('organizations.update', $organization) }}" method="POST" class="space-y-6">
                @method('PUT')
                @include('admin.organizations._form', ['submit' => 'Обновить'])
            </form>
        </div>
    </div>
@endsection
