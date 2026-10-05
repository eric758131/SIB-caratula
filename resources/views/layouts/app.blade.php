<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel') · S.I.B. Carátulas</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @include('layouts.partials.tailwind')
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-800 antialiased">

@php
    /* Menú lateral: [texto, ruta, patrón activo, ícono] agrupado por sección */
    $nav = [
        '' => [
            ['Dashboard', 'dashboard', 'dashboard', '🏠'],
        ],
        'Catálogos' => [
            ['Países', 'countries.index', 'countries.*', '🌎'],
            ['Normas', 'standards.index', 'standards.*', '📏'],
            ['Universidades', 'universities.index', 'universities.*', '🎓'],
            ['Ramas de ingeniería', 'branches.index', 'branches.*', '🏗️'],
            ['Especialidades', 'specialties.index', 'specialties.*', '🔧'],
            ['Ingenieros', 'engineers.index', 'engineers.*', '👷'],
            ['Parámetros', 'parameters.index', 'parameters.*', '📊'],
            ['Propietarios', 'owners.index', 'owners.*', '👤'],
            ['Documentos requeridos', 'required-documents.index', 'required-documents.*', '📋'],
        ],
        'Categorías' => [
            ['Categorías primarias', 'primary-categories.index', 'primary-categories.*', '📁'],
            ['Categorías secundarias', 'secondary-categories.index', 'secondary-categories.*', '📂'],
            ['Categorías terciarias', 'tertiary-categories.index', 'tertiary-categories.*', '🗂️'],
        ],
    ];

    $userName = auth()->user()->name ?? 'Usuario';
    $initials = collect(preg_split('/\s+/', trim($userName)))
        ->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
