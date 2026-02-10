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
            ['key' => 'product.name', 'name' => 'Nombre del producto', 'source' => 'product', 'data_type' => 'string', 'example_value' => 'Lápida clásica', 'help_text' => 'Nombre del producto tal como está en el catálogo. Origen: producto seleccionado.', 'color' => '#0ea5e9'],
            ['key' => 'product.material', 'name' => 'Material', 'source' => 'product', 'data_type' => 'string', 'example_value' => 'Mármol', 'help_text' => 'Material del producto. Origen: producto seleccionado (relación material).', 'color' => '#0ea5e9'],
            ['key' => 'product.included', 'name' => 'Incluye (descripción)', 'source' => 'product', 'data_type' => 'string', 'example_value' => 'Inscripción, instalación', 'help_text' => 'Texto de lo que incluye el producto. Origen: campo "included" del producto.', 'color' => '#0ea5e9'],
            ['key' => 'product.price', 'name' => 'Precio del producto', 'source' => 'product', 'data_type' => 'currency', 'example_value' => '$12,345.00', 'help_text' => 'Precio base del producto sin envío. Origen: campo price del producto. Formato: moneda.', 'color' => '#06b6d4'],
            ['key' => 'product.trips_required', 'name' => 'Viajes de instalación', 'source' => 'product', 'data_type' => 'number', 'example_value' => '1', 'help_text' => 'Número de viajes necesarios para la instalación. Afecta el cálculo de la tarifa de envío.', 'color' => '#14b8a6'],
            // Quote (calculados)
            ['key' => 'quote.tarifa_envio', 'name' => 'Tarifa de envío', 'source' => 'calculated', 'data_type' => 'currency', 'example_value' => '$450.00', 'help_text' => 'Se calcula según la distancia (Google Distance Matrix) desde el origen configurado hasta la ubicación del cliente. Si la distancia no supera la base configurada, es 0. Fórmula: ceil(distancia_km) × precio_por_km × viajes.', 'color' => '#8b5cf6'],
            ['key' => 'quote.pagos_totales', 'name' => 'Total de pagos', 'source' => 'calculated', 'data_type' => 'number', 'example_value' => '12', 'help_text' => 'Número total de pagos del plan (mensual o semanal según el período elegido). Depende del perfil de cotización y del plan de pago del producto.', 'color' => '#a855f7'],
            ['key' => 'quote.abono', 'name' => 'Abono / mensualidad', 'source' => 'calculated', 'data_type' => 'currency', 'example_value' => '$1,200.00', 'help_text' => 'Monto de cada pago periódico (mensual o semanal). Se calcula según el perfil (speech 1/2/3), plan de pago del producto y tarifa de envío.', 'color' => '#8b5cf6'],
            ['key' => 'quote.pago3meses', 'name' => 'Pago en 3 mensualidades', 'source' => 'calculated', 'data_type' => 'currency', 'example_value' => '$4,800.00', 'help_text' => 'Precio total dividido en 3 pagos. Fórmula: (precio producto + tarifa envío) / 3.', 'color' => '#8b5cf6'],
            ['key' => 'quote.string_pago_inicial', 'name' => 'Texto pago inicial (semanal)', 'source' => 'calculated', 'data_type' => 'string', 'example_value' => ' con un único pago inicial de $1,200.00', 'help_text' => 'Solo se rellena cuando el período es semanal. Añade la frase del pago inicial al texto. En período mensual suele ir vacío.', 'color' => '#6366f1'],
            ['key' => 'quote.precio_real', 'name' => 'Precio real (con envío)', 'source' => 'calculated', 'data_type' => 'currency', 'example_value' => '$12,795.00', 'help_text' => 'Precio del producto + tarifa de envío. Es el total a pagar de contado.', 'color' => '#8b5cf6'],
            ['key' => 'quote.precio_lista', 'name' => 'Precio de lista (antes de descuento)', 'source' => 'calculated', 'data_type' => 'currency', 'example_value' => '$14,216.67', 'help_text' => 'Precio “antes del 10% de descuento”. Se calcula como (precio_real) / 0.9 redondeado hacia arriba. Usado en speech 2 y 3.'],
            ['key' => 'quote.pagos_enganche', 'name' => 'Número de pagos de enganche', 'source' => 'calculated', 'data_type' => 'number', 'example_value' => '3', 'help_text' => 'Cantidad de pagos que corresponden al enganche (según plan de pago del producto o perfil).', 'color' => '#a855f7'],
            ['key' => 'quote.pagos_restantes', 'name' => 'Número de pagos restantes', 'source' => 'calculated', 'data_type' => 'number', 'example_value' => '9', 'help_text' => 'Pagos totales menos pagos de enganche. En plan semanal puede mostrarse en número de semanas.', 'color' => '#a855f7'],
            ['key' => 'quote.abono_enganche', 'name' => 'Monto abono enganche', 'source' => 'calculated', 'data_type' => 'currency', 'example_value' => '$1,500.00', 'help_text' => 'Monto de cada uno de los pagos de enganche. Depende del plan y de si se reparte la tarifa de envío entre los pagos.', 'color' => '#8b5cf6'],
            ['key' => 'quote.restante_string', 'name' => 'Texto restante (financiamiento)', 'source' => 'calculated', 'data_type' => 'string', 'example_value' => ' y los 9 restantes de $1,200.00', 'help_text' => 'Frase que describe los pagos restantes (speech 2 y 3). Se construye según pagos_restantes, abono y string_pago_inicial. Solo se rellena cuando hay pagos restantes.', 'color' => '#6366f1'],
            ['key' => 'quote.primer_pago', 'name' => 'Monto primer pago (enganche)', 'source' => 'calculated', 'data_type' => 'currency', 'example_value' => '$1,500.00', 'help_text' => 'Monto del primer pago o enganche. Usado en el texto de “pago inicial” en planes semanales.'],
            // Periodo (contexto)
            ['key' => 'periodo.plural', 'name' => 'Período plural', 'source' => 'system', 'data_type' => 'string', 'example_value' => 'meses', 'help_text' => 'Según si el cliente eligió mensual o semanal: "meses" o "semanas". Origen: selección del usuario en el cotizador.', 'color' => '#64748b'],
            ['key' => 'periodo.singular', 'name' => 'Período singular', 'source' => 'system', 'data_type' => 'string', 'example_value' => 'mes', 'help_text' => '"mes" o "semana" según el período elegido.', 'color' => '#64748b'],
            ['key' => 'periodo.periodo_plural', 'name' => 'Adjetivo plural (mensuales/semanales)', 'source' => 'system', 'data_type' => 'string', 'example_value' => 'mensuales', 'help_text' => '"mensuales" o "semanales" según el período. Se usa en frases como "12 pagos mensuales".', 'color' => '#64748b'],
        ];

        foreach ($variables as $v) {
            QuoteVariable::query()->updateOrCreate(
                ['key' => $v['key']],
                array_merge($v, [
                    'description' => $v['name'] ?? null,
                    'format' => ($v['data_type'] ?? '') === 'currency' ? 'number_format:2' : null,
                    'default_value' => null,
                    'example_value' => $v['example_value'] ?? null,
                    'help_text' => $v['help_text'] ?? null,
                    'color' => $v['color'] ?? null,
                ])
            );
        }
    }
}
