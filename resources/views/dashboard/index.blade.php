@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Hola, {{ auth()->user()->name }} 👋</h2>
        <p class="mt-1 text-sm text-slate-500">Resumen del sistema de carátulas de la S.I.B. Departamental La Paz.</p>
    </div>

    {{-- Trámites --}}
    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-3">
        @foreach ([
            ['Trámites registrados', $procedures['total'], 'bg-slate-800 text-white', 'text-slate-300'],
            ['Enviados por los usuarios', $procedures['submitted'], 'bg-brand-600 text-white', 'text-brand-100'],
            ['Borradores sin enviar', $procedures['pending'], 'bg-white text-slate-800 ring-1 ring-slate-200', 'text-slate-500'],
        ] as [$label, $value, $classes, $muted])
            <div class="rounded-2xl p-5 shadow-sm {{ $classes }}">
                <div class="text-sm {{ $muted }}">{{ $label }}</div>
                <div class="mt-1 text-3xl font-extrabold">{{ number_format($value) }}</div>
            </div>
        @endforeach
    </div>

    {{-- Módulos --}}
    @foreach ($cards as $group => $items)
        <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $group }}</h3>
        <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($items as $card)
                <a href="{{ route($card['route']) }}"
                   class="group flex items-center gap-4 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200/70 transition hover:-translate-y-0.5 hover:shadow-md hover:ring-brand-300">
                    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-brand-50 text-2xl group-hover:bg-brand-100" aria-hidden="true">{{ $card['icon'] }}</span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate font-semibold text-slate-800">{{ $card['title'] }}</span>
                        <span class="block text-sm text-slate-500">
                            {{ number_format($card['active']) }} activos
                            @if ($card['total'] !== $card['active'])
                                · {{ number_format($card['total'] - $card['active']) }} inactivos
                            @endif
                        </span>
                    </span>
                    <span class="text-2xl font-extrabold text-slate-800">{{ number_format($card['total']) }}</span>
                </a>
            @endforeach
        </div>
    @endforeach

    {{-- Últimos trámites enviados --}}
    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/70">
        <div class="border-b border-slate-100 px-5 py-4">
            <h3 class="font-semibold text-slate-800">Últimos trámites enviados</h3>
        </div>
        @forelse ($recentProcedures as $procedure)
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 border-b border-slate-100 px-5 py-3 last:border-0">
                <span class="font-mono text-sm font-semibold text-brand-700">{{ $procedure->number }}</span>
                <span class="min-w-0 flex-1 truncate text-sm text-slate-700">{{ $procedure->title }}</span>
                <span class="text-xs text-slate-500">{{ $procedure->primaryCategory?->name }}</span>
                <span class="rounded-full bg-brand-50 px-2 py-0.5 text-xs font-medium capitalize text-brand-700">
                    {{ str_replace('_', ' ', $procedure->status) }}
                </span>
            </div>
        @empty
            <p class="px-5 py-8 text-center text-sm text-slate-500">Todavía no se envió ningún trámite.</p>
        @endforelse
    </div>
@endsection
