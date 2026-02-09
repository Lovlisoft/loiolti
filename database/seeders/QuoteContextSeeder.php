<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\QuoteContext;
use Illuminate\Database\Seeder;

final class QuoteContextSeeder extends Seeder
{
    /**
     * Context inputs for quote generation (legacy: pago_periodo, location).
     */
    public function run(): void
    {
        $contexts = [
            [
                'key' => 'periodo',
                'name' => 'Período de pago',
                'data_type' => 'string',
                'allowed_values' => ['mes', 'semana'],
                'required' => true,
            ],
            [
                'key' => 'location',
                'name' => 'Coordenadas destino (lat,lng)',
                'data_type' => 'string',
                'allowed_values' => null,
                'required' => false,
            ],
        ];

        foreach ($contexts as $c) {
            QuoteContext::query()->updateOrCreate(
                ['key' => $c['key']],
                $c
            );
        }
    }
}
