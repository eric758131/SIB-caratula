@extends('layouts.app')

@section('title', 'Nueva categoría primaria')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Nueva categoría primaria</h2>
        <p class="text-sm text-gray-500 mt-1">Completa los datos para registrar una nueva categoría.</p>
    </div>

    <div class="bg-white rounded-lg shadow p-6 max-w-3xl">
        <form method="POST" action="{{ route('primary-categories.store') }}" enctype="multipart/form-data">
            @csrf

            @include('primary-categories._form')

            <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
                <a href="{{ route('primary-categories.index') }}"
                   class="px-4 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100">
                    Cancelar
                </a>
                <button type="submit"
                        class="bg-brand-600 hover:bg-brand-700 text-white px-4 py-2 rounded shadow-sm">
                    Guardar
                </button>
            </div>
        </form>
    </div>
@endsection