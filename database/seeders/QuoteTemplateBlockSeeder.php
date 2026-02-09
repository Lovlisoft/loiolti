<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\QuoteTemplate;
use App\Models\QuoteTemplateBlock;
use Illuminate\Database\Seeder;

final class QuoteTemplateBlockSeeder extends Seeder
{
    /**
     * Conditional blocks: string_pago_inicial (when periodo=semana), restante_string (when pagos_enganche < pagos_totales).
     */
    public function run(): void
    {
        $templates = QuoteTemplate::query()->whereIn('slug', ['speech-1', 'speech-2', 'speech-3'])->get();
        $bySlug = $templates->keyBy('slug');

        $blocks = [
            // Speech 1: solo string_pago_inicial (semana)
            'speech-1' => [
                [
                    'key' => 'string_pago_inicial',
                    'content' => ' con un único pago inicial de ${{quote.primer_pago}}',
                    'condition_type' => 'equals',
                    'condition_config' => ['context_key' => 'periodo', 'value' => 'semana'],
                    'sort_order' => 1,
                ],
            ],
            // Speech 2: string_pago_inicial + restante_string
            'speech-2' => [
                [
                    'key' => 'string_pago_inicial',
                    'content' => ' con un único pago inicial de ${{quote.primer_pago}}',
                    'condition_type' => 'equals',
                    'condition_config' => ['context_key' => 'periodo', 'value' => 'semana'],
                    'sort_order' => 1,
                ],
                [
                    'key' => 'restante_string',
                    'content' => ' y los {{quote.pagos_restantes}} restantes de  ${{quote.abono}}{{quote.string_pago_inicial}}',
                    'condition_type' => 'expression',
                    'condition_config' => ['expression' => 'quote.pagos_enganche < quote.pagos_totales'],
                    'sort_order' => 2,
                ],
            ],
            // Speech 3: string_pago_inicial + restante_string (otro texto)
            'speech-3' => [
                [
                    'key' => 'string_pago_inicial',
                    'content' => ' con un único pago inicial de ${{quote.primer_pago}}',
                    'condition_type' => 'equals',
                    'condition_config' => ['context_key' => 'periodo', 'value' => 'semana'],
                    'sort_order' => 1,
                ],
                [
                    'key' => 'restante_string',
                    'content' => ' y {{quote.pagos_restantes}} pagos aún más bajos de ${{quote.abono}}{{quote.string_pago_inicial}}',
                    'condition_type' => 'expression',
                    'condition_config' => ['expression' => 'quote.pagos_enganche < quote.pagos_totales'],
                    'sort_order' => 2,
                ],
            ],
        ];

        foreach ($blocks as $slug => $templateBlocks) {
            $template = $bySlug->get($slug);
            if (! $template) {
                continue;
            }
            foreach ($templateBlocks as $b) {
                QuoteTemplateBlock::query()->updateOrCreate(
                    [
                        'quote_template_id' => $template->id,
                        'key' => $b['key'],
                    ],
                    [
                        'content' => $b['content'],
                        'condition_type' => $b['condition_type'],
                        'condition_config' => $b['condition_config'],
                        'sort_order' => $b['sort_order'],
                    ]
                );
            }
        }
    }
}
