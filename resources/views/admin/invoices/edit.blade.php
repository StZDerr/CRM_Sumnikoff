@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto">
        <h1 class="text-2xl mb-4">Редактировать счёт</h1>
        <form action="{{ route('invoices.update', $invoice) }}" method="POST" enctype="multipart/form-data">
            @method('PATCH')
            @include('admin.invoices._form')
            <div class="mt-4 flex justify-end">
                <a href="{{ route('invoices.show', $invoice) }}" class="px-4 py-2 border rounded mr-2">Отмена</a>
                <button class="px-4 py-2 bg-indigo-600 text-white rounded">Обновить</button>
            </div>
        </form>
    </div>
@endsection