@endphp

    {{-- ============ Barra superior ============ --}}
    <header class="fixed inset-x-0 top-0 z-30 h-16 border-b border-slate-200 bg-white/90 backdrop-blur">
        <div class="flex h-full items-center justify-between gap-4 px-4 md:pl-[19.5rem]">
            <div class="flex min-w-0 items-center gap-3">
                <button id="toggle-sidebar" type="button"
                        class="rounded-lg p-2 text-slate-600 hover:bg-slate-100 md:hidden"
                        aria-label="Abrir menú" aria-controls="sidebar" aria-expanded="false">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <h1 class="truncate text-lg font-semibold text-slate-800">@yield('title', 'Panel')</h1>
            </div>

            <div class="flex items-center gap-3">
                <div class="hidden text-right sm:block">
                    <div class="text-sm font-semibold leading-tight text-slate-800">{{ $userName }}</div>
                    <div class="text-xs leading-tight text-slate-500">Administrador</div>
                </div>
                <span class="grid h-9 w-9 place-items-center rounded-full bg-brand-600 text-sm font-bold text-white" aria-hidden="true">
                    {{ $initials }}
                </span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" data-loading="Saliendo…"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 hover:border-red-200 hover:bg-red-50 hover:text-red-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span class="hidden sm:inline">Cerrar sesión</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    {{-- ============ Menú lateral ============ --}}
    <div id="sidebar-backdrop" class="fixed inset-0 z-30 hidden bg-slate-900/50 md:hidden"></div>

    <aside id="sidebar"
           class="fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col bg-gradient-to-b from-brand-900 to-brand-950 text-brand-50 transition-transform duration-200 md:translate-x-0">
        <a href="{{ route('dashboard') }}" class="flex h-16 shrink-0 items-center gap-3 border-b border-white/10 px-5">
            <span class="grid h-10 w-10 place-items-center rounded-xl bg-white text-sm font-black text-brand-800 shadow">SIB</span>
            <span class="leading-tight">
                <span class="block text-base font-bold text-white">S.I.B. Carátulas</span>
                <span class="block text-xs text-brand-200">Departamental La Paz</span>
            </span>
        </a>

        <nav class="flex-1 overflow-y-auto px-3 py-4" aria-label="Menú principal">
            @foreach ($nav as $group => $items)
                @if ($group !== '')
                    <div class="mb-1 mt-5 px-3 text-[11px] font-semibold uppercase tracking-wider text-brand-300/80">{{ $group }}</div>
                @endif
                @foreach ($items as [$label, $route, $pattern, $icon])
                    @php($active = request()->routeIs($pattern))
                    <a href="{{ route($route) }}"
                       @if ($active) aria-current="page" @endif
                       class="group mb-0.5 flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition
                              {{ $active ? 'bg-white/15 font-semibold text-white shadow-inner' : 'text-brand-100/85 hover:bg-white/10 hover:text-white' }}">
                        <span class="grid h-7 w-7 place-items-center rounded-md text-base {{ $active ? 'bg-white/20' : 'bg-white/5 group-hover:bg-white/10' }}" aria-hidden="true">{{ $icon }}</span>
                        <span class="truncate">{{ $label }}</span>
                        @if ($active)
                            <span class="ml-auto h-1.5 w-1.5 rounded-full bg-brand-300"></span>
                        @endif
                    </a>
                @endforeach
            @endforeach
        </nav>

        <div class="border-t border-white/10 px-5 py-3 text-xs text-brand-200/80">
            © {{ date('Y') }} Sociedad de Ingenieros de Bolivia
        </div>
    </aside>

    {{-- ============ Contenido ============ --}}
    <main class="min-h-screen pt-16 md:pl-72">
        <div class="mx-auto max-w-7xl p-4 sm:p-6 lg:p-8">

            @if (session('success'))
                <div class="flash mb-5 flex items-start gap-3 rounded-xl border border-brand-200 bg-brand-50 px-4 py-3 text-brand-800 shadow-sm" role="status" data-autohide>
                    <span aria-hidden="true">✅</span>
                    <p class="flex-1 text-sm font-medium">{{ session('success') }}</p>
                    <button type="button" class="flash-close -m-1 rounded p-1 opacity-60 hover:opacity-100" aria-label="Cerrar aviso">✕</button>
                </div>
            @endif

            @if (session('error'))
                <div class="flash mb-5 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800 shadow-sm" role="alert">
                    <span aria-hidden="true">⛔</span>
                    <p class="flex-1 text-sm font-medium">{{ session('error') }}</p>
                    <button type="button" class="flash-close -m-1 rounded p-1 opacity-60 hover:opacity-100" aria-label="Cerrar aviso">✕</button>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-900 shadow-sm" role="alert">
                    <span aria-hidden="true">⚠️</span>
                    <p class="text-sm font-medium">
                        Revisa los campos marcados en rojo{{ $errors->count() > 1 ? ' (' . $errors->count() . ' errores)' : '' }}.
                    </p>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    {{-- ============ Cuadro de confirmación (reemplaza a confirm()) ============ --}}
    <div id="confirm-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4" role="dialog" aria-modal="true" aria-labelledby="confirm-title">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
            <div class="flex items-start gap-4">
                <span id="confirm-icon" class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-amber-100 text-xl" aria-hidden="true">⚠️</span>
                <div>
                    <h2 id="confirm-title" class="text-lg font-semibold text-slate-800">¿Confirmar acción?</h2>
                    <p id="confirm-message" class="mt-1 text-sm text-slate-600"></p>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" id="confirm-cancel" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
                    Cancelar
                </button>
                <button type="button" id="confirm-accept" class="rounded-lg px-4 py-2 text-sm font-semibold text-white shadow-sm">
                    Confirmar
                </button>
            </div>
        </div>
    </div>

    <script>
    (() => {
        /* ---------- Menú lateral en móvil ---------- */
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');
        const toggle = document.getElementById('toggle-sidebar');
        const setSidebar = (open) => {
            sidebar.classList.toggle('-translate-x-full', !open);
            backdrop.classList.toggle('hidden', !open);
            toggle?.setAttribute('aria-expanded', String(open));
        };
        toggle?.addEventListener('click', () => setSidebar(sidebar.classList.contains('-translate-x-full')));
        backdrop.addEventListener('click', () => setSidebar(false));

        /* ---------- Avisos: se pueden cerrar y los de éxito se van solos ---------- */
        document.querySelectorAll('.flash').forEach((el) => {
            el.querySelector('.flash-close')?.addEventListener('click', () => el.remove());
            if (el.hasAttribute('data-autohide')) setTimeout(() => el.remove(), 6000);
        });

        /* ---------- Confirmación: <form data-confirm="¿Seguro...?"> ---------- */
        const modal = document.getElementById('confirm-modal');
        const accept = document.getElementById('confirm-accept');
        let pendingForm = null;

        const closeModal = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            pendingForm = null;
        };

        document.getElementById('confirm-cancel').addEventListener('click', closeModal);
        modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && pendingForm) closeModal(); });

        accept.addEventListener('click', () => {
            const form = pendingForm;
            closeModal();
            if (!form) return;
            form.dataset.confirmed = '1';
            form.requestSubmit();
        });

        /* ---------- Guardar una sola vez ---------- */
        const spinner = '<svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"/><path fill="currentColor" class="opacity-75" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>';

        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (!(form instanceof HTMLFormElement) || e.defaultPrevented) return;

            // 1) Pedir confirmación si el formulario la necesita
            if (form.dataset.confirm && form.dataset.confirmed !== '1') {
                e.preventDefault();
                const submitBtn = form.querySelector('button[type="submit"], button:not([type])');
                const action = submitBtn?.textContent.trim() || 'Confirmar';
                const danger = /desactivar|eliminar/i.test(action);
                document.getElementById('confirm-message').textContent = form.dataset.confirm;
                document.getElementById('confirm-icon').textContent = danger ? '⚠️' : '♻️';
                accept.textContent = action;
                accept.className = 'rounded-lg px-4 py-2 text-sm font-semibold text-white shadow-sm ' +
                    (danger ? 'bg-red-600 hover:bg-red-700' : 'bg-brand-600 hover:bg-brand-700');
                pendingForm = form;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                accept.focus();
                return;
            }

            // 2) Los filtros (GET) no guardan nada
            if ((form.getAttribute('method') || 'get').toLowerCase() === 'get') return;

            // 3) Evitar que un doble clic envíe el formulario dos veces (y duplique el registro)
            if (form.dataset.submitting === '1') {
                e.preventDefault();
                return;
            }
            form.dataset.submitting = '1';

            const isDelete = !!form.querySelector('input[name="_method"][value="DELETE"]');
            form.querySelectorAll('button[type="submit"], button:not([type])').forEach((btn) => {
                btn.dataset.original = btn.innerHTML;
                btn.disabled = true;
                btn.classList.add('opacity-75', 'cursor-wait');
                btn.innerHTML = `<span class="inline-flex items-center gap-2">${spinner}${btn.dataset.loading || (isDelete ? 'Procesando…' : 'Guardando…')}</span>`;
            });
        });

        // Al volver con "Atrás" el navegador puede mostrar la página congelada: reactivar botones
        window.addEventListener('pageshow', (e) => {
            if (!e.persisted) return;
            document.querySelectorAll('form[data-submitting="1"]').forEach((form) => {
                delete form.dataset.submitting;
                delete form.dataset.confirmed;
                form.querySelectorAll('button[data-original]').forEach((btn) => {
                    btn.innerHTML = btn.dataset.original;
                    btn.disabled = false;
                    btn.classList.remove('opacity-75', 'cursor-wait');
                });
            });
        });
    })();
    </script>

    @stack('scripts')
</body>
</html>
