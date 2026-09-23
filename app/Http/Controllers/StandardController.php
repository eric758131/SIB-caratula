<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Standard;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;

class StandardController extends Controller
{
    public function index(Request $request)
    {
        $search    = trim((string) $request->input('search', ''));
        $countryId = $request->input('country_id', '');
        $status    = $request->input('status', '');

        $standards = Standard::query()
            ->with('country')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ILIKE', "%{$search}%")
                      ->orWhere('description', 'ILIKE', "%{$search}%")
                      ->orWhere('id', 'ILIKE', "%{$search}%");
                });
            })
            ->when($countryId !== '' && $countryId !== null, function ($query) use ($countryId) {
                $query->where('country_id', $countryId);
            })
            ->when($status !== '' && $status !== null, function ($query) use ($status) {
                $query->where('status', filter_var($status, FILTER_VALIDATE_BOOLEAN));
            })
            ->orderBy('name', 'asc')
            ->paginate(10)
            ->withQueryString();

        $countries = Country::orderBy('name')->get();

        return view('standards.index', compact('standards', 'countries', 'search', 'countryId', 'status'));
    }

    public function create()
    {
        $countries = Country::where('status', true)->orderBy('name')->get();

        return view('standards.create', compact('countries'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'country_id' => [
                'required',
                'integer',
                Rule::exists('countries', 'id')->where('status', true),
            ],
            'name' => [
                'required',
                'string',
                'min:3',
                'max:100',
                'regex:/^[\pL\pN\s\.\-\(\)\/]+$/u',
                Rule::unique('standards', 'name')->where(function ($query) use ($request) {
                    $query->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($request->input('name')), 'UTF-8')]);
                }),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'effective_date' => ['nullable', 'date'],
            'status' => ['nullable', 'boolean'],
        ], $this->validationMessages(), $this->validationAttributes());

        $validated['name'] = $this->normalizeName($validated['name']);
        $validated['description'] = $this->normalizeDescription($validated['description'] ?? null);
        $validated['status'] = $request->boolean('status', true);

        try {
            Standard::create($validated);
        } catch (QueryException $e) {
            if ($e->getCode() === '23505') {
                return back()
                    ->withInput()
                    ->withErrors(['name' => 'Ya existe una norma con ese nombre.']);
            }
            throw $e;
        }

        return redirect()
            ->route('standards.index')
            ->with('success', 'Norma creada correctamente.');
    }

    public function edit(Standard $standard)
    {
        $countries = Country::where(function ($q) use ($standard) {
                $q->where('status', true)
                  ->orWhere('id', $standard->country_id);
            })
            ->orderBy('name')
            ->get();

        return view('standards.edit', compact('standard', 'countries'));
    }

    public function update(Request $request, Standard $standard)
    {
        $validated = $request->validate([
            'country_id' => [
                'required',
                'integer',
                Rule::exists('countries', 'id')->where(function ($q) use ($standard) {
                    $q->where('status', true)
                    ->orWhere('id', $standard->country_id);
                }),
            ],
            'name' => [
                'required',
                'string',
                'min:3',
                'max:100',
                'regex:/^[\pL\pN\s\.\-\(\)\/]+$/u',
                Rule::unique('standards', 'name')
                    ->where(function ($query) use ($request) {
                        $query->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($request->input('name')), 'UTF-8')]);
                    })
                    ->ignore($standard->id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'effective_date' => ['nullable', 'date'],
            'status' => ['nullable', 'boolean'],
        ], $this->validationMessages(), $this->validationAttributes());

        $validated['name'] = $this->normalizeName($validated['name']);
        $validated['description'] = $this->normalizeDescription($validated['description'] ?? null);
        $validated['status'] = $request->boolean('status', true);

        try {
            $standard->update($validated);
        } catch (QueryException $e) {
            if ($e->getCode() === '23505') {
                return back()
                    ->withInput()
                    ->withErrors(['name' => 'Ya existe una norma con ese nombre.']);
            }
            throw $e;
        }

        return redirect()
            ->route('standards.index')
            ->with('success', 'Norma actualizada correctamente.');
    }

    public function destroy(Standard $standard)
    {
        $standard->update(['status' => ! $standard->status]);

        $message = $standard->status
            ? 'Norma reactivada correctamente.'
            : 'Norma desactivada correctamente.';

        return redirect()
            ->route('standards.index')
            ->with('success', $message);
    }

    private function normalizeName(string $name): string
    {
        $name = preg_replace('/\s+/', ' ', trim($name));

        return mb_strtoupper($name, 'UTF-8');
    }

    private function normalizeDescription(?string $description): ?string
    {
        if ($description === null) {
            return null;    
        }

        $description = preg_replace('/[ \t]+/', ' ', $description);
        $description = preg_replace('/\n{3,}/', "\n\n", $description);

        return trim($description) ?: null;
    }

    private function validationMessages(): array
    {
        return [
            'country_id.required' => 'Debes seleccionar un país.',
            'country_id.exists'   => 'El país seleccionado no es válido o está inactivo.',
            'name.required'       => 'El nombre de la norma es obligatorio.',
            'name.string'         => 'El nombre debe ser texto.',
            'name.min'            => 'El nombre debe tener al menos :min caracteres.',
            'name.max'            => 'El nombre no puede superar los :max caracteres.',
            'name.regex'          => 'El nombre solo puede contener letras, números, espacios, puntos, guiones, paréntesis y barras.',
            'name.unique'         => 'Ya existe una norma con ese nombre.',
            'description.max'     => 'La descripción no puede superar los :max caracteres.',
            'effective_date.date' => 'La fecha de vigencia no es válida.',
            'status.boolean'      => 'El estado debe ser verdadero o falso.',
        ];
    }

    private function validationAttributes(): array
    {
        return [
            'country_id'     => 'país',
            'name'           => 'nombre',
            'description'    => 'descripción',
            'effective_date' => 'fecha de vigencia',
            'status'         => 'estado',
        ];
    }
}