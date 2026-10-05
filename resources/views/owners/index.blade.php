@extends('layouts.app')

@section('title', 'Propietarios')

@section('content')
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Propietarios</h2>
        <a href="{{ route('owners.create') }}"
           class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white px-4 py-2 rounded shadow-sm">
            <span class="text-lg leading-none">+</span>
            <span>Nuevo propietario</span>
        </a>
    </div>

    <form method="GET" action="{{ route('owners.index') }}"
          class="bg-white rounded-lg shadow p-4 mb-6">
        <x-keep-sort />
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                <input type="text" name="search" id="search" value="{{ $search }}"
                       placeholder="Nombre, teléfono o ID..."
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
                    <a href="{{ route('owners.index') }}" class="text-gray-500 hover:text-gray-700 px-3 py-2">Limpiar</a>
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
                        <x-sort-th column="phone" class="w-48">Teléfono</x-sort-th>
                        <x-sort-th column="procedures" align="center" class="w-40">Trámites</x-sort-th>
                        <x-sort-th column="status" class="w-32">Estado</x-sort-th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider w-56">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($owners as $owner)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $owner->id }}</td>
                            <td class="px-4 py-3 text-sm text-gray-800 font-medium">{{ $owner->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 font-mono">
                                {{ $owner->phone ?: '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                    {{ $owner->procedure_owners_count }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if ($owner->status)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Activo</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Inactivo</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-right">
                                <div class="inline-flex items-center gap-3">
                                    <a href="{{ route('owners.edit', $owner) }}"
                                       class="text-brand-600 hover:text-brand-800 font-medium">Editar</a>
                                    <form action="{{ route('owners.destroy', $owner) }}"
                                          method="POST"
                                          data-confirm="¿Seguro que quieres {{ $owner->status ? 'desactivar' : 'reactivar' }} este propietario?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="{{ $owner->status ? 'text-red-600 hover:text-red-800' : 'text-green-600 hover:text-green-800' }} font-medium">
                                            {{ $owner->status ? 'Desactivar' : 'Reactivar' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                No hay propietarios registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($owners->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
                {{ $owners->links() }}
            </div>
        @endif
    </div>

    <p class="text-sm text-gray-500 mt-3">
        Mostrando {{ $owners->firstItem() ?? 0 }}–{{ $owners->lastItem() ?? 0 }}
        de {{ $owners->total() }} registros.
    </p>
@endsection