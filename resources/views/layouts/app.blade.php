<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIB - Carátulas')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-100">

    {{-- Barra superior --}}
    <header class="bg-white shadow fixed top-0 left-0 right-0 z-30 h-16">
        <div class="flex items-center justify-between h-full px-4">
            <div class="flex items-center gap-4">
                <button id="toggle-sidebar"
                        class="text-gray-600 hover:text-gray-900 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <h1 class="text-xl font-bold text-gray-800">SIB - Carátulas</h1>
            </div>

            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-600 hidden md:inline">
                    {{ auth()->user()->name }}
                </span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded text-sm">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </header>

    {{-- Sidebar --}}
    <aside id="sidebar"
           class="fixed top-16 left-0 bottom-0 w-64 bg-gray-900 text-gray-200 z-20
                  transform -translate-x-full md:translate-x-0 transition-transform duration-200 overflow-y-auto">
        <nav class="py-4">

            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 px-5 py-3 hover:bg-gray-800 {{ request()->routeIs('dashboard') ? 'bg-gray-800 border-l-4 border-blue-500' : '' }}">
                <span>🏠</span>
                <span>Dashboard</span>
            </a>

            <div class="mt-4 px-5 text-xs uppercase text-gray-500 font-semibold">Catálogos</div>

            

            <a href="{{ route('countries.index') }}"
            class="flex items-center gap-3 px-5 py-3 hover:bg-gray-800 {{ request()->routeIs('countries.*') ? 'bg-gray-800 border-l-4 border-blue-500' : '' }}">
                <span>🌎</span>
                <span>Países</span>
            </a>

            <a href="{{ route('standards.index') }}"
            class="flex items-center gap-3 px-5 py-3 hover:bg-gray-800 {{ request()->routeIs('standards.*') ? 'bg-gray-800 border-l-4 border-blue-500' : '' }}">
                <span>📏</span>
                <span>Normas</span>
            </a>

            <a href="{{ route('universities.index') }}"
            class="flex items-center gap-3 px-5 py-3 hover:bg-gray-800 {{ request()->routeIs('universities.*') ? 'bg-gray-800 border-l-4 border-blue-500' : '' }}">
                <span>🎓</span>
                <span>Universidades</span>
            </a>

            <a href="{{ route('branches.index') }}"
            class="flex items-center gap-3 px-5 py-3 hover:bg-gray-800 {{ request()->routeIs('branches.*') ? 'bg-gray-800 border-l-4 border-blue-500' : '' }}">
                <span>🏗️</span>
                <span>Ramas de ingeniería</span>
            </a>

            <a href="{{ route('specialties.index') }}"
            class="flex items-center gap-3 px-5 py-3 hover:bg-gray-800 {{ request()->routeIs('specialties.*') ? 'bg-gray-800 border-l-4 border-blue-500' : '' }}">
                <span>🔧</span>
                <span>Especialidades</span>
            </a>

            <a href="{{ route('engineers.index') }}"
            class="flex items-center gap-3 px-5 py-3 hover:bg-gray-800 {{ request()->routeIs('engineers.*') ? 'bg-gray-800 border-l-4 border-blue-500' : '' }}">
                <span>👷</span>
                <span>Ingenieros</span>
            </a>

            <div class="mt-4 px-5 text-xs uppercase text-gray-500 font-semibold">Categorías</div>

            <a href="{{ route('primary-categories.index') }}"
            class="flex items-center gap-3 px-5 py-3 hover:bg-gray-800 {{ request()->routeIs('primary-categories.*') ? 'bg-gray-800 border-l-4 border-blue-500' : '' }}">
                <span>📁</span>
                <span>Categorías primarias</span>
            </a>

            <a href="{{ route('secondary-categories.index') }}"
            class="flex items-center gap-3 px-5 py-3 hover:bg-gray-800 {{ request()->routeIs('secondary-categories.*') ? 'bg-gray-800 border-l-4 border-blue-500' : '' }}">
                <span>📂</span>
                <span>Categorías secundarias</span>
            </a>

            <a href="{{ route('tertiary-categories.index') }}"
            class="flex items-center gap-3 px-5 py-3 hover:bg-gray-800 {{ request()->routeIs('tertiary-categories.*') ? 'bg-gray-800 border-l-4 border-blue-500' : '' }}">
                <span>🗂️</span>
                <span>Categorías terciarias</span>
            </a>

            

        </nav>
    </aside>

    {{-- Contenido principal --}}
    <main class="pt-16 md:pl-64 min-h-screen">
        <div class="p-6">

            @if (session('success'))
                <div class="bg-green-100 text-green-800 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 text-red-800 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <script>
        const toggleBtn = document.getElementById('toggle-sidebar');
        const sidebar = document.getElementById('sidebar');
        toggleBtn?.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
        });
    </script>

</body>
</html>