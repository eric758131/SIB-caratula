@extends('layouts.app')

@section('title', 'Ingenieros')

@section('content')
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Ingenieros</h2>
        <a href="{{ route('engineers.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow-sm">
            <span class="text-lg leading-none">+</span>
            <span>Nuevo ingeniero</span>
        </a>
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('engineers.index') }}"
          class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div class="md:col-span-2">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                <input type="text" name="search" id="search" value="{{ $search }}"
                       placeholder="Nombre, apellido, RNI, CI, email..."
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-1">Rama</label>
                <select name="branch_id" id="branch_id"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todas</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" {{ (string) $branchId === (string) $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="university_id" class="block text-sm font-medium text-gray-700 mb-1">Universidad</label>
                <select name="university_id" id="university_id"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todas</option>
                    @foreach ($universities as $university)
                        <option value="{{ $university->id }}" {{ (string) $universityId === (string) $university->id ? 'selected' : '' }}>
                            {{ $university->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="sib_departmental" class="block text-sm font-medium text-gray-700 mb-1">Departamental</label>
                <select name="sib_departmental" id="sib_departmental"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todas</option>
                    @foreach (\App\Models\Engineer::SIB_DEPARTAMENTAL_LABELS as $value => $label)
                        <option value="{{ $value }}" {{ $departmental === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                <select name="status" id="status"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos</option>
                    @foreach (\App\Models\Engineer::STATUS_LABELS as $value => $label)
                        <option value="{{ $value }}" {{ $status === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex items-center gap-3 mt-4">
            <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded">
                Filtrar
            </button>
            @if ($search !== '' || $branchId !== '' || $universityId !== '' || $departmental !== '' || $status !== '')
                <a href="{{ route('engineers.index') }}" class="text-gray-500 hover:text-gray-700">
                    Limpiar filtros
                </a>
            @endif
        </div>
    </form>

    {{-- Tabla --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-16">#</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-20">Foto</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Ingeniero</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-32">RNI</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-32">CI</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Rama / Universidad</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-40">Departamental</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-40">Estado</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider w-56">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($engineers as $engineer)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $engineer->id }}</td>
                            <td class="px-4 py-3">
                                @if ($engineer->image)
                                    <img src="{{ Storage::url($engineer->image) }}"
                                         alt="{{ $engineer->full_name }}"
                                         class="w-12 h-12 object-cover rounded-full border border-gray-200">
                                @else
                                    <div class="w-12 h-12 bg-gray-100 rounded-full border border-gray-200 flex items-center justify-center text-gray-400 text-xs">
                                        {{ strtoupper(substr($engineer->name, 0, 1) . substr($engineer->father_last_name, 0, 1)) }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-800">
                                <div class="font-medium">{{ $engineer->full_name }}</div>
                                @if ($engineer->email)
                                    <div class="text-xs text-gray-500 mt-0.5">{{ $engineer->email }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm font-mono text-gray-700">{{ $engineer->rni }}</td>
                            <td class="px-4 py-3 text-sm font-mono text-gray-700">{{ $engineer->ci }}</td>
                            <td class="px-4 py-3 text-xs text-gray-600">
                                <div class="flex flex-col gap-0.5">
                                    <span class="font-medium text-gray-800">{{ $engineer->branch?->name ?? '—' }}</span>
                                    <span class="text-gray-400">{{ $engineer->university?->name ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-600">
                                {{ $engineer->sib_departmental_label }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @php
                                    $statusColor = match($engineer->status) {
                                        \App\Models\Engineer::STATUS_ACTIVO => 'bg-green-100 text-green-800',
                                        \App\Models\Engineer::STATUS_INACTIVO => 'bg-gray-200 text-gray-800',
                                        \App\Models\Engineer::STATUS_SUSPENSION_INDEFINIDA,
                                        \App\Models\Engineer::STATUS_SUSPENSION_DEFINIDA => 'bg-yellow-100 text-yellow-800',
                                        \App\Models\Engineer::STATUS_EMERITO => 'bg-blue-100 text-blue-800',
                                        \App\Models\Engineer::STATUS_FALLECIDO => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $statusColor }}">
                                    {{ $engineer->status_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-right">
                                <div class="inline-flex items-center gap-3">
                                    <a href="{{ route('engineers.edit', $engineer) }}"
                                       class="text-blue-600 hover:text-blue-800 font-medium">Editar</a>
                                    @php
                                        $canDeactivate = in_array($engineer->status, [
                                            \App\Models\Engineer::STATUS_ACTIVO,
                                            \App\Models\Engineer::STATUS_EMERITO,
                                        ], true);
                                    @endphp

                                    <form action="{{ route('engineers.destroy', $engineer) }}"
                                        method="POST"
                                        onsubmit="return confirm('¿Seguro que quieres {{ $canDeactivate ? 'desactivar' : 'activar' }} este ingeniero?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="{{ $canDeactivate ? 'text-red-600 hover:text-red-800' : 'text-green-600 hover:text-green-800' }} font-medium">
                                            {{ $canDeactivate ? 'Desactivar' : 'Activar' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                                No hay ingenieros registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($engineers->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
                {{ $engineers->links() }}
            </div>
        @endif
    </div>

    <p class="text-sm text-gray-500 mt-3">
        Mostrando {{ $engineers->firstItem() ?? 0 }}–{{ $engineers->lastItem() ?? 0 }}
        de {{ $engineers->total() }} registros.
    </p>
@endsection