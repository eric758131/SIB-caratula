<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión · S.I.B. Carátulas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @include('layouts.partials.tailwind')
</head>
<body class="min-h-screen bg-gradient-to-br from-brand-700 via-brand-800 to-brand-950 font-sans antialiased flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        {{-- Logo / Título --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-white shadow-lg mb-4">
                <span class="text-lg font-black text-brand-800">SIB</span>
            </div>
            <h1 class="text-3xl font-bold text-white">S.I.B. Carátulas</h1>
            <p class="text-brand-100 mt-2 text-sm">Sociedad de Ingenieros de Bolivia · Departamental La Paz</p>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-2xl p-8">

            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-800">Iniciar sesión</h2>
                <p class="text-sm text-gray-500 mt-1">Ingresa tus credenciales para continuar</p>
            </div>

            {{-- Mensaje de error general --}}
            @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded mb-5 flex items-start gap-2">
                    <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-sm">{{ $errors->first() }}</span>
                </div>
            @endif

            {{-- Formulario --}}
            <form method="POST" action="{{ route('login.post') }}" class="space-y-5" id="login-form">
                @csrf

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Correo electrónico
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                            </svg>
                        </div>
                        <input type="email"
                               name="email"
                               id="email"
                               value="{{ old('email') }}"
                               required
                               autofocus
                               autocomplete="email"
                               placeholder="tu@correo.com"
                               class="w-full pl-10 pr-3 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition
                                      @error('email') border-red-400 @else border-gray-300 @enderror">
                    </div>
                    @error('email')
                        <p class="text-red-600 text-sm mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Contraseña --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Contraseña
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input type="password"
                               name="password"
                               id="password"
                               required
                               autocomplete="current-password"
                               placeholder="••••••••"
                               class="w-full pl-10 pr-10 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition
                                      @error('password') border-red-400 @else border-gray-300 @enderror">
                        <button type="button"
                                id="toggle-password"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                            <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-red-600 text-sm mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Recordarme --}}
                <div class="flex items-center justify-between">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" id="remember"
                               class="rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500">
                        <span class="text-sm text-gray-600">Recordarme</span>
                    </label>
                </div>

                {{-- Botón --}}
                <button type="submit"
                        class="w-full bg-brand-600 hover:bg-brand-700 active:bg-brand-800 text-white py-2.5 rounded-lg font-medium shadow-sm transition flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    Entrar
                </button>
            </form>

        </div>

        {{-- Footer --}}
        <p class="text-center text-xs text-brand-100 mt-6">
            © {{ date('Y') }} SIB - Todos los derechos reservados
        </p>
    </div>

    <script>
        // Mostrar/ocultar contraseña
        document.getElementById('toggle-password')?.addEventListener('click', function () {
            const input = document.getElementById('password');
            input.type = input.type === 'password' ? 'text' : 'password';
        });

        // Un solo envío aunque se presione "Entrar" varias veces
        document.getElementById('login-form')?.addEventListener('submit', function (e) {
            if (this.dataset.submitting === '1') { e.preventDefault(); return; }
            this.dataset.submitting = '1';
            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.classList.add('opacity-75', 'cursor-wait');
            btn.lastChild.textContent = ' Entrando…';
        });
        window.addEventListener('pageshow', function (e) {
            if (!e.persisted) return;
            const form = document.getElementById('login-form');
            const btn = form?.querySelector('button[type="submit"]');
            if (!btn) return;
            delete form.dataset.submitting;
            btn.disabled = false;
            btn.classList.remove('opacity-75', 'cursor-wait');
            btn.lastChild.textContent = ' Entrar';
        });
    </script>

</body>
</html>