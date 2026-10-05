@extends('layouts.app')

@section('title', 'Ramas de ingeniería')

@section('content')
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Ramas de ingeniería</h2>
        <a href="{{ route('branches.create') }}"
           class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white px-4 py-2 rounded shadow-sm">
            <span class="text-lg leading-none">+</span>
            <span>Nueva rama</span>
        </a>
    </div>

    <form method="GET" action="{{ route('branches.index') }}"
          class="bg-white rounded-lg shadow p-4 mb-6">
        <x-keep-sort />
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                <input type="text" name="search" id="search" value="{{ $search }}"
                       placeholder="Nombre o ID..."
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                <select name="status" id="status"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="" {{ $status === '' ? 'selected' : '' }}>Todos</option>
                    <option value="1" {{ $status === '1' ? 'selected' : '' }}>Activos</option>
                    <option value="0" {{ $status === '0' ? 'selected' : '' }}>Inactivos</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded">Filtrar</button>
                @if ($search !== '' || $status !== '')
                    <a href="{{ route('branches.index') }}" class="text-gray-500 hover:text-gray-700 px-3 py-2">Limpiar</a>
                @endif
            </div>
        </div>
    </form>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <x-sort-th column="id" class="w-16">#</x-sort-th>
                        <x-sort-th column="name" default>Nombre</x-sort-th>
                        <x-sort-th column="status" class="w-32">Estado</x-sort-th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider w-56">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($branches as $branch)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $branch->id }}</td>
                            <td class="px-4 py-3 text-sm text-gray-800">{{ $branch->name }}</td>
                            <td class="px-4 py-3 text-sm">
                                @if ($branch->status)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Activo</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Inactivo</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-right">
                                <div class="inline-flex items-center gap-3">
                                    <a href="{{ route('branches.edit', $branch) }}"
                                       class="text-brand-600 hover:text-brand-800 font-medium">Editar</a>
                                    <form action="{{ route('branches.destroy', $branch) }}"
                                          method="POST"
                                          data-confirm="¿Seguro que quieres {{ $branch->status ? 'desactivar' : 'reactivar' }} esta rama?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="{{ $branch->status ? 'text-red-600 hover:text-red-800' : 'text-green-600 hover:text-green-800' }} font-medium">
                                            {{ $branch->status ? 'Desactivar' : 'Reactivar' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                No hay ramas de ingeniería registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($branches->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
                {{ $branches->links() }}
            </div>
        @endif
    </div>

    <p class="text-sm text-gray-500 mt-3">
        Mostrando {{ $branches->firstItem() ?? 0 }}–{{ $branches->lastItem() ?? 0 }}
        de {{ $branches->total() }} registros.
    </p>
@endsection