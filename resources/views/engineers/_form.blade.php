@php
    $editing = isset($engineer);
@endphp

{{-- ============================================
     SECCIÓN 1: Identificación profesional
     ============================================ --}}
<h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Identificación profesional</h3>

<div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
    {{-- Rama --}}
    <div>
        <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-1">
            Rama de ingeniería <span class="text-red-500">*</span>
        </label>
        <select name="branch_id" id="branch_id" required
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500
                       @error('branch_id') border-red-400 @else border-gray-300 @enderror">
            <option value="">— Selecciona una rama —</option>
            @foreach ($branches as $branch)
                <option value="{{ $branch->id }}"
                    {{ (string) old('branch_id', $engineer->branch_id ?? '') === (string) $branch->id ? 'selected' : '' }}>
                    {{ $branch->name }}
                </option>
            @endforeach
        </select>
        @error('branch_id')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Universidad --}}
    <div>
        <label for="university_id" class="block text-sm font-medium text-gray-700 mb-1">
            Universidad <span class="text-red-500">*</span>
        </label>
        <select name="university_id" id="university_id" required
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500
                       @error('university_id') border-red-400 @else border-gray-300 @enderror">
            <option value="">— Selecciona una universidad —</option>
            @foreach ($universities as $university)
                <option value="{{ $university->id }}"
                    {{ (string) old('university_id', $engineer->university_id ?? '') === (string) $university->id ? 'selected' : '' }}>
                    {{ $university->name }}
                </option>
            @endforeach
        </select>
        @error('university_id')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- RNI --}}
    <div>
        <label for="rni" class="block text-sm font-medium text-gray-700 mb-1">
            RNI <span class="text-red-500">*</span>
        </label>
        <input type="text" name="rni" id="rni" inputmode="numeric"
               value="{{ old('rni', $engineer->rni ?? '') }}"
               maxlength="20" required
               placeholder="Ej: 1234567"
               class="w-full border rounded px-3 py-2 font-mono focus:outline-none focus:ring-2 focus:ring-brand-500
                      @error('rni') border-red-400 @else border-gray-300 @enderror">
        @error('rni')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
        <p class="text-xs text-gray-500 mt-1">Solo números. Entre 4 y 20 dígitos.</p>
    </div>

    {{-- CI --}}
    <div>
        <label for="ci" class="block text-sm font-medium text-gray-700 mb-1">
            CI <span class="text-red-500">*</span>
        </label>
        <input type="text" name="ci" id="ci"
               value="{{ old('ci', $engineer->ci ?? '') }}"
               maxlength="20" required
               placeholder="Ej: 1234567-1A"
               style="text-transform: uppercase;"
               class="w-full border rounded px-3 py-2 font-mono focus:outline-none focus:ring-2 focus:ring-brand-500
                      @error('ci') border-red-400 @else border-gray-300 @enderror">
        @error('ci')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
        <p class="text-xs text-gray-500 mt-1">Entre 5 y 15 dígitos, extensión opcional (ej: 1234567-1A).</p>
    </div>
</div>

{{-- ============================================
     SECCIÓN 2: Datos personales
     ============================================ --}}
<h3 class="text-sm font-semibold text-gray-500 uppercase mb-3 mt-8">Datos personales</h3>

<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
    {{-- Nombre --}}
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
            Nombre(s) <span class="text-red-500">*</span>
        </label>
        <input type="text" name="name" id="name"
               value="{{ old('name', $engineer->name ?? '') }}"
               maxlength="200" required
               placeholder="Ej: Juan Carlos"
               class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500
                      @error('name') border-red-400 @else border-gray-300 @enderror">
        @error('name')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Apellido paterno --}}
    <div>
        <label for="father_last_name" class="block text-sm font-medium text-gray-700 mb-1">
            Apellido paterno <span class="text-red-500">*</span>
        </label>
        <input type="text" name="father_last_name" id="father_last_name"
               value="{{ old('father_last_name', $engineer->father_last_name ?? '') }}"
               maxlength="100" required
               placeholder="Ej: Pérez"
               class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500
                      @error('father_last_name') border-red-400 @else border-gray-300 @enderror">
        @error('father_last_name')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Apellido materno --}}
    <div>
        <label for="mother_last_name" class="block text-sm font-medium text-gray-700 mb-1">
            Apellido materno
        </label>
        <input type="text" name="mother_last_name" id="mother_last_name"
               value="{{ old('mother_last_name', $engineer->mother_last_name ?? '') }}"
               maxlength="100"
               placeholder="Ej: Gómez"
               class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500
                      @error('mother_last_name') border-red-400 @else border-gray-300 @enderror">
        @error('mother_last_name')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>
