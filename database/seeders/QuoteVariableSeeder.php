<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\QuoteVariable;
use Illuminate\Database\Seeder;

final class QuoteVariableSeeder extends Seeder
{
    /**
     * Variables used in quote templates (legacy cotizarString).
     */
    public function run(): void
    {
        $variables = [
            // Product
            ['key' => 'product.name', 'name' => 'Nombre del producto', 'source' => 'product', 'data_type' => 'string'],
            ['key' => 'product.material', 'name' => 'Material', 'source' => 'product', 'data_type' => 'string'],
            ['key' => 'product.included', 'name' => 'Incluye (descripción)', 'source' => 'product', 'data_type' => 'string'],
            ['key' => 'product.price', 'name' => 'Precio del producto', 'source' => 'product', 'data_type' => 'currency'],
            ['key' => 'product.trips_required', 'name' => 'Viajes de instalación', 'source' => 'product', 'data_type' => 'number'],
            // Quote (calculados)
            ['key' => 'quote.tarifa_envio', 'name' => 'Tarifa de envío', 'source' => 'calculated', 'data_type' => 'currency'],
            ['key' => 'quote.pagos_totales', 'name' => 'Total de pagos', 'source' => 'calculated', 'data_type' => 'number'],
            ['key' => 'quote.abono', 'name' => 'Abono / mensualidad', 'source' => 'calculated', 'data_type' => 'currency'],
            ['key' => 'quote.pago3meses', 'name' => 'Pago en 3 mensualidades', 'source' => 'calculated', 'data_type' => 'currency'],
            ['key' => 'quote.string_pago_inicial', 'name' => 'Texto pago inicial (semanal)', 'source' => 'calculated', 'data_type' => 'string'],
            ['key' => 'quote.precio_real', 'name' => 'Precio real (con envío)', 'source' => 'calculated', 'data_type' => 'currency'],
            ['key' => 'quote.precio_lista', 'name' => 'Precio de lista (antes de descuento)', 'source' => 'calculated', 'data_type' => 'currency'],
            ['key' => 'quote.pagos_enganche', 'name' => 'Número de pagos de enganche', 'source' => 'calculated', 'data_type' => 'number'],
            ['key' => 'quote.pagos_restantes', 'name' => 'Número de pagos restantes', 'source' => 'calculated', 'data_type' => 'number'],
            ['key' => 'quote.abono_enganche', 'name' => 'Monto abono enganche', 'source' => 'calculated', 'data_type' => 'currency'],
            ['key' => 'quote.restante_string', 'name' => 'Texto restante (financiamiento compuesto)', 'source' => 'calculated', 'data_type' => 'string'],
            ['key' => 'quote.primer_pago', 'name' => 'Monto primer pago (enganche inicial)', 'source' => 'calculated', 'data_type' => 'currency'],
            // Periodo (contexto)
            ['key' => 'periodo.plural', 'name' => 'Período plural (meses/semanas)', 'source' => 'system', 'data_type' => 'string'],
            ['key' => 'periodo.singular', 'name' => 'Período singular (mes/semana)', 'source' => 'system', 'data_type' => 'string'],
            ['key' => 'periodo.periodo_plural', 'name' => 'Adjetivo plural (mensuales/semanales)', 'source' => 'system', 'data_type' => 'string'],
        ];

        foreach ($variables as $v) {
            QuoteVariable::query()->updateOrCreate(
                ['key' => $v['key']],
                array_merge($v, [
                    'description' => $v['name'] ?? null,
                    'format' => $v['data_type'] === 'currency' ? 'number_format:2' : null,
                    'default_value' => null,
                ])
            );
        }
    }
}
