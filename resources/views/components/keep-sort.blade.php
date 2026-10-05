{{-- Dentro de un formulario de filtros (GET): conserva el orden elegido al buscar o filtrar --}}
@foreach (['sort', 'direction'] as $param)
    @if (is_string(request()->query($param)) && request()->query($param) !== '')
        <input type="hidden" name="{{ $param }}" value="{{ request()->query($param) }}">
    @endif
@endforeach