</div>

{{-- ============================================
     SECCIÓN 3: Contacto
     ============================================ --}}
<h3 class="text-sm font-semibold text-gray-500 uppercase mb-3 mt-8">Contacto</h3>

<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
    {{-- Teléfono --}}
    <div>
        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
        <input type="text" name="phone" id="phone"
               value="{{ old('phone', $engineer->phone ?? '') }}"
               maxlength="30"
               placeholder="Ej: +591 71234567"
               class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500
                      @error('phone') border-red-400 @else border-gray-300 @enderror">
        @error('phone')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Email --}}
    <div class="md:col-span-2">
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
        <input type="email" name="email" id="email"
               value="{{ old('email', $engineer->email ?? '') }}"
               maxlength="100"
               placeholder="Ej: juan.perez@ejemplo.com"
               class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500
                      @error('email') border-red-400 @else border-gray-300 @enderror">
        @error('email')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Dirección --}}
    <div class="md:col-span-3">
        <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
        <textarea name="address" id="address" rows="2" maxlength="500"
                  placeholder="Dirección opcional..."
                  class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500
                         @error('address') border-red-400 @else border-gray-300 @enderror">{{ old('address', $engineer->address ?? '') }}</textarea>
        @error('address')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>
</div>

{{-- ============================================
     SECCIÓN 4: Información institucional
     ============================================ --}}
<h3 class="text-sm font-semibold text-gray-500 uppercase mb-3 mt-8">Información institucional</h3>

