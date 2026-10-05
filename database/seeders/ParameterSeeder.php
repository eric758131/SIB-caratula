<?php

namespace Database\Seeders;

use App\Models\Parameter;
use App\Models\TertiaryCategory;
use Illuminate\Database\Seeder;

class ParameterSeeder extends Seeder
{
    /** Parámetros direccionales (nombre => descripción) */
    private const ADDRESS_PARAMETERS = [
        'Calle'     => 'Calle o avenida donde se ubica el proyecto.',
        'Dirección' => 'Dirección completa: calle, número y referencia.',
        'Zona'      => 'Zona o barrio del proyecto.',
        'Municipio' => 'Municipio donde se ubica el proyecto.',
    ];

    public function run(): void
    {
        /* ============================================================
         |  1. Catálogo de parámetros
         * ============================================================ */
        $parameters = [
            // Eléctricos
            ['name' => 'Potencia Instalada', 'unit_of_measure' => 'kW', 'parameter_type' => Parameter::TYPE_CARATULA,
             'description' => 'Potencia total instalada en la edificación.',
             'example' => 'Vivienda con 5 kW de potencia instalada.'],
            ['name' => 'Tensión Nominal', 'unit_of_measure' => 'V', 'parameter_type' => Parameter::TYPE_CARATULA,
             'description' => 'Tensión a la que opera la instalación.',
             'example' => '220 V monofásico, 380 V trifásico.'],
            ['name' => 'Corriente Nominal', 'unit_of_measure' => 'A', 'parameter_type' => Parameter::TYPE_CARATULA,
             'description' => 'Corriente máxima que soporta la instalación.',
             'example' => '32 A en acometida monofásica.'],
            ['name' => 'Factor de Potencia', 'unit_of_measure' => '', 'parameter_type' => Parameter::TYPE_PARAMETRICO,
             'description' => 'Relación entre potencia activa y aparente.',
             'example' => '0.95 en instalaciones industriales.'],
            ['name' => 'Longitud de Acometida', 'unit_of_measure' => 'm', 'parameter_type' => Parameter::TYPE_CARATULA,
             'description' => 'Distancia desde la red hasta el medidor.',
             'example' => '25 m desde el poste hasta la vivienda.'],
            ['name' => 'Cantidad de Circuitos', 'unit_of_measure' => 'und', 'parameter_type' => Parameter::TYPE_CARATULA,
             'description' => 'Número de circuitos derivados.',
             'example' => '8 circuitos en vivienda de 2 pisos.'],

            // Sanitarios
            ['name' => 'Longitud de Red de Agua', 'unit_of_measure' => 'm', 'parameter_type' => Parameter::TYPE_CARATULA,
             'description' => 'Longitud total de tubería de agua potable.',
             'example' => '30 m de tubería PVC 1/2".'],
            ['name' => 'Longitud de Red de Alcantarillado', 'unit_of_measure' => 'm', 'parameter_type' => Parameter::TYPE_CARATULA,
             'description' => 'Longitud total de tubería de desagüe.',
             'example' => '20 m de tubería PVC 4".'],
            ['name' => 'Diámetro de Tubería', 'unit_of_measure' => 'mm', 'parameter_type' => Parameter::TYPE_CARATULA,
             'description' => 'Diámetro nominal de la tubería.',
             'example' => '50 mm para desagüe principal.'],
            ['name' => 'Caudal de Diseño', 'unit_of_measure' => 'L/s', 'parameter_type' => Parameter::TYPE_PARAMETRICO,
             'description' => 'Caudal máximo que debe soportar la instalación.',
             'example' => '1.5 L/s en vivienda unifamiliar.'],
            ['name' => 'Cantidad de Artefactos', 'unit_of_measure' => 'und', 'parameter_type' => Parameter::TYPE_CARATULA,
             'description' => 'Número total de artefactos sanitarios.',
             'example' => '6 artefactos: 2 inodoros, 2 lavamanos, 1 ducha, 1 lavadero.'],
            ['name' => 'Profundidad de Pozo Séptico', 'unit_of_measure' => 'm', 'parameter_type' => Parameter::TYPE_CARATULA,
             'description' => 'Profundidad total del pozo séptico.',
             'example' => '3 m de profundidad.'],

            // Gas
            ['name' => 'Longitud de Red de Gas', 'unit_of_measure' => 'm', 'parameter_type' => Parameter::TYPE_CARATULA,
             'description' => 'Longitud total de tubería de gas.',
             'example' => '15 m de tubería de cobre.'],
            ['name' => 'Presión de Suministro', 'unit_of_measure' => 'mbar', 'parameter_type' => Parameter::TYPE_PARAMETRICO,
             'description' => 'Presión de operación del sistema de gas.',
             'example' => '20 mbar en red domiciliaria.'],
            ['name' => 'Cantidad de Artefactos a Gas', 'unit_of_measure' => 'und', 'parameter_type' => Parameter::TYPE_CARATULA,
             'description' => 'Número de artefactos conectados a gas.',
             'example' => '3 artefactos: cocina, calefón, calentador.'],

            // Generales
            ['name' => 'Superficie Construida', 'unit_of_measure' => 'm²', 'parameter_type' => Parameter::TYPE_CARATULA,
             'description' => 'Área total construida.',
             'example' => '120 m² de construcción.'],
            ['name' => 'Número de Pisos', 'unit_of_measure' => 'und', 'parameter_type' => Parameter::TYPE_CARATULA,
             'description' => 'Cantidad de niveles de la edificación.',
             'example' => '2 pisos + azotea.'],
            ['name' => 'Cantidad de Ambientes', 'unit_of_measure' => 'und', 'parameter_type' => Parameter::TYPE_CARATULA,
             'description' => 'Número total de ambientes.',
             'example' => '5 ambientes: 3 dormitorios, sala, cocina.'],
            ['name' => 'Tipo de Estructura', 'unit_of_measure' => '', 'parameter_type' => Parameter::TYPE_CARATULA,
             'description' => 'Sistema estructural principal.',
             'example' => 'Hormigón armado, estructuras metálicas, mampostería.'],
            ['name' => 'Zona Sísmica', 'unit_of_measure' => '', 'parameter_type' => Parameter::TYPE_PARAMETRICO,
             'description' => 'Zona sísmica según norma boliviana.',
             'example' => 'Zona 3 en La Paz.'],
            ['name' => 'Volumen de Tanque', 'unit_of_measure' => 'm³', 'parameter_type' => Parameter::TYPE_CARATULA,
             'description' => 'Capacidad de almacenamiento de agua.',
             'example' => 'Tanque de 2 m³.'],
            ['name' => 'Cantidad de Medidores', 'unit_of_measure' => 'und', 'parameter_type' => Parameter::TYPE_CARATULA,
             'description' => 'Número de medidores a instalar.',
             'example' => '1 medidor por unidad habitacional.'],
            ['name' => 'Distancia a Red Pública', 'unit_of_measure' => 'm', 'parameter_type' => Parameter::TYPE_PARAMETRICO,
             'description' => 'Distancia desde la propiedad hasta la red pública.',
             'example' => '10 m hasta el colector principal.'],
            ['name' => 'Número de Ambientes Húmedos', 'unit_of_measure' => 'und', 'parameter_type' => Parameter::TYPE_CARATULA,
             'description' => 'Ambientes con instalaciones sanitarias.',
             'example' => 'Baño, cocina y lavandería.'],
        ];

        // Parámetros de texto (el resto de la lista son números)
        $textParameters = ['Tipo de Estructura', 'Zona Sísmica'];

        foreach ($parameters as $data) {
            Parameter::updateOrCreate(
                ['name' => $data['name']],
                [
                    'description'     => $data['description'] ?? null,
                    'unit_of_measure' => ($data['unit_of_measure'] ?? '') ?: null,
                    'parameter_type'  => $data['parameter_type'],
                    'data_type'       => in_array($data['name'], $textParameters, true)
                        ? Parameter::DATA_TEXTO
                        : Parameter::DATA_NUMERO,
                    'status'          => true,
                ]
            );
        }

        // Direccionales: se llenan desde el mapa del asistente
        foreach (self::ADDRESS_PARAMETERS as $name => $description) {
            Parameter::updateOrCreate(
                ['name' => $name],
                [
                    'description'     => $description,
                    'unit_of_measure' => null,
                    'parameter_type'  => Parameter::TYPE_DIRECCIONAL,
                    'data_type'       => Parameter::DATA_TEXTO,
                    'status'          => true,
                ]
            );
        }

        $this->command->info('Parámetros creados: ' . Parameter::count());

        /* ============================================================
         |  2. Asignar parámetros a categorías terciarias
         * ============================================================ */
        $this->assignParametersToTertiaryCategories();

        $this->command->info('Asignaciones de parámetros creadas.');
    }

