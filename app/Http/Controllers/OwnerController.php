<?php

namespace App\Http\Controllers;

use App\Models\Owner;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OwnerController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $status = $request->input('status', '');

        $owners = Owner::query()
            ->withCount('procedureOwners')
            ->whereSearch($search, ['name', 'phone', 'id'])
            ->when($status !== '' && $status !== null, function ($query) use ($status) {
                $query->where('status', filter_var($status, FILTER_VALIDATE_BOOLEAN));
            })
            ->sortable([
                'id'         => 'id',
                'name'       => 'name',
                'phone'      => 'phone',
                'procedures' => fn ($q, $dir) => $q->orderBy('procedure_owners_count', $dir),
                'status'     => 'status',
            ], 'name')
            ->paginate(15)
            ->withQueryString();

        return view('owners.index', compact('owners', 'search', 'status'));
    }

    public function create()
    {
        return view('owners.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'min:3',
                'max:200',
                "regex:/^[\pL\s'\-\.\&]+$/u",
                Rule::unique('owners', 'name')->where(function ($query) use ($request) {
                    $query->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($request->input('name')), 'UTF-8')]);
                }),
            ],
            'phone' => [
                'nullable',
                'string',
                'max:30',
                'regex:/^[\d\+\-\s\(\)]+$/',
            ],
            'status' => ['nullable', 'boolean'],
        ], $this->validationMessages(), $this->validationAttributes());

        $validated['name'] = $this->normalizeName($validated['name']);
        $validated['phone'] = !empty($validated['phone']) ? trim($validated['phone']) : null;
        $validated['status'] = $request->boolean('status', true);

        try {
            Owner::create($validated);
        } catch (QueryException $e) {
            if ($e->getCode() === '23505') {
                return back()->withInput()
                    ->withErrors(['name' => 'Ya existe un propietario con ese nombre.']);
            }
            throw $e;
        }

        return redirect()
            ->route('owners.index')
            ->with('success', 'Propietario creado correctamente.');
    }

    public function edit(Owner $owner)
    {
        return view('owners.edit', compact('owner'));
    }

    public function update(Request $request, Owner $owner)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'min:3',
                'max:200',
                "regex:/^[\pL\s'\-\.\&]+$/u",
                Rule::unique('owners', 'name')
                    ->where(function ($query) use ($request) {
                        $query->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($request->input('name')), 'UTF-8')]);
                    })
                    ->ignore($owner->id),
            ],
            'phone' => [
                'nullable',
                'string',
                'max:30',
                'regex:/^[\d\+\-\s\(\)]+$/',
            ],
            'status' => ['nullable', 'boolean'],
        ], $this->validationMessages(), $this->validationAttributes());

        $validated['name'] = $this->normalizeName($validated['name']);
        $validated['phone'] = !empty($validated['phone']) ? trim($validated['phone']) : null;
        $validated['status'] = $request->boolean('status', true);

        try {
            $owner->update($validated);
        } catch (QueryException $e) {
            if ($e->getCode() === '23505') {
                return back()->withInput()
                    ->withErrors(['name' => 'Ya existe un propietario con ese nombre.']);
            }
            throw $e;
        }

        return redirect()
            ->route('owners.index')
            ->with('success', 'Propietario actualizado correctamente.');
    }

    public function destroy(Owner $owner)
    {
        if ($owner->status && $owner->procedureOwners()->exists()) {
            return redirect()
                ->route('owners.index')
                ->with('error', 'No se puede desactivar: el propietario está asociado a uno o más trámites.');
        }

        $owner->update(['status' => ! $owner->status]);

        $message = $owner->status
            ? 'Propietario reactivado correctamente.'
            : 'Propietario desactivado correctamente.';

        return redirect()
            ->route('owners.index')
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
            'name.required' => 'El nombre del propietario es obligatorio.',
            'name.string' => 'El nombre debe ser texto.',
            'name.min' => 'El nombre debe tener al menos :min caracteres.',
            'name.max' => 'El nombre no puede superar los :max caracteres.',
            'name.regex' => 'El nombre solo puede contener letras, espacios, apóstrofes, guiones, puntos y el símbolo &.',
            'name.unique' => 'Ya existe un propietario con ese nombre.',
            'phone.regex' => 'El teléfono solo puede contener números, +, -, espacios y paréntesis.',
            'phone.max' => 'El teléfono no puede superar los :max caracteres.',
        ];
    }

    private function validationAttributes(): array
    {
        return [
            'name' => 'nombre',
            'phone' => 'teléfono',
            'status' => 'estado',
        ];
    }
}