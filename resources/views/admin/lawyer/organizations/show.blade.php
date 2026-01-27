@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-semibold">{{ $organization->name_short ?? $organization->name_full }}</h1>
        </div>

        <div class="bg-white rounded shadow p-4 mb-4">
            <div class="mb-3">
                <div class="text-sm text-gray-500">Полное название</div>
                <div class="font-medium">{{ $organization->name_full ?? '—' }}</div>
            </div>
            <div class="mb-3">
                <div class="text-sm text-gray-500">Контакты</div>
                <div class="font-medium">{{ $organization->contact ?? '—' }}</div>
            </div>

            <div class="mt-4">
                <a href="{{ route('lawyer.projects.show', $projectLawyer) }}" class="px-3 py-1 border rounded">← Назад</a>
            </div>
        </div>
    </div>
@endsection