    private function assignParametersToTertiaryCategories(): void
    {
        $map = [
            'TER-EL-001' => [
                'Potencia Instalada'    => 0.150000,
                'Tensión Nominal'       => 0.050000,
                'Corriente Nominal'     => 0.080000,
                'Longitud de Acometida' => 0.200000,
                'Cantidad de Circuitos' => 0.100000,
                'Número de Pisos'       => 0.050000,
            ],
            'TER-EL-002' => [
                'Potencia Instalada'    => 0.250000,
                'Tensión Nominal'       => 0.080000,
                'Corriente Nominal'     => 0.120000,
                'Factor de Potencia'    => 0.050000,
                'Longitud de Acometida' => 0.300000,
                'Cantidad de Circuitos' => 0.150000,
                'Número de Pisos'       => 0.080000,
            ],
            'TER-EL-003' => [
                'Potencia Instalada'    => 0.200000,
                'Tensión Nominal'       => 0.060000,
                'Superficie Construida' => 0.120000,
                'Cantidad de Circuitos' => 0.100000,
            ],
            'TER-EL-004' => [
                'Potencia Instalada'    => 0.350000,
                'Tensión Nominal'       => 0.100000,
                'Factor de Potencia'    => 0.080000,
                'Superficie Construida' => 0.200000,
                'Cantidad de Circuitos' => 0.150000,
            ],
            'TER-SA-001' => [
                'Longitud de Red de Agua'     => 0.180000,
                'Diámetro de Tubería'         => 0.120000,
                'Cantidad de Artefactos'      => 0.100000,
                'Número de Ambientes Húmedos' => 0.080000,
            ],
            'TER-SA-002' => [
                'Longitud de Red de Agua' => 0.250000,
                'Diámetro de Tubería'     => 0.150000,
                'Caudal de Diseño'        => 0.200000,
                'Cantidad de Artefactos'  => 0.150000,
            ],
            'TER-SA-003' => [
                'Longitud de Red de Alcantarillado' => 0.250000,
                'Diámetro de Tubería'               => 0.150000,
                'Distancia a Red Pública'           => 0.100000,
            ],
            'TER-SA-004' => [
                'Profundidad de Pozo Séptico' => 0.400000,
                'Volumen de Tanque'           => 0.250000,
                'Cantidad de Artefactos'      => 0.150000,
            ],
            'TER-GA-001' => [
                'Longitud de Red de Gas'       => 0.200000,
                'Cantidad de Artefactos a Gas' => 0.150000,
                'Presión de Suministro'        => 0.100000,
            ],
            'TER-GA-002' => [
                'Longitud de Red de Gas' => 0.250000,
                'Presión de Suministro'  => 0.120000,
            ],
            'TER-GA-003' => [
                'Longitud de Red de Gas'       => 0.280000,
                'Cantidad de Artefactos a Gas' => 0.200000,
                'Presión de Suministro'        => 0.150000,
                'Superficie Construida'        => 0.100000,
            ],
            'TER-GA-004' => [
                'Longitud de Red de Gas'       => 0.400000,
                'Cantidad de Artefactos a Gas' => 0.300000,
                'Presión de Suministro'        => 0.250000,
                'Superficie Construida'        => 0.180000,
            ],
        ];

        foreach ($map as $code => $parameterMap) {
            $tertiaryCategory = TertiaryCategory::where('code', $code)->first();

            if (!$tertiaryCategory) {
                $this->command->warn("Categoría terciaria con código {$code} no encontrada. Se omite.");
                continue;
            }

            $syncData = [];

            foreach ($parameterMap as $parameterName => $tariffIndex) {
                $parameter = Parameter::where('name', $parameterName)->first();

                if (!$parameter) {
                    $this->command->warn("Parámetro '{$parameterName}' no encontrado. Se omite en {$code}.");
                    continue;
                }

                $syncData[$parameter->id] = [
                    'tariff_index' => $tariffIndex,
                    'is_required'  => true,
                    'status'       => true,
                ];
            }

            // Ubicación: sin tarifa; zona y municipio obligatorios
            foreach (array_keys(self::ADDRESS_PARAMETERS) as $name) {
                $syncData[Parameter::where('name', $name)->value('id')] = [
                    'tariff_index' => null,
                    'is_required'  => in_array($name, ['Zona', 'Municipio'], true),
                    'status'       => true,
                ];
            }

            $tertiaryCategory->parameters()->syncWithoutDetaching($syncData);
        }
    }
}