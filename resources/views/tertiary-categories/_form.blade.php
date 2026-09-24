{{-- Categoría secundaria --}}
<div class="mb-5">
    <label for="secondary_category_id" class="block text-sm font-medium text-gray-700 mb-1">
        Categoría secundaria <span class="text-red-500">*</span>
    </label>
    <select name="secondary_category_id" id="secondary_category_id" required
            class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500
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
           style="text-transform: uppercase;"
           class="w-full border rounded px-3 py-2 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500
                  @error('code') border-red-400 @else border-gray-300 @enderror">
    @error('code')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
    <p class="text-xs text-gray-500 mt-1">
        Solo mayúsculas, números, guiones, puntos y barras. Máx. 20 caracteres. Se guarda en mayúsculas.
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
           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500
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
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500
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
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500
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
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500
                     @error('important_notes') border-red-400 @else border-gray-300 @enderror">{{ old('important_notes', $tertiaryCategory->important_notes ?? '') }}</textarea>
    @error('important_notes')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

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
                   class="w-full border rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
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
               class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
        <span class="text-sm text-gray-700">Activo</span>
    </label>
    @error('status')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>