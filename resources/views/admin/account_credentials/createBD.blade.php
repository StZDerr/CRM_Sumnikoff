@extends('layouts.app')

@section('content')
    <h1 class="text-xl font-bold mb-4">Создать доступ к БД {{ $project->title }}</h1>

    @include('admin.account_credentials._formBD')
@endsection
