@extends('layouts.app')

@section('content')
    <h1 class="text-xl font-bold mb-4">Создать доступ</h1>

    @include('admin.account_credentials._formSSH', ['project' => $project])
@endsection
