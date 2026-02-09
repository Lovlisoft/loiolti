<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\QuoteTemplate;
use Illuminate\Database\Seeder;

final class QuoteTemplateSeeder extends Seeder
{
    /**
     * Tres plantillas idénticas al flujo de legacystring.php (speech 1, 2, 3).
     * Misma guía y mismos flujos de respuesta que el script legacy.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Financiamiento 12 pagos (speech 1)',
                'slug' => 'speech-1',
                'content' => '¡Buen día! :), El monumento que le gustó esta hermoso, es el número {{product.name}} de nuestro catálogo, es de {{product.material}}, y aplica para el financiamiento a {{quote.pagos_totales}} {{periodo.plural}} de tan sólo ${{quote.abono}}{{quote.string_pago_inicial}}, o también se lo podemos manejar con descuento especial en 3 pagos mensuales de ${{quote.pago3meses}}. El precio ya incluye: {{product.included}}. También ya se incluye en el precio la correcta y profesional instalación del monumento en su terreno en su panteón. Además, es muy importante que usted sepa que la primera mensualidad es el enganche y ya solo con eso le fabricamos su monumento y ¡¡se lo instalamos!! :). El segundo pago no sería hasta que ya le hayamos instalado el monumento y usted este completamente satisfecho con el y le haya encantado, y los pagos restantes serían {{periodo.singular}} con {{periodo.singular}}, ¡¡está increible!!. ¿Le gustaría que le ayude a elaborar su contrato?',
                'sort_order' => 1,
            ],
            [
                'name' => 'Descuento 10% y financiamiento (speech 2)',
                'slug' => 'speech-2',
                'content' => 'Buen día!  :) El monumento que le  gustó está hermoso, es el numero {{product.name}} de nuestro catalogo, es de {{product.material}}, tiene un costo de ${{quote.precio_lista}} pero por el momento contamos con el 10% de descuento en ese modelo, quedaría en tan solo ${{quote.precio_real}}, pero no se preocupe ya que éste precio se dividiría en {{quote.pagos_totales}} pagos :), se lo podemos financiar en {{quote.pagos_totales}} pequeñísimos pagos {{periodo.periodo_plural}},  los primeros {{quote.pagos_enganche}} serían de tan solo ${{quote.abono_enganche}}{{quote.restante_string}}. El precio incluye: {{product.included}}. También ya se incluye en el precio la correcta y profesional instalación del monumento en su terreno en su panteón. Además es muy importante que usted sepa que el primer pago es el enganche y ya solo con eso le fabricamos su monumento y se lo instalamos!! :). El segundo pago no sería hasta que ya le hayamos instalado el monumento y usted este completamente satisfecho con el y le haya encantado, y los pagos restantes serían {{periodo.singular}} con {{periodo.singular}}! está increible!!. ¿Le gustaría que le ayude a elaborar su contrato?',
                'sort_order' => 2,
            ],
            [
                'name' => 'Promoción desde primer pago (speech 3)',
                'slug' => 'speech-3',
                'content' => 'Buen día!  :) El monumento que le  gustó está hermoso, es el número {{product.name}} de nuestro catálogo, es de {{product.material}}, y aplica para el financiemiento a {{quote.pagos_totales}} {{periodo.plural}} entregandole el monumento desde el primer pago!, ahorita esta en promocion y tiene un costo de ${{quote.precio_real}} pago de contado o tambien podria ser con el plan de pagos a {{quote.pagos_totales}} {{periodo.plural}} que consiste en {{quote.pagos_enganche}} pequeños pagos {{periodo.periodo_plural}} de tan solo ${{quote.abono_enganche}} {{quote.restante_string}}. El precio incluye: {{product.included}}. También ya se incluye en el precio la correcta y profesional instalación del monumento en su terreno en su panteón. Además es muy importante que usted sepa que el primer pago es el enganche y ya solo con eso le fabricamos su monumento y se lo instalamos!! :). El segundo pago no sería hasta que ya le hayamos instalado el monumento y usted este completamente satisfecho con el y le haya encantado, y los pagos restantes serían {{periodo.singular}} con {{periodo.singular}}! está increible!!. ¿Le gustaría que le ayude a elaborar su contrato?',
                'sort_order' => 3,
            ],
        ];

        foreach ($templates as $t) {
            QuoteTemplate::query()->updateOrCreate(
                ['slug' => $t['slug']],
                array_merge($t, ['is_active' => true, 'content_html' => null])
            );
        }
    }
}
