{{-- Nombre --}}
<div class="mb-5">
    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
        Nombre <span class="text-red-500">*</span>
    </label>
    <input type="text" name="name" id="name"
           value="{{ old('name', $parameter->name ?? '') }}"
           maxlength="200" required autofocus
           placeholder="Ej: Potencia instalada"
           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500
                  @error('name') border-red-400 @else border-gray-300 @enderror">
    @error('name')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
    <p class="text-xs text-gray-500 mt-1">Mínimo 2, máximo 200 caracteres.</p>
</div>

{{-- Tipo, tipo de dato y unidad de medida --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-5">
    <div>
        <label for="parameter_type" class="block text-sm font-medium text-gray-700 mb-1">
            Tipo de parámetro <span class="text-red-500">*</span>
        </label>
        <select name="parameter_type" id="parameter_type" required
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500
                       @error('parameter_type') border-red-400 @else border-gray-300 @enderror">
            @foreach (\App\Models\Parameter::TYPE_LABELS as $value => $label)
                <option value="{{ $value }}"
                    {{ old('parameter_type', $parameter->parameter_type ?? \App\Models\Parameter::TYPE_PARAMETRICO) === $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('parameter_type')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
        <p class="text-xs text-gray-500 mt-1">
            Direccional: calle, dirección, zona o municipio (se llenan desde el mapa).
        </p>
    </div>

    <div>
        <label for="data_type" class="block text-sm font-medium text-gray-700 mb-1">
            Tipo de dato <span class="text-red-500">*</span>
        </label>
        <select name="data_type" id="data_type" required
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500
                       @error('data_type') border-red-400 @else border-gray-300 @enderror">
            @foreach (\App\Models\Parameter::DATA_TYPE_LABELS as $value => $label)
                <option value="{{ $value }}"
                    {{ old('data_type', $parameter->data_type ?? \App\Models\Parameter::DATA_TEXTO) === $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('data_type')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
        <p class="text-xs text-gray-500 mt-1">Lo que el usuario debe ingresar. Solo "Número" se usa en la cotización.</p>
    </div>

    <div>
        <label for="unit_of_measure" class="block text-sm font-medium text-gray-700 mb-1">
            Unidad de medida
        </label>
        <input type="text" name="unit_of_measure" id="unit_of_measure"
               value="{{ old('unit_of_measure', $parameter->unit_of_measure ?? '') }}"
               maxlength="20"
               placeholder="Ej: kW, m², V, A"
               class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500
                      @error('unit_of_measure') border-red-400 @else border-gray-300 @enderror">
        @error('unit_of_measure')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
        <p class="text-xs text-gray-500 mt-1">Opcional. Máximo 20 caracteres.</p>
    </div>
</div>

{{-- Nro. de decimales máximos (solo tipo Número) --}}
<div class="mb-5" id="max-decimals-field">
    <label for="max_decimals" class="block text-sm font-medium text-gray-700 mb-1">
        Nro. de decimales máximos
    </label>
    <input type="number" name="max_decimals" id="max_decimals"
           value="{{ old('max_decimals', $parameter->max_decimals ?? '') }}"
           min="0" step="1"
           placeholder="Ej: 2"
           class="w-full md:w-1/3 border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500
                  @error('max_decimals') border-red-400 @else border-gray-300 @enderror">
    @error('max_decimals')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
    <p class="text-xs text-gray-500 mt-1">
        Solo para tipo "Número". Cuántos decimales puede escribir el usuario (0 = solo enteros). Vacío = sin límite.
    </p>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const dataType = document.getElementById('data_type');
        const field = document.getElementById('max-decimals-field');
        if (!dataType || !field) return;

        function toggleMaxDecimals() {
            field.classList.toggle('hidden', dataType.value !== '{{ \App\Models\Parameter::DATA_NUMERO }}');
        }

        dataType.addEventListener('change', toggleMaxDecimals);
        toggleMaxDecimals();
    });
</script>

{{-- Descripción --}}
<div class="mb-5">
    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
    <textarea name="description" id="description" rows="3" maxlength="2000"
              placeholder="Descripción opcional..."
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500
                     @error('description') border-red-400 @else border-gray-300 @enderror">{{ old('description', $parameter->description ?? '') }}</textarea>
    @error('description')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

{{-- Estado --}}
<div class="mb-5">
    <input type="hidden" name="status" value="0">
    <label class="inline-flex items-center gap-2 cursor-pointer">
        <input type="checkbox" name="status" value="1"
               {{ old('status', $parameter->status ?? true) ? 'checked' : '' }}
               class="rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500">
        <span class="text-sm text-gray-700">Activo</span>
    </label>
    @error('status')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>
