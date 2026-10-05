{{-- Nombre --}}
<div class="mb-5">
    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
        Nombre completo <span class="text-red-500">*</span>
    </label>
    <input type="text" name="name" id="name"
           value="{{ old('name', $owner->name ?? '') }}"
           maxlength="200" required autofocus
           placeholder="Ej: Juan Pérez García"
           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500
                  @error('name') border-red-400 @else border-gray-300 @enderror">
    @error('name')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
    <p class="text-xs text-gray-500 mt-1">
        Puede ser una persona o una empresa. Solo letras, espacios, apóstrofes, guiones, puntos y el símbolo &.
    </p>
</div>

{{-- Teléfono --}}
<div class="mb-5">
    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
    <input type="text" name="phone" id="phone"
           value="{{ old('phone', $owner->phone ?? '') }}"
           maxlength="30"
           placeholder="Ej: +591 71234567"
           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500
                  @error('phone') border-red-400 @else border-gray-300 @enderror">
    @error('phone')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
    <p class="text-xs text-gray-500 mt-1">Opcional. Solo números, +, -, espacios y paréntesis.</p>
</div>

{{-- Estado --}}
<div class="mb-5">
    <input type="hidden" name="status" value="0">
    <label class="inline-flex items-center gap-2 cursor-pointer">
        <input type="checkbox" name="status" value="1"
               {{ old('status', $owner->status ?? true) ? 'checked' : '' }}
               class="rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500">
        <span class="text-sm text-gray-700">Activo</span>
    </label>
    @error('status')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>