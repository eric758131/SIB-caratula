{{--
    Encabezado de tabla que ordena al hacer clic: ▲ ascendente, ▼ descendente.
    Conserva la búsqueda y los filtros y vuelve a la página 1.

    <x-sort-th column="name" default>Nombre</x-sort-th>
    · column:  clave de ordenamiento (debe estar en ->sortable([...]) del controlador)
    · default: marca la columna por la que se ordena cuando no se eligió ninguna
    · default-direction: dirección de ese orden por defecto (asc por defecto)
    · align:   left | right | center
--}}
@props(['column', 'default' => false, 'defaultDirection' => 'asc', 'align' => 'left'])

@php
    $current = request()->query('sort');
    $isActive = $current === $column || (! $current && $default);
    $direction = $current === $column
        ? (request()->query('direction') === 'desc' ? 'desc' : 'asc')
        : $defaultDirection;
    $next = $isActive && $direction === 'asc' ? 'desc' : 'asc';
    $url = request()->fullUrlWithQuery(['sort' => $column, 'direction' => $next, 'page' => null]);
    $label = trim(strip_tags($slot));
    $alignClass = ['right' => 'text-right', 'center' => 'text-center'][$align] ?? 'text-left';
    $justify = ['right' => 'justify-end', 'center' => 'justify-center'][$align] ?? 'justify-start';
@endphp

<th {{ $attributes->merge(['class' => "px-4 py-3 text-xs font-semibold uppercase tracking-wider text-gray-600 {$alignClass}"]) }}
    aria-sort="{{ $isActive ? ($direction === 'asc' ? 'ascending' : 'descending') : 'none' }}">
    <a href="{{ $url }}"
       class="group inline-flex items-center gap-1 {{ $justify }} select-none transition hover:text-brand-700 {{ $isActive ? 'text-brand-700' : '' }}"
       title="Ordenar por {{ mb_strtolower($label) }} ({{ $next === 'asc' ? 'ascendente' : 'descendente' }})">
        <span>{{ $slot }}</span>
        <span class="inline-flex flex-col text-[8px] leading-[7px]" aria-hidden="true">
            <span class="{{ $isActive && $direction === 'asc' ? 'text-brand-600' : 'text-gray-300 group-hover:text-gray-400' }}">▲</span>
            <span class="{{ $isActive && $direction === 'desc' ? 'text-brand-600' : 'text-gray-300 group-hover:text-gray-400' }}">▼</span>
        </span>
    </a>
</th>
