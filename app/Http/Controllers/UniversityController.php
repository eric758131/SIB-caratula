<?php

namespace App\Http\Controllers;

use App\Models\University;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UniversityController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $status = $request->input('status', '');

        $universities = University::query()
            ->whereSearch($search, ['name', 'id'])
            ->when($status !== '' && $status !== null, function ($query) use ($status) {
                $query->where('status', filter_var($status, FILTER_VALIDATE_BOOLEAN));
            })
            ->sortable(['id' => 'id', 'name' => 'name', 'status' => 'status'], 'name')
            ->paginate(10)
            ->withQueryString();

        return view('universities.index', compact('universities', 'search', 'status'));
    }

    public function create()
    {
        return view('universities.create');
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
                Rule::unique('universities', 'name')->where(function ($query) use ($request) {
                    $query->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($request->input('name')), 'UTF-8')]);
                }),
            ],
            'status' => ['nullable', 'boolean'],
        ], $this->validationMessages(), $this->validationAttributes());

        $validated['name'] = $this->normalizeName($validated['name']);
        $validated['status'] = $request->boolean('status', true);

        try {
            University::create($validated);
        } catch (QueryException $e) {
            if ($e->getCode() === '23505') {
                return back()->withInput()
                    ->withErrors(['name' => 'Ya existe una universidad con ese nombre.']);
            }
            throw $e;
        }

        return redirect()
            ->route('universities.index')
            ->with('success', 'Universidad creada correctamente.');
    }

    public function edit(University $university)
    {
        return view('universities.edit', compact('university'));
    }

    public function update(Request $request, University $university)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'min:3',
                'max:200',
                'regex:/^[\pL\pN\s\.\-\(\)\/]+$/u',
                Rule::unique('universities', 'name')
                    ->where(function ($query) use ($request) {
                        $query->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($request->input('name')), 'UTF-8')]);
                    })
                    ->ignore($university->id),
            ],
            'status' => ['nullable', 'boolean'],
        ], $this->validationMessages(), $this->validationAttributes());

        $validated['name'] = $this->normalizeName($validated['name']);
        $validated['status'] = $request->boolean('status', true);

        try {
            $university->update($validated);
        } catch (QueryException $e) {
            if ($e->getCode() === '23505') {
                return back()->withInput()
                    ->withErrors(['name' => 'Ya existe una universidad con ese nombre.']);
            }
            throw $e;
        }

        return redirect()
            ->route('universities.index')
            ->with('success', 'Universidad actualizada correctamente.');
    }

    public function destroy(University $university)
    {
        $university->update(['status' => ! $university->status]);

        $message = $university->status
            ? 'Universidad reactivada correctamente.'
            : 'Universidad desactivada correctamente.';

        return redirect()
            ->route('universities.index')
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
            'name.required' => 'El nombre de la universidad es obligatorio.',
            'name.string' => 'El nombre debe ser texto.',
            'name.min' => 'El nombre debe tener al menos :min caracteres.',
            'name.max' => 'El nombre no puede superar los :max caracteres.',
            'name.regex' => 'El nombre solo puede contener letras, números, espacios, puntos, guiones, paréntesis y barras.',
            'name.unique' => 'Ya existe una universidad con ese nombre.',
        ];
    }

    private function validationAttributes(): array
    {
        return [
            'name' => 'nombre',
            'status' => 'estado',
        ];
    }
}