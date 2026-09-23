<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;

class CountryController extends Controller
{
    /**
     * Lista de países con búsqueda, filtro por estado y paginación.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $status = $request->input('status', '');

        $countries = Country::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ILIKE', "%{$search}%")
                      ->orWhere('id', 'ILIKE', "%{$search}%");
                });
            })
            ->when($status !== '' && $status !== null, function ($query) use ($status) {
                $query->where('status', filter_var($status, FILTER_VALIDATE_BOOLEAN));
            })
            ->orderBy('name', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('countries.index', compact('countries', 'search', 'status'));
    }

    /**
     * Formulario de creación.
     */
    public function create()
    {
        return view('countries.create');
    }

    /**
     * Guarda un nuevo país.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'min:3',
                'max:100',
                'regex:/^[\pL\s\.\-\(\)]+$/u',
                Rule::unique('countries', 'name')->where(function ($query) use ($request) {
                    $query->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($request->input('name')), 'UTF-8')]);
                }),
            ],
            'status' => ['nullable', 'boolean'],
        ], $this->validationMessages(), $this->validationAttributes());

        $validated['name'] = $this->normalizeName($validated['name']);
        $validated['status'] = $request->boolean('status', true);

        try {
            Country::create($validated);
        } catch (QueryException $e) {
            if ($e->getCode() === '23505') {
                return back()
                    ->withInput()
                    ->withErrors(['name' => 'Ya existe un país con ese nombre.']);
            }
            throw $e;
        }

        return redirect()
            ->route('countries.index')
            ->with('success', 'País creado correctamente.');
    }

    /**
     * Formulario de edición.
     */
    public function edit(Country $country)
    {
        return view('countries.edit', compact('country'));
    }

    /**
     * Actualiza un país existente.
     */
    public function update(Request $request, Country $country)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'min:3',
                'max:100',
                'regex:/^[\pL\s\.\-\(\)]+$/u',
                Rule::unique('countries', 'name')
                    ->where(function ($query) use ($request) {
                        $query->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($request->input('name')), 'UTF-8')]);
                    })
                    ->ignore($country->id),
            ],
            'status' => ['nullable', 'boolean'],
        ], $this->validationMessages(), $this->validationAttributes());

        $validated['name'] = $this->normalizeName($validated['name']);
        $validated['status'] = $request->boolean('status', true);

        try {
            $country->update($validated);
        } catch (QueryException $e) {
            if ($e->getCode() === '23505') {
                return back()
                    ->withInput()
                    ->withErrors(['name' => 'Ya existe un país con ese nombre.']);
            }
            throw $e;
        }

        return redirect()
            ->route('countries.index')
            ->with('success', 'País actualizado correctamente.');
    }

    /**
     * Borrado lógico: alterna el campo status.
     */
    public function destroy(Country $country)
    {
        $country->update(['status' => ! $country->status]);

        $message = $country->status
            ? 'País reactivado correctamente.'
            : 'País desactivado correctamente.';

        return redirect()
            ->route('countries.index')
            ->with('success', $message);
    }

    /**
     * Normaliza el nombre: primera letra de cada palabra en mayúscula.
     */
    private function normalizeName(string $name): string
    {
        $name = preg_replace('/\s+/', ' ', trim($name));

        return mb_convert_case(mb_strtolower($name, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Mensajes de validación personalizados.
     */
    private function validationMessages(): array
    {
        return [
            'name.required' => 'El nombre del país es obligatorio.',
            'name.string'   => 'El nombre debe ser texto.',
            'name.min'      => 'El nombre debe tener al menos :min caracteres.',
            'name.max'      => 'El nombre no puede superar los :max caracteres.',
            'name.regex'    => 'El nombre solo puede contener letras, espacios, puntos, guiones y paréntesis.',
            'name.unique'   => 'Ya existe un país con ese nombre.',
            'status.boolean'=> 'El estado debe ser verdadero o falso.',
        ];
    }

    /**
     * Nombres amigables para los atributos.
     */
    private function validationAttributes(): array
    {
        return [
            'name'   => 'nombre',
            'status' => 'estado',
        ];
    }
}