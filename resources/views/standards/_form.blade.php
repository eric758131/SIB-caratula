{{-- País --}}
<div class="mb-5">
    <label for="country_id" class="block text-sm font-medium text-gray-700 mb-1">
        País <span class="text-red-500">*</span>
    </label>
    <select name="country_id" id="country_id" required
            class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500
                   @error('country_id') border-red-400 @else border-gray-300 @enderror">
        <option value="">— Selecciona un país —</option>
        @foreach ($countries as $country)
            <option value="{{ $country->id }}"
                {{ (string) old('country_id', $standard->country_id ?? '') === (string) $country->id ? 'selected' : '' }}>
                {{ $country->name }}
            </option>
        @endforeach
    </select>
    @error('country_id')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

{{-- Nombre --}}
<div class="mb-5">
    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
        Nombre <span class="text-red-500">*</span>
    </label>
    <input type="text"
           name="name"
           id="name"
           value="{{ old('name', $standard->name ?? '') }}"
           maxlength="100"
           required
           placeholder="Ej: NB 777 - Instalaciones eléctricas"
           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500
                  @error('name') border-red-400 @else border-gray-300 @enderror">
    @error('name')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
    <p class="text-xs text-gray-500 mt-1">
        Solo letras, números, espacios, puntos, guiones, paréntesis y barras. Mínimo 3, máximo 100 caracteres.
    </p>
</div>

{{-- Descripción --}}
<div class="mb-5">
    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
        Descripción
    </label>
    <textarea name="description"
              id="description"
              rows="4"
              maxlength="2000"
              placeholder="Descripción opcional de la norma..."
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500
                     @error('description') border-red-400 @else border-gray-300 @enderror">{{ old('description', $standard->description ?? '') }}</textarea>
    @error('description')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
    <p class="text-xs text-gray-500 mt-1">Máximo 2000 caracteres.</p>
</div>

{{-- Fecha de vigencia --}}
<div class="mb-5">
    <label for="effective_date" class="block text-sm font-medium text-gray-700 mb-1">
        Fecha de vigencia
    </label>
    <input type="date"
           name="effective_date"
           id="effective_date"
           value="{{ old('effective_date', isset($standard) && $standard->effective_date ? $standard->effective_date->format('Y-m-d') : '') }}"
           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500
                  @error('effective_date') border-red-400 @else border-gray-300 @enderror">
    @error('effective_date')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
    <p class="text-xs text-gray-500 mt-1">Opcional. Puede ser una fecha futura.</p>
</div>

{{-- Estado --}}
<div class="mb-5">
    <input type="hidden" name="status" value="0">

    <label class="inline-flex items-center gap-2 cursor-pointer">
        <input type="checkbox"
               name="status"
               value="1"
               {{ old('status', $standard->status ?? true) ? 'checked' : '' }}
               class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
        <span class="text-sm text-gray-700">Activo</span>
    </label>
    @error('status')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>