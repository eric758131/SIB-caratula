<?php

namespace App\Http\Controllers;

use App\Models\TertiaryCategory;
use App\Models\Specialty;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SpecialtyController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $status = $request->input('status', '');

        $specialties = Specialty::query()
            ->withCount(['engineers', 'tertiaryCategories'])
            ->whereSearch($search, ['name', 'id'])
            ->when($status !== '' && $status !== null, function ($query) use ($status) {
                $query->where('status', filter_var($status, FILTER_VALIDATE_BOOLEAN));
            })
            ->sortable([
                'id'         => 'id',
                'name'       => 'name',
                'engineers'  => fn ($q, $dir) => $q->orderBy('engineers_count', $dir),
                'tertiaries' => fn ($q, $dir) => $q->orderBy('tertiary_categories_count', $dir),
                'status'     => 'status',
            ], 'name')
            ->paginate(10)
            ->withQueryString();

        return view('specialties.index', compact('specialties', 'search', 'status'));
    }

    public function create()
    {
        $tertiaryCategories = TertiaryCategory::with('secondaryCategory.primaryCategory')
            ->where('status', true)
            ->orderBy('name')
            ->get();

        return view('specialties.create', compact('tertiaryCategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'min:3',
                'max:200',
                'regex:/^[\pL\pN\s\.\-\(\)\/]+$/u',
                Rule::unique('specialties', 'name')->where(function ($query) use ($request) {
                    $query->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($request->input('name')), 'UTF-8')]);
                }),
            ],
            'status' => ['nullable', 'boolean'],
            'tertiary_categories' => ['nullable', 'array'],
            'tertiary_categories.*' => ['integer', Rule::exists('tertiary_categories', 'id')->where('status', true)],
        ], $this->validationMessages(), $this->validationAttributes());

        $validated['name'] = $this->normalizeName($validated['name']);
        $validated['status'] = $request->boolean('status', true);

        try {
            $specialty = Specialty::create($validated);
        } catch (QueryException $e) {
            if ($e->getCode() === '23505') {
                return back()->withInput()
                    ->withErrors(['name' => 'Ya existe una especialidad con ese nombre.']);
            }
            throw $e;
        }

        $specialty->tertiaryCategories()->sync($request->input('tertiary_categories', []));

        return redirect()
            ->route('specialties.index')
            ->with('success', 'Especialidad creada correctamente.');
    }

    public function edit(Specialty $specialty)
    {
        $specialty->load('tertiaryCategories');

        $assignedTertiaryIds = $specialty->tertiaryCategories->pluck('id')->toArray();

        $tertiaryCategories = TertiaryCategory::with('secondaryCategory.primaryCategory')
            ->where(function ($q) use ($assignedTertiaryIds) {
                $q->where('status', true);
                if (!empty($assignedTertiaryIds)) {
                    $q->orWhereIn('id', $assignedTertiaryIds);
                }
            })
            ->orderBy('name')
            ->get();

        return view('specialties.edit', compact('specialty', 'tertiaryCategories', 'assignedTertiaryIds'));
    }

    public function update(Request $request, Specialty $specialty)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'min:3',
                'max:200',
                'regex:/^[\pL\pN\s\.\-\(\)\/]+$/u',
                Rule::unique('specialties', 'name')
                    ->where(function ($query) use ($request) {
                        $query->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($request->input('name')), 'UTF-8')]);
                    })
                    ->ignore($specialty->id),
            ],
            'status' => ['nullable', 'boolean'],
            'tertiary_categories' => ['nullable', 'array'],
            'tertiary_categories.*' => ['integer', Rule::exists('tertiary_categories', 'id')->where('status', true)],
        ], $this->validationMessages(), $this->validationAttributes());

        $validated['name'] = $this->normalizeName($validated['name']);
        $validated['status'] = $request->boolean('status', true);

        try {
            $specialty->update($validated);
        } catch (QueryException $e) {
            if ($e->getCode() === '23505') {
                return back()->withInput()
                    ->withErrors(['name' => 'Ya existe una especialidad con ese nombre.']);
            }
            throw $e;
        }

        $specialty->tertiaryCategories()->sync($request->input('tertiary_categories', []));

        return redirect()
            ->route('specialties.index')
            ->with('success', 'Especialidad actualizada correctamente.');
    }

    public function destroy(Specialty $specialty)
    {
        if ($specialty->status && $specialty->engineers()->exists()) {
            return redirect()
                ->route('specialties.index')
                ->with('error', 'No se puede desactivar: la especialidad tiene ingenieros asignados. Desactívalos o reasígnalos primero.');
        }

        $specialty->update(['status' => ! $specialty->status]);

        $message = $specialty->status
            ? 'Especialidad reactivada correctamente.'
            : 'Especialidad desactivada correctamente.';

        return redirect()
            ->route('specialties.index')
            ->with('success', $message);
    }

    private function normalizeName(string $name): string
    {
        $name = preg_replace('/\s+/', ' ', trim($name));

        return mb_convert_case(mb_strtolower($name, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }

    private function validationMessages(): array
    {
        return [
            'name.required' => 'El nombre de la especialidad es obligatorio.',
            'name.string' => 'El nombre debe ser texto.',
            'name.min' => 'El nombre debe tener al menos :min caracteres.',
            'name.max' => 'El nombre no puede superar los :max caracteres.',
            'name.regex' => 'El nombre solo puede contener letras, números, espacios, puntos, guiones, paréntesis y barras.',
            'name.unique' => 'Ya existe una especialidad con ese nombre.',
            'tertiary_categories.array' => 'Las categorías terciarias deben ser un arreglo.',
            'tertiary_categories.*.exists' => 'Una de las categorías terciarias seleccionadas no es válida o está inactiva.',
        ];
    }

    private function validationAttributes(): array
    {
        return [
            'name' => 'nombre',
            'status' => 'estado',
            'tertiary_categories' => 'categorías terciarias',
        ];
    }
}