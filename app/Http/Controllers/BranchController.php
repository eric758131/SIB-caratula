<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $status = $request->input('status', '');

        $branches = Branch::query()
            ->whereSearch($search, ['name', 'id'])
            ->when($status !== '' && $status !== null, function ($query) use ($status) {
                $query->where('status', filter_var($status, FILTER_VALIDATE_BOOLEAN));
            })
            ->sortable(['id' => 'id', 'name' => 'name', 'status' => 'status'], 'name')
            ->paginate(10)
            ->withQueryString();

        return view('branches.index', compact('branches', 'search', 'status'));
    }

    public function create()
    {
        return view('branches.create');
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
                'not_regex:/^\s|\s$/',
                'not_regex:/\s{2,}/',
                Rule::unique('branches', 'name')->where(function ($query) use ($request) {
                    $query->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($request->input('name')), 'UTF-8')]);
                }),
            ],
            'status' => ['nullable', 'boolean'],
        ], $this->validationMessages(), $this->validationAttributes());

        $validated['name'] = $this->normalizeName($validated['name']);
        $validated['status'] = $request->boolean('status', true);

        try {
            Branch::create($validated);
        } catch (QueryException $e) {
            if ($e->getCode() === '23505') {
                return back()->withInput()
                    ->withErrors(['name' => 'Ya existe una rama de ingeniería con ese nombre.']);
            }
            throw $e;
        }

        return redirect()
            ->route('branches.index')
            ->with('success', 'Rama de ingeniería creada correctamente.');
    }

    public function edit(Branch $branch)
    {
        return view('branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'min:3',
                'max:200',
                'regex:/^[\pL\pN\s\.\-\(\)\/]+$/u',
                'not_regex:/^\s|\s$/',
                'not_regex:/\s{2,}/',
                Rule::unique('branches', 'name')
                    ->where(function ($query) use ($request) {
                        $query->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($request->input('name')), 'UTF-8')]);
                    })
                    ->ignore($branch->id),
            ],
            'status' => ['nullable', 'boolean'],
        ], $this->validationMessages(), $this->validationAttributes());

        $validated['name'] = $this->normalizeName($validated['name']);
        $validated['status'] = $request->boolean('status', true);

        try {
            $branch->update($validated);
        } catch (QueryException $e) {
            if ($e->getCode() === '23505') {
                return back()->withInput()
                    ->withErrors(['name' => 'Ya existe una rama de ingeniería con ese nombre.']);
            }
            throw $e;
        }

        return redirect()
            ->route('branches.index')
            ->with('success', 'Rama de ingeniería actualizada correctamente.');
    }

    public function destroy(Branch $branch)
    {
        $branch->update(['status' => ! $branch->status]);

        $message = $branch->status
            ? 'Rama de ingeniería reactivada correctamente.'
            : 'Rama de ingeniería desactivada correctamente.';

        return redirect()
            ->route('branches.index')
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
            'name.required' => 'El nombre de la rama es obligatorio.',
            'name.string' => 'El nombre debe ser texto.',
            'name.min' => 'El nombre debe tener al menos :min caracteres.',
            'name.max' => 'El nombre no puede superar los :max caracteres.',
            'name.regex' => 'El nombre solo puede contener letras, números, espacios, puntos, guiones, paréntesis y barras.',
            'name.unique' => 'Ya existe una rama de ingeniería con ese nombre.',
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