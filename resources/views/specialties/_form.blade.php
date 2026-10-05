{{-- Nombre --}}
<div class="mb-5">
    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
        Nombre <span class="text-red-500">*</span>
    </label>
    <input type="text" name="name" id="name"
           value="{{ old('name', $specialty->name ?? '') }}"
           maxlength="200" required autofocus
           placeholder="Ej: Alta Tensión"
           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500
                  @error('name') border-red-400 @else border-gray-300 @enderror">
    @error('name')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
    <p class="text-xs text-gray-500 mt-1">
        Solo letras, espacios, puntos, guiones, paréntesis y barras. Mínimo 3, máximo 200 caracteres.
    </p>
</div>

{{-- Estado --}}
<div class="mb-5">
    <input type="hidden" name="status" value="0">
    <label class="inline-flex items-center gap-2 cursor-pointer">
        <input type="checkbox" name="status" value="1"
               {{ old('status', $specialty->status ?? true) ? 'checked' : '' }}
               class="rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500">
        <span class="text-sm text-gray-700">Activo</span>
    </label>
    @error('status')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

{{-- ============================================
     SECCIÓN: Categorías terciarias asociadas
     ============================================ --}}
<h3 class="text-sm font-semibold text-gray-500 uppercase mb-3 mt-8">Categorías terciarias asociadas</h3>

<div class="mb-6">
    @if ($tertiaryCategories->isEmpty())
        <p class="text-sm text-gray-500 italic">
            No hay categorías terciarias activas. Crea primero en el módulo de Categorías terciarias.
        </p>
    @else
        @php
            $selectedIds = old('tertiary_categories', $assignedTertiaryIds ?? []);
        @endphp

        {{-- Buscador --}}
        <div class="mb-3">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text"
                       id="tertiary-search"
                       placeholder="Buscar por nombre, código o jerarquía..."
                       class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>
            <div class="flex items-center justify-between mt-2 text-xs text-gray-500">
                <span id="tertiary-counter">
                    {{ count($selectedIds) }} seleccionada(s) de {{ $tertiaryCategories->count() }}
                </span>
                <div class="flex gap-3">
                    <button type="button" id="tertiary-select-all"
                            class="text-brand-600 hover:text-brand-800">Seleccionar todo</button>
                    <button type="button" id="tertiary-clear-all"
                            class="text-red-600 hover:text-red-800">Limpiar</button>
                </div>
            </div>
        </div>

        {{-- Lista de categorías terciarias --}}
        <div id="tertiary-list"
             class="border border-gray-200 rounded-lg max-h-80 overflow-y-auto bg-gray-50">
            @foreach ($tertiaryCategories as $tertiary)
                @php
                    $searchText = strtolower(
                        $tertiary->code . ' ' .
                        $tertiary->name . ' ' .
                        ($tertiary->secondaryCategory?->name ?? '') . ' ' .
                        ($tertiary->secondaryCategory?->primaryCategory?->name ?? '')
                    );
                @endphp
                <label class="tertiary-item flex items-start gap-3 px-4 py-2.5 border-b border-gray-100 last:border-b-0 hover:bg-white cursor-pointer"
                       data-search="{{ $searchText }}">
                    <input type="checkbox"
                           name="tertiary_categories[]"
                           value="{{ $tertiary->id }}"
                           class="tertiary-checkbox mt-1 rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500"
                           {{ in_array($tertiary->id, $selectedIds) ? 'checked' : '' }}>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs font-mono bg-gray-200 text-gray-700 px-2 py-0.5 rounded">
                                {{ $tertiary->code }}
                            </span>
                            <span class="text-sm font-medium text-gray-800">
                                {{ $tertiary->name }}
                            </span>
                            @if (!$tertiary->status)
                                <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded">
                                    Inactiva
                                </span>
                            @endif
                        </div>
                        <div class="text-xs text-gray-500 mt-0.5">
                            {{ $tertiary->secondaryCategory?->primaryCategory?->name ?? '—' }}
                            › {{ $tertiary->secondaryCategory?->name ?? '—' }}
                        </div>
                    </div>
                </label>
            @endforeach

            <div id="tertiary-no-results" class="hidden px-4 py-6 text-center text-sm text-gray-500">
                No se encontraron categorías terciarias con ese criterio.
            </div>
        </div>

        @error('tertiary_categories.*')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror

        <p class="text-xs text-gray-500 mt-2">
            Marca las categorías terciarias que esta especialidad puede atender.
        </p>
    @endif
</div>

{{-- Script del buscador --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('tertiary-search');
        const items = document.querySelectorAll('.tertiary-item');
        const noResults = document.getElementById('tertiary-no-results');
        const counter = document.getElementById('tertiary-counter');
        const checkboxes = document.querySelectorAll('.tertiary-checkbox');
        const selectAllBtn = document.getElementById('tertiary-select-all');
        const clearAllBtn = document.getElementById('tertiary-clear-all');
        const totalCount = items.length;

        function updateCounter() {
            const checked = document.querySelectorAll('.tertiary-checkbox:checked').length;
            counter.textContent = checked + ' seleccionada(s) de ' + totalCount;
        }

        // Sin tildes ni mayúsculas: "electrica" encuentra "Eléctrica"
        function normalizeText(text) {
            return (text || '').toString().normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().replace(/\s+/g, ' ').trim();
        }

        function filterItems() {
            // Cada palabra escrita debe aparecer (en cualquier orden)
            const words = normalizeText(searchInput.value).split(' ').filter(Boolean);
            let visible = 0;

            items.forEach(function (item) {
                const text = normalizeText(item.dataset.search);
                if (!words.length || words.every(function (w) { return text.includes(w); })) {
                    item.classList.remove('hidden');
                    visible++;
                } else {
                    item.classList.add('hidden');
                }
            });

            if (visible === 0) {
                noResults.classList.remove('hidden');
            } else {
                noResults.classList.add('hidden');
            }
        }

        searchInput.addEventListener('input', filterItems);

        checkboxes.forEach(function (cb) {
            cb.addEventListener('change', updateCounter);
        });

        selectAllBtn.addEventListener('click', function () {
            items.forEach(function (item) {
                if (!item.classList.contains('hidden')) {
                    const cb = item.querySelector('.tertiary-checkbox');
                    if (cb) cb.checked = true;
                }
            });
            updateCounter();
        });

        clearAllBtn.addEventListener('click', function () {
            checkboxes.forEach(function (cb) {
                cb.checked = false;
            });
            updateCounter();
        });

        updateCounter();
    });
</script>