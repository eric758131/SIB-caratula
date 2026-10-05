{{--
    Paginación del panel, en español y con los colores de la S.I.B.
    (La de Laravel viene en inglés y cambia a modo oscuro según el sistema.)
    El resumen "Mostrando X–Y de Z" ya lo muestra cada listado debajo de la tabla.
--}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Paginación" class="flex items-center justify-between gap-3">
        <span class="text-sm text-slate-500">
            Página <strong class="text-slate-700">{{ $paginator->currentPage() }}</strong> de {{ $paginator->lastPage() }}
        </span>

        <div class="flex items-center gap-1">
            {{-- Anterior --}}
            @if ($paginator->onFirstPage())
                <span class="rounded-lg px-3 py-1.5 text-sm text-slate-300" aria-disabled="true">‹ Anterior</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                   class="rounded-lg px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-brand-50 hover:text-brand-700">‹ Anterior</a>
            @endif

            {{-- Números (ocultos en pantallas chicas) --}}
            <span class="hidden items-center gap-1 sm:flex">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="px-2 text-sm text-slate-400">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="grid h-8 min-w-8 place-items-center rounded-lg bg-brand-600 px-2 text-sm font-semibold text-white">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" aria-label="Ir a la página {{ $page }}"
                                   class="grid h-8 min-w-8 place-items-center rounded-lg px-2 text-sm text-slate-600 hover:bg-brand-50 hover:text-brand-700">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </span>

            {{-- Siguiente --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                   class="rounded-lg px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-brand-50 hover:text-brand-700">Siguiente ›</a>
            @else
                <span class="rounded-lg px-3 py-1.5 text-sm text-slate-300" aria-disabled="true">Siguiente ›</span>
            @endif
        </div>
    </nav>
@endif
