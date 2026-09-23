@extends('layouts.app')

@section('title', 'Tipos de documento')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Tipos de documento</h2>
        <a href="#"
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
            + Nuevo
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <p class="text-gray-500">Aquí irá la tabla de tipos de documento.</p>
    </div>
@endsection