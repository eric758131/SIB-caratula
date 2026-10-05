{{-- Categoría primaria --}}
<div class="mb-5">
    <label for="primary_category_id" class="block text-sm font-medium text-gray-700 mb-1">
        Categoría primaria <span class="text-red-500">*</span>
    </label>
    <select name="primary_category_id" id="primary_category_id" required
            class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500
                   @error('primary_category_id') border-red-400 @else border-gray-300 @enderror">
        <option value="">— Selecciona una categoría —</option>
        @foreach ($primaryCategories as $primary)
            <option value="{{ $primary->id }}"
                {{ (string) old('primary_category_id', $secondaryCategory->primary_category_id ?? '') === (string) $primary->id ? 'selected' : '' }}>
                {{ $primary->name }}
            </option>
        @endforeach
    </select>
    @error('primary_category_id')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

{{-- Nombre --}}
<div class="mb-5">
    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
        Nombre <span class="text-red-500">*</span>
    </label>
    <input type="text" name="name" id="name"
           value="{{ old('name', $secondaryCategory->name ?? '') }}"
           maxlength="200" required
           placeholder="Ej: Instalaciones domiciliarias"
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
                     @error('description') border-red-400 @else border-gray-300 @enderror">{{ old('description', $secondaryCategory->description ?? '') }}</textarea>
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
                     @error('example') border-red-400 @else border-gray-300 @enderror">{{ old('example', $secondaryCategory->example ?? '') }}</textarea>
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
                     @error('important_notes') border-red-400 @else border-gray-300 @enderror">{{ old('important_notes', $secondaryCategory->important_notes ?? '') }}</textarea>
    @error('important_notes')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

{{-- Imágenes --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
    @foreach (['image_1' => 'Imagen 1', 'image_2' => 'Imagen 2', 'image_3' => 'Imagen 3'] as $field => $label)
        <div>
            <label for="{{ $field }}" class="block text-sm font-medium text-gray-700 mb-1">{{ $label }}</label>

            @if (!empty($secondaryCategory->$field ?? null))
                <div class="mb-2 flex items-start gap-3">
                    <img src="{{ Storage::url($secondaryCategory->$field) }}"
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

{{-- Documento de definición (el usuario lo ve desde el asistente) --}}
<div class="mb-5">
    <label for="definition" class="block text-sm font-medium text-gray-700 mb-1">Definición (documento)</label>

    @if (!empty($secondaryCategory->definition ?? null))
        <div class="mb-3 flex items-start gap-4 p-3 bg-gray-50 rounded border border-gray-200">
            <div class="flex-1">
                <a href="{{ Storage::url($secondaryCategory->definition) }}" target="_blank"
                   class="text-brand-600 hover:text-brand-800 text-sm font-medium flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Ver documento actual
                </a>
            </div>

            <label class="inline-flex items-center gap-2 cursor-pointer text-sm text-red-600">
                <input type="checkbox" name="remove_definition" value="1"
                       class="rounded border-gray-300 text-red-600 shadow-sm focus:ring-red-500">
                <span>Eliminar documento</span>
            </label>
        </div>
    @endif

    <input type="file" name="definition" id="definition"
           accept=".pdf,.jpg,.jpeg,.png,.webp"
           class="w-full border rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500
                  @error('definition') border-red-400 @else border-gray-300 @enderror">
    @error('definition')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
    <p class="text-xs text-gray-500 mt-1">PDF, JPG, PNG o WEBP. Máx. 10 MB. El usuario podrá verlo desde el asistente al elegir esta categoría.</p>
</div>

{{-- Estado --}}
<div class="mb-5">
    <input type="hidden" name="status" value="0">
    <label class="inline-flex items-center gap-2 cursor-pointer">
        <input type="checkbox" name="status" value="1"
               {{ old('status', $secondaryCategory->status ?? true) ? 'checked' : '' }}
               class="rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500">
        <span class="text-sm text-gray-700">Activo</span>
    </label>
    @error('status')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>