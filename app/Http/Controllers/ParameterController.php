<?php

namespace App\Http\Controllers;

use App\Models\Parameter;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ParameterController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $type   = $request->input('parameter_type', '');
        $status = $request->input('status', '');

        $parameters = Parameter::query()
            ->whereSearch($search, ['name', 'description', 'unit_of_measure', 'id'])
            ->when($type !== '' && $type !== null, fn($q) => $q->where('parameter_type', $type))
            ->when($status !== '' && $status !== null, function ($query) use ($status) {
                $query->where('status', filter_var($status, FILTER_VALIDATE_BOOLEAN));
            })
            ->sortable([
                'id'     => 'id',
                'name'   => 'name',
                'unit'   => 'unit_of_measure',
                'type'   => 'parameter_type',
                'data'   => 'data_type',
                'status' => 'status',
            ], 'name')
            ->paginate(10)
            ->withQueryString();

        return view('parameters.index', compact('parameters', 'search', 'type', 'status'));
    }

    public function create()
    {
        return view('parameters.create');
    }

    public function store(Request $request)
    {
        $validated = $this->normalizeFields($this->validateParameter($request));

        try {
            Parameter::create($validated);
        } catch (QueryException $e) {
            if ($e->getCode() === '23505') {
                return back()->withInput()
                    ->withErrors(['name' => 'Ya existe un parámetro con ese nombre.']);
            }
            throw $e;
        }

        return redirect()
            ->route('parameters.index')
            ->with('success', 'Parámetro creado correctamente.');
    }

    public function edit(Parameter $parameter)
    {
        return view('parameters.edit', compact('parameter'));
    }

    public function update(Request $request, Parameter $parameter)
    {
        $validated = $this->normalizeFields($this->validateParameter($request, $parameter));

        try {
            $parameter->update($validated);
        } catch (QueryException $e) {
            if ($e->getCode() === '23505') {
                return back()->withInput()
                    ->withErrors(['name' => 'Ya existe un parámetro con ese nombre.']);
            }
            throw $e;
        }

        return redirect()
            ->route('parameters.index')
            ->with('success', 'Parámetro actualizado correctamente.');
    }

    public function destroy(Parameter $parameter)
    {
        $parameter->update(['status' => ! $parameter->status]);

        $message = $parameter->status
            ? 'Parámetro reactivado correctamente.'
            : 'Parámetro desactivado correctamente.';

        return redirect()
            ->route('parameters.index')
            ->with('success', $message);
    }

    /* ============================================================
     |  Validación central
     * ============================================================ */
    private function validateParameter(Request $request, ?Parameter $parameter = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'min:2',
                'max:200',
                'regex:/^[\pL\pN\s\.\-\(\)\/]+$/u',
                Rule::unique('parameters', 'name')
                    ->where(function ($query) use ($request) {
                        $query->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($request->input('name')), 'UTF-8')]);
                    })
                    ->ignore($parameter?->id),
            ],
            'description'     => ['nullable', 'string', 'max:2000'],
            'unit_of_measure' => ['nullable', 'string', 'max:20'],
            'parameter_type'  => ['required', Rule::in(Parameter::TYPES)],
            'data_type'       => ['required', Rule::in(Parameter::DATA_TYPES)],
            'max_decimals'    => ['nullable', 'integer', 'min:0', 'max:2147483647'],
            'status'          => ['nullable', 'boolean'],
        ], $this->validationMessages(), $this->validationAttributes());
    }

    private function normalizeFields(array $data): array
    {
        $data['name']            = $this->normalizeName($data['name']);
        $data['description']     = $this->normalizeText($data['description'] ?? null);
        $data['unit_of_measure'] = !empty($data['unit_of_measure']) ? trim($data['unit_of_measure']) : null;
        // Solo aplica a Número; vacío = sin límite
        $data['max_decimals']    = ($data['data_type'] === Parameter::DATA_NUMERO && isset($data['max_decimals']))
            ? (int) $data['max_decimals']
            : null;
        $data['status']          = request()->boolean('status', true);

        return $data;
    }

    private function normalizeName(string $name): string
    {
        $name = preg_replace('/\s+/', ' ', trim($name));

        return mb_convert_case(mb_strtolower($name, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }

    private function normalizeText(?string $text): ?string
    {
        if ($text === null) return null;
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        return trim($text) ?: null;
    }

    private function validationMessages(): array
    {
        return [
            'name.required' => 'El nombre del parámetro es obligatorio.',
            'name.min' => 'El nombre debe tener al menos :min caracteres.',
            'name.max' => 'El nombre no puede superar los :max caracteres.',
            'name.regex' => 'El nombre solo puede contener letras, números, espacios, puntos, guiones, paréntesis y barras.',
            'name.unique' => 'Ya existe un parámetro con ese nombre.',
            'parameter_type.required' => 'Debes seleccionar un tipo de parámetro.',
            'parameter_type.in' => 'El tipo de parámetro seleccionado no es válido.',
            'data_type.required' => 'Debes seleccionar el tipo de dato.',
            'data_type.in' => 'El tipo de dato seleccionado no es válido.',
            'unit_of_measure.max' => 'La unidad de medida no puede superar los :max caracteres.',
            'max_decimals.integer' => 'El Nro. de decimales máximos debe ser un número entero.',
            'max_decimals.min' => 'El Nro. de decimales máximos no puede ser negativo.',
            'max_decimals.max' => 'El Nro. de decimales máximos es demasiado grande.',
            'description.max' => 'La descripción no puede superar los :max caracteres.',
        ];
    }

    private function validationAttributes(): array
    {
        return [
            'name' => 'nombre',
            'description' => 'descripción',
            'unit_of_measure' => 'unidad de medida',
            'parameter_type' => 'tipo de parámetro',
            'data_type' => 'tipo de dato',
            'max_decimals' => 'Nro. de decimales máximos',
        ];
    }
}
