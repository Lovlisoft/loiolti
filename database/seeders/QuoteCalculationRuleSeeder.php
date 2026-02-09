<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\QuoteCalculationRule;
use Illuminate\Database\Seeder;

final class QuoteCalculationRuleSeeder extends Seeder
{
    /**
     * Calculation rules replicating legacy cotizarString logic.
     * formula stores intent/description; engine will implement evaluation.
     */
    public function run(): void
    {
        $rules = [
            [
                'key' => 'tarifa_envio',
                'name' => 'Tarifa de envío',
                'execution_order' => 10,
                'formula_type' => 'api',
                'formula' => 'distance_matrix(origin, location) -> if distance > 30000 then ceil(distance/1000)*30*product.trips_required else 0',
                'conditions' => null,
                'output_variable_key' => 'quote.tarifa_envio',
            ],
            [
                'key' => 'pago3meses',
                'name' => 'Pago en 3 meses',
                'execution_order' => 20,
                'formula_type' => 'expression',
                'formula' => '(product.price + quote.tarifa_envio) / 3',
                'conditions' => ['profile' => 'speech-1'],
                'output_variable_key' => 'quote.pago3meses',
            ],
            [
                'key' => 'abono_12_meses',
                'name' => 'Abono plan 12 meses (speech 1)',
                'execution_order' => 30,
                'formula_type' => 'expression',
                'formula' => '((product.precio12 or product.price/12)*12 + quote.tarifa_envio) / 12; if periodo==semana then pagos_totales=ceil(11*4.3333), abono=(abono*11)/pagos_totales',
                'conditions' => ['profile' => 'speech-1'],
                'output_variable_key' => 'quote.abono',
            ],
            [
                'key' => 'string_pago_inicial_semana',
                'name' => 'Texto pago inicial (período semanal)',
                'execution_order' => 40,
                'formula_type' => 'conditional',
                'formula' => 'if periodo==semana then " con un único pago inicial de $"+format(quote.primer_pago)',
                'conditions' => ['context_periodo' => 'semana'],
                'output_variable_key' => 'quote.string_pago_inicial',
            ],
            [
                'key' => 'precio_real',
                'name' => 'Precio real con envío',
                'execution_order' => 50,
                'formula_type' => 'expression',
                'formula' => 'product.price + quote.tarifa_envio',
                'conditions' => null,
                'output_variable_key' => 'quote.precio_real',
            ],
            [
                'key' => 'precio_lista_10_descuento',
                'name' => 'Precio de lista (antes 10% descuento)',
                'execution_order' => 60,
                'formula_type' => 'expression',
                'formula' => 'ceil((product.price + quote.tarifa_envio) / 0.9)',
                'conditions' => ['profile' => 'speech-2'],
                'output_variable_key' => 'quote.precio_lista',
            ],
            [
                'key' => 'plan_compuesto',
                'name' => 'Cálculo plan compuesto (enganche + restantes)',
                'execution_order' => 70,
                'formula_type' => 'expression',
                'formula' => 'From payment_plan: pagos_totales, pagos_enganche, mensualidad_enganche; pagos_restantes=pagos_totales-pagos_enganche; monto_restante=product.price-(pagos_enganche*mensualidad_enganche); mensualidad=monto_restante/pagos_restantes; distribute tarifa_envio; if periodo==semana convert to weekly payments',
                'conditions' => ['profile' => ['speech-2', 'speech-3']],
                'output_variable_key' => null,
            ],
        ];

        foreach ($rules as $r) {
            QuoteCalculationRule::query()->updateOrCreate(
                ['key' => $r['key']],
                array_merge($r, ['conditions' => $r['conditions'] ?? null])
            );
        }
    }
}
