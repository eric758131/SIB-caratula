<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            'Ingeniería Civil',
            'Ingeniería Eléctrica',
            'Ingeniería Electrónica',
            'Ingeniería Mecánica',
            'Ingeniería Industrial',
            'Ingeniería Química',
            'Ingeniería de Sistemas',
            'Ingeniería de Telecomunicaciones',
            'Ingeniería Ambiental',
            'Ingeniería Petrolera',
            'Ingeniería de Minas',
            'Ingeniería Agronómica',
            'Arquitectura',
        ];

        foreach ($branches as $name) {
            Branch::updateOrCreate(
                ['name' => $name],
                ['status' => true]
            );
        }

        $this->command->info('Ramas de ingeniería creadas: ' . Branch::count());
    }
}