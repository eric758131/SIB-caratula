@extends('layouts.app')

@section('title', 'Editar categoría primaria')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Editar categoría primaria</h2>
        <p class="text-sm text-gray-500 mt-1">Actualiza los datos de la categoría seleccionada.</p>
    </div>

    <div class="bg-white rounded-lg shadow p-6 max-w-3xl">
        <form method="POST" action="{{ route('primary-categories.update', $primaryCategory) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            @include('primary-categories._form')

            <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
                <a href="{{ route('primary-categories.index') }}"
                   class="px-4 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100">
                    Cancelar
                </a>
                <button type="submit"
                        class="bg-brand-600 hover:bg-brand-700 text-white px-4 py-2 rounded shadow-sm">
                    Actualizar
                </button>
            </div>
        </form>
    </div>
@endsection