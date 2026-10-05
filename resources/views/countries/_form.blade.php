{{-- Nombre --}}
<div class="mb-5">
    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
        Nombre <span class="text-red-500">*</span>
    </label>
    <input type="text"
           name="name"
           id="name"
           value="{{ old('name', $country->name ?? '') }}"
           maxlength="100"
           required
           autofocus
           placeholder="Ej: Bolivia"
           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500
                  @error('name') border-red-400 @else border-gray-300 @enderror">
    @error('name')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
    <p class="text-xs text-gray-500 mt-1">
        Solo letras, espacios, puntos, guiones y paréntesis. Mínimo 3, máximo 100 caracteres.
    </p>
</div>

{{-- Estado --}}
<div class="mb-5">
    <input type="hidden" name="status" value="0">

    <label class="inline-flex items-center gap-2 cursor-pointer">
        <input type="checkbox"
               name="status"
               value="1"
               {{ old('status', $country->status ?? true) ? 'checked' : '' }}
               class="rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500">
        <span class="text-sm text-gray-700">Activo</span>
    </label>
    @error('status')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>