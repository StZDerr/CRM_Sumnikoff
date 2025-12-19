@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto">
        <h1 class="text-2xl mb-4">Новое поступление</h1>
        <form action="{{ route('payments.store') }}" method="POST">
            @include('admin.payments._form')
            <div class="mt-4 flex justify-end">
                <a href="{{ route('payments.index') }}" class="px-4 py-2 border rounded mr-2">Отмена</a>
                <button class="px-4 py-2 bg-indigo-600 text-white rounded">Сохранить</button>
            </div>
        </form>
    </div>
@endsection
