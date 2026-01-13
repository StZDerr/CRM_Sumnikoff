@extends('layouts.app')

@section('content')
    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
        <h1 class="text-2xl font-semibold mb-4">Редактировать специалность</h1>

        <div class="bg-white shadow-sm rounded p-6">
            <form action="{{ route('specialties.update', $specialty) }}" method="POST">
                @csrf
                @method('PATCH')
                @include('admin.specialty._form', ['specialty' => $specialty])
            </form>
        </div>
    </div>
@endsection
