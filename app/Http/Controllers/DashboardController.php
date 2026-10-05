<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Country;
use App\Models\Engineer;
use App\Models\Owner;
use App\Models\Parameter;
use App\Models\PrimaryCategory;
use App\Models\Procedure;
use App\Models\RequiredDocument;
use App\Models\SecondaryCategory;
use App\Models\Specialty;
use App\Models\Standard;
use App\Models\TertiaryCategory;
use App\Models\University;

class DashboardController extends Controller
{
    public function index()
    {
        // [título, ícono, ruta, modelo, cómo contar los activos]
        $modules = [
            'Catálogos' => [
                ['Países', '🌎', 'countries.index', Country::class],
                ['Normas', '📏', 'standards.index', Standard::class],
                ['Universidades', '🎓', 'universities.index', University::class],
                ['Ramas de ingeniería', '🏗️', 'branches.index', Branch::class],
                ['Especialidades', '🔧', 'specialties.index', Specialty::class],
                ['Ingenieros', '👷', 'engineers.index', Engineer::class],
                ['Parámetros', '📊', 'parameters.index', Parameter::class],
                ['Propietarios', '👤', 'owners.index', Owner::class],
                ['Documentos requeridos', '📋', 'required-documents.index', RequiredDocument::class],
            ],
            'Categorías' => [
                ['Categorías primarias', '📁', 'primary-categories.index', PrimaryCategory::class],
                ['Categorías secundarias', '📂', 'secondary-categories.index', SecondaryCategory::class],
                ['Categorías terciarias', '🗂️', 'tertiary-categories.index', TertiaryCategory::class],
            ],
        ];

        $cards = collect($modules)->map(fn ($items) => collect($items)->map(function ($item) {
            [$title, $icon, $route, $model] = $item;

            // Ingenieros usa estados de texto; el resto, un booleano
            $active = $model === Engineer::class
                ? $model::where('status', Engineer::STATUS_ACTIVO)->count()
                : $model::where('status', true)->count();

            return compact('title', 'icon', 'route') + [
                'total'  => $model::count(),
                'active' => $active,
            ];
        }));

        $procedures = [
            'total'     => Procedure::count(),
            'pending'   => Procedure::where('status', Procedure::STATUS_PENDIENTE)->count(),
            'submitted' => Procedure::where('status', '!=', Procedure::STATUS_PENDIENTE)->count(),
        ];

        $recentProcedures = Procedure::with('primaryCategory')
            ->where('status', '!=', Procedure::STATUS_PENDIENTE)
            ->latest('updated_at')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact('cards', 'procedures', 'recentProcedures'));
    }
}