<div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
    {{-- Departamental SIB --}}
    <div>
        <label for="sib_departmental" class="block text-sm font-medium text-gray-700 mb-1">
            Departamental SIB <span class="text-red-500">*</span>
        </label>
        <select name="sib_departmental" id="sib_departmental" required
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500
                       @error('sib_departmental') border-red-400 @else border-gray-300 @enderror">
            @foreach (\App\Models\Engineer::SIB_DEPARTAMENTAL_LABELS as $value => $label)
                <option value="{{ $value }}"
                    {{ old('sib_departmental', $engineer->sib_departmental ?? \App\Models\Engineer::SIB_LA_PAZ) === $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('sib_departmental')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Estado --}}
    <div>
        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
            Estado <span class="text-red-500">*</span>
        </label>
        <select name="status" id="status" required
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500
                       @error('status') border-red-400 @else border-gray-300 @enderror">
            @foreach (\App\Models\Engineer::STATUS_LABELS as $value => $label)
                <option value="{{ $value }}"
                    {{ old('status', $engineer->status ?? \App\Models\Engineer::STATUS_ACTIVO) === $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('status')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>
</div>

{{-- ============================================
     SECCIÓN 5: Suspensión (dinámico según status)
     ============================================ --}}
<div id="suspension-section" class="hidden">
    <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3 mt-8">Fechas de suspensión</h3>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
        <div>
            <label for="suspension_start_date" class="block text-sm font-medium text-gray-700 mb-1">
                Fecha de inicio <span id="req-start" class="text-red-500 hidden">*</span>
            </label>
            <input type="date" name="suspension_start_date" id="suspension_start_date"
                   value="{{ old('suspension_start_date', isset($engineer) && $engineer->suspension_start_date ? $engineer->suspension_start_date->format('Y-m-d') : '') }}"
                   class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500
                          @error('suspension_start_date') border-red-400 @else border-gray-300 @enderror">
            @error('suspension_start_date')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="suspension_end_date" class="block text-sm font-medium text-gray-700 mb-1">
                Fecha de fin <span id="req-end" class="text-red-500 hidden">*</span>
            </label>
            <input type="date" name="suspension_end_date" id="suspension_end_date"
                   value="{{ old('suspension_end_date', isset($engineer) && $engineer->suspension_end_date ? $engineer->suspension_end_date->format('Y-m-d') : '') }}"
                   class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500
                          @error('suspension_end_date') border-red-400 @else border-gray-300 @enderror">
            @error('suspension_end_date')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
            <p class="text-xs text-gray-500 mt-1">Para suspensión indefinida, déjalo vacío.</p>
        </div>
    </div>
</div>

{{-- ============================================
     SECCIÓN 6: Especialidades
     ============================================ --}}
<h3 class="text-sm font-semibold text-gray-500 uppercase mb-3 mt-8">Especialidades</h3>

<div class="mb-6">
    @if ($specialties->isEmpty())
        <p class="text-sm text-gray-500 italic">No hay especialidades activas registradas.</p>
    @else
        @php
            $selected = old('specialties', $editing ? $engineer->specialties->pluck('id')->toArray() : []);
        @endphp
        <div class="grid grid-cols-1 md:grid-cols-3 gap-2 max-h-64 overflow-y-auto border border-gray-200 rounded p-3 bg-gray-50">
            @foreach ($specialties as $specialty)
                <label class="inline-flex items-center gap-2 cursor-pointer text-sm text-gray-700">
                    <input type="checkbox" name="specialties[]" value="{{ $specialty->id }}"
                           {{ in_array($specialty->id, $selected) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500">
                    <span>{{ $specialty->name }}</span>
                </label>
            @endforeach
        </div>
        @error('specialties.*')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    @endif
</div>

{{-- ============================================
     SECCIÓN 7: Imagen
     ============================================ --}}
<h3 class="text-sm font-semibold text-gray-500 uppercase mb-3 mt-8">Fotografía</h3>

<div class="mb-6">
    @if ($editing && $engineer->image)
        <div class="mb-3 flex items-start gap-4">
            <img src="{{ Storage::url($engineer->image) }}"
                 alt="{{ $engineer->full_name }}"
                 class="w-32 h-32 object-cover rounded-full border border-gray-200">

            <label class="inline-flex items-center gap-2 cursor-pointer text-sm text-red-600 mt-2">
                <input type="checkbox" name="remove_image" value="1"
                       class="rounded border-gray-300 text-red-600 shadow-sm focus:ring-red-500">
                <span>Eliminar imagen actual</span>
            </label>
        </div>
    @endif

    <input type="file" name="image" id="image"
           accept="image/jpeg,image/png,image/webp"
           class="w-full border rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500
                  @error('image') border-red-400 @else border-gray-300 @enderror">
    @error('image')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
    <p class="text-xs text-gray-500 mt-1">JPG, PNG o WEBP. Máx. 4 MB.</p>
</div>


{{-- ============================================
     Script: mostrar/ocultar sección de suspensión
     ============================================ --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const statusSelect = document.getElementById('status');
        const suspensionSection = document.getElementById('suspension-section');
        const reqStart = document.getElementById('req-start');
        const reqEnd = document.getElementById('req-end');
        const startInput = document.getElementById('suspension_start_date');
        const endInput = document.getElementById('suspension_end_date');

        function update() {
            const status = statusSelect.value;

            if (status === 'suspension_definida' || status === 'suspension_indefinida') {
                suspensionSection.classList.remove('hidden');
            } else {
                suspensionSection.classList.add('hidden');
                startInput.value = '';
                endInput.value = '';
            }

            if (status === 'suspension_definida') {
                reqStart.classList.remove('hidden');
                reqEnd.classList.remove('hidden');
                endInput.disabled = false;
            } else if (status === 'suspension_indefinida') {
                reqStart.classList.remove('hidden');
                reqEnd.classList.add('hidden');
                endInput.value = '';
                endInput.disabled = true;
            } else {
                reqStart.classList.add('hidden');
                reqEnd.classList.add('hidden');
                endInput.disabled = false;
            }
        }

        statusSelect.addEventListener('change', update);
        update();
    });
</script>