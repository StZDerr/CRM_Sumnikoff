@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto">
        <h1 class="text-2xl mb-4">Новый счёт</h1>
        <form action="{{ route('invoices.store') }}" method="POST" enctype="multipart/form-data">
            @include('admin.invoices._form')
            <div class="mt-4 flex justify-end">
                <a href="{{ route('invoices.index') }}" class="px-4 py-2 border rounded mr-2">Отмена</a>
                <button class="px-4 py-2 bg-indigo-600 text-white rounded">Сохранить</button>
            </div>
        </form>
    </div>
@endsection
