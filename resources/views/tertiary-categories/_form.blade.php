{{-- Categoría secundaria --}}
<div class="mb-5">
    <label for="secondary_category_id" class="block text-sm font-medium text-gray-700 mb-1">
        Categoría secundaria <span class="text-red-500">*</span>
    </label>
    <select name="secondary_category_id" id="secondary_category_id" required
            class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500
                   @error('secondary_category_id') border-red-400 @else border-gray-300 @enderror">
        <option value="">— Selecciona una categoría —</option>
        @foreach ($secondaryCategories as $secondary)
            <option value="{{ $secondary->id }}"
                {{ (string) old('secondary_category_id', $tertiaryCategory->secondary_category_id ?? '') === (string) $secondary->id ? 'selected' : '' }}>
                {{ $secondary->primaryCategory?->name }} › {{ $secondary->name }}
            </option>
        @endforeach
    </select>
    @error('secondary_category_id')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

{{-- Código --}}
<div class="mb-5">
    <label for="code" class="block text-sm font-medium text-gray-700 mb-1">
        Código <span class="text-red-500">*</span>
    </label>
    <input type="text" name="code" id="code"
           value="{{ old('code', $tertiaryCategory->code ?? '') }}"
           maxlength="20" required
           placeholder="Ej: TER-001"
           class="w-full border rounded px-3 py-2 font-mono focus:outline-none focus:ring-2 focus:ring-brand-500
                  @error('code') border-red-400 @else border-gray-300 @enderror">
    @error('code')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
    <p class="text-xs text-gray-500 mt-1">
        Acepta cualquier carácter. Máx. 20 caracteres.
    </p>
</div>

{{-- Nombre --}}
<div class="mb-5">
    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
        Nombre <span class="text-red-500">*</span>
    </label>
    <input type="text" name="name" id="name"
           value="{{ old('name', $tertiaryCategory->name ?? '') }}"
           maxlength="200" required
           placeholder="Ej: Instalaciones monofásicas"
           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500
                  @error('name') border-red-400 @else border-gray-300 @enderror">
    @error('name')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

{{-- Descripción --}}
<div class="mb-5">
    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
    <textarea name="description" id="description" rows="3" maxlength="2000"
              placeholder="Descripción opcional..."
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500
                     @error('description') border-red-400 @else border-gray-300 @enderror">{{ old('description', $tertiaryCategory->description ?? '') }}</textarea>
    @error('description')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

{{-- Ejemplo --}}
<div class="mb-5">
    <label for="example" class="block text-sm font-medium text-gray-700 mb-1">Ejemplo</label>
    <textarea name="example" id="example" rows="3" maxlength="2000"
              placeholder="Ejemplo opcional..."
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500
                     @error('example') border-red-400 @else border-gray-300 @enderror">{{ old('example', $tertiaryCategory->example ?? '') }}</textarea>
    @error('example')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

{{-- Notas importantes --}}
<div class="mb-5">
    <label for="important_notes" class="block text-sm font-medium text-gray-700 mb-1">Notas importantes</label>
    <textarea name="important_notes" id="important_notes" rows="3" maxlength="2000"
              placeholder="Notas importantes opcionales..."
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500
                     @error('important_notes') border-red-400 @else border-gray-300 @enderror">{{ old('important_notes', $tertiaryCategory->important_notes ?? '') }}</textarea>
    @error('important_notes')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

{{-- ============================================
     SECCIÓN: Parámetros asociados
     ============================================ --}}
<h3 class="text-sm font-semibold text-gray-500 uppercase mb-3 mt-8">Parámetros asociados</h3>

