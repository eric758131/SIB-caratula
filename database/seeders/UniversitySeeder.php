<?php

namespace Database\Seeders;

use App\Models\University;
use Illuminate\Database\Seeder;

class UniversitySeeder extends Seeder
{
    public function run(): void
    {
        $universities = [
            'Universidad Mayor de San Andrés',
            'Universidad Autónoma Tomás Frías',
            'Universidad Autónoma Juan Misael Saracho',
            'Universidad Mayor Real y Pontificia de San Francisco Xavier de Chuquisaca',
            'Universidad Autónoma Gabriel René Moreno',
            'Universidad Técnica de Oruro',
            'Universidad Autónoma del Beni José Ballivián',
            'Universidad Amazónica de Pando',
            'Universidad Católica Boliviana San Pablo',
            'Universidad Privada Boliviana',
            'Universidad Mayor de San Simón',
            'Universidad Privada del Valle',
            'Universidad NUR',
            'Universidad Salesiana de Bolivia',
            'Universidad Andina Simón Bolívar',
            'Universidad Tecnológica Privada de Santa Cruz',
            'Universidad Privada de Santa Cruz de la Sierra',
            'Universidad Central',
            'Universidad Evangélica Boliviana',
            'Universidad Loyola',
        ];

        foreach ($universities as $name) {
            University::updateOrCreate(
                ['name' => $name],
                ['status' => true]
            );
        }

        $this->command->info('Universidades creadas: ' . University::count());
    }
}