<div class="mb-6">
    @if ($parameters->isEmpty())
        <p class="text-sm text-gray-500 italic">
            No hay parámetros activos registrados. Crea primero en el módulo de Parámetros.
        </p>
    @else
        @php
            $selectedParameters = old('parameters', isset($tertiaryCategory) ? $tertiaryCategory->parameters->pluck('id')->toArray() : []);
            $requiredSelected = old('required_parameters', $requiredParameters ?? []);
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
                       id="parameter-search"
                       placeholder="Buscar por nombre, unidad o tipo..."
                       class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>
            <div class="flex items-center justify-between mt-2 text-xs text-gray-500">
                <span id="parameter-counter">
                    {{ count($selectedParameters) }} seleccionado(s) de {{ $parameters->count() }}
                </span>
                <div class="flex gap-3">
                    <button type="button" id="parameter-select-all"
                            class="text-brand-600 hover:text-brand-800">Seleccionar todo</button>
                    <button type="button" id="parameter-clear-all"
                            class="text-red-600 hover:text-red-800">Limpiar</button>
                </div>
            </div>
        </div>

        {{-- Tabla de parámetros --}}
        <div class="border border-gray-200 rounded-lg overflow-hidden bg-gray-50">
            <div class="max-h-96 overflow-y-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100 sticky top-0">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase w-16">Usar</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Parámetro</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase w-32">Unidad</th>
                            <th class="px-4 py-2 text-center text-xs font-semibold text-gray-600 uppercase w-28">Obligatorio</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white" id="parameter-list">
                        @foreach ($parameters as $parameter)
                            @php
                                $isChecked = in_array($parameter->id, $selectedParameters);
                                $searchText = strtolower(
                                    $parameter->name . ' ' .
                                    ($parameter->unit_of_measure ?? '') . ' ' .
                                    ($parameter->parameter_type_label ?? '')
                                );
                            @endphp
                            <tr class="parameter-item hover:bg-gray-50" data-search="{{ $searchText }}">
                                <td class="px-4 py-2 text-center">
                                    <input type="checkbox"
                                           name="parameters[]"
                                           value="{{ $parameter->id }}"
                                           data-parameter-id="{{ $parameter->id }}"
                                           class="parameter-checkbox rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500"
                                           {{ $isChecked ? 'checked' : '' }}>
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-800">
                                    {{ $parameter->name }}
                                    @if ($parameter->parameter_type)
                                        <span class="text-xs text-gray-400">({{ $parameter->parameter_type_label }})</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-600">
                                    {{ $parameter->unit_of_measure ?: '—' }}
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <input type="checkbox"
                                           name="required_parameters[]"
                                           value="{{ $parameter->id }}"
                                           class="rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500"
                                           {{ in_array($parameter->id, $requiredSelected) ? 'checked' : '' }}>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div id="parameter-no-results" class="hidden px-4 py-6 text-center text-sm text-gray-500">
                    No se encontraron parámetros con ese criterio.
                </div>
            </div>
        </div>

        @error('parameters.*')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror

        <p class="text-xs text-gray-500 mt-2">
            Marca los parámetros que aplican a esta categoría. "Obligatorio" exige que el usuario lo llene en el asistente.
        </p>
    @endif
</div>

{{-- Script del buscador y toggles --}}
<script>

    function normalizeText(text) {
    if (!text) return '';

    return text
        .toString()
        .normalize('NFD')                      // descompone "á" en "a" + tilde
        .replace(/[\u0300-\u036f]/g, '')       // elimina los diacríticos
        .toLowerCase()                          // todo a minúsculas
        .replace(/[^\w\s]/g, ' ')               // quita símbolos raros (·, -, ., /, etc.)
        .replace(/\s+/g, ' ')                   // colapsa espacios múltiples
        .trim();
    }
    document.addEventListener('DOMContentLoaded', function () {
        /* ============================================================
         |  Buscador de parámetros
         * ============================================================ */
        const searchInput = document.getElementById('parameter-search');
        const items = document.querySelectorAll('.parameter-item');
        const noResults = document.getElementById('parameter-no-results');
        const counter = document.getElementById('parameter-counter');
        const checkboxes = document.querySelectorAll('.parameter-checkbox');
        const selectAllBtn = document.getElementById('parameter-select-all');
        const clearAllBtn = document.getElementById('parameter-clear-all');
        const totalCount = items.length;

        function updateCounter() {
            const checked = document.querySelectorAll('.parameter-checkbox:checked').length;
            if (counter) {
                counter.textContent = checked + ' seleccionado(s) de ' + totalCount;
            }
        }

        function filterItems() {
            const query = normalizeText(searchInput.value);

            // Si la búsqueda está vacía, muestra todo
            if (query === '') {
                items.forEach(function (item) {
                    item.classList.remove('hidden');
                });
                if (noResults) noResults.classList.add('hidden');
                return;
            }

            // Divide la búsqueda en palabras y exige que TODAS estén presentes
            const queryWords = query.split(' ').filter(Boolean);
            let visible = 0;

            items.forEach(function (item) {
                const text = normalizeText(item.dataset.search || '');

                const matches = queryWords.every(function (word) {
                    return text.includes(word);
                });

                if (matches) {
                    item.classList.remove('hidden');
                    visible++;
                } else {
                    item.classList.add('hidden');
                }
            });

            if (visible === 0) {
                noResults?.classList.remove('hidden');
            } else {
                noResults?.classList.add('hidden');
            }
        }

        if (searchInput) {
            searchInput.addEventListener('input', filterItems);
        }

        /* ============================================================
         |  Contador al marcar/desmarcar
         * ============================================================ */
        checkboxes.forEach(function (cb) {
            cb.addEventListener('change', updateCounter);
        });

        /* ============================================================
         |  Seleccionar todo / Limpiar
         * ============================================================ */
        if (selectAllBtn) {
            selectAllBtn.addEventListener('click', function () {
                items.forEach(function (item) {
                    if (!item.classList.contains('hidden')) {
                        const cb = item.querySelector('.parameter-checkbox');
                        if (cb) cb.checked = true;
                    }
                });
                updateCounter();
            });
        }

        if (clearAllBtn) {
            clearAllBtn.addEventListener('click', function () {
                checkboxes.forEach(function (cb) {
                    cb.checked = false;
                });
                updateCounter();
            });
        }

        updateCounter();
    });
</script>

{{-- Imágenes --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
    @foreach (['image_1' => 'Imagen 1', 'image_2' => 'Imagen 2', 'image_3' => 'Imagen 3'] as $field => $label)
        <div>
            <label for="{{ $field }}" class="block text-sm font-medium text-gray-700 mb-1">{{ $label }}</label>

            @if (!empty($tertiaryCategory->$field ?? null))
                <div class="mb-2 flex items-start gap-3">
                    <img src="{{ Storage::url($tertiaryCategory->$field) }}"
                         alt="{{ $label }}"
                         class="w-24 h-24 object-cover rounded border border-gray-200">

                    <label class="inline-flex items-center gap-2 cursor-pointer text-sm text-red-600 mt-2">
                        <input type="checkbox" name="remove_{{ $field }}" value="1"
                               class="rounded border-gray-300 text-red-600 shadow-sm focus:ring-red-500">
                        <span>Eliminar</span>
                    </label>
                </div>
            @endif

            <input type="file" name="{{ $field }}" id="{{ $field }}"
                   accept="image/jpeg,image/png,image/webp"
                   class="w-full border rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500
                          @error($field) border-red-400 @else border-gray-300 @enderror">
            @error($field)
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
            <p class="text-xs text-gray-500 mt-1">JPG, PNG o WEBP. Máx. 4 MB.</p>
        </div>
    @endforeach
</div>

{{-- Estado --}}
<div class="mb-5">
    <input type="hidden" name="status" value="0">
    <label class="inline-flex items-center gap-2 cursor-pointer">
        <input type="checkbox" name="status" value="1"
               {{ old('status', $tertiaryCategory->status ?? true) ? 'checked' : '' }}
               class="rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500">
        <span class="text-sm text-gray-700">Activo</span>
    </label>
    @error('status')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>