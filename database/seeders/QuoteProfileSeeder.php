<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\QuoteCalculationRule;
use App\Models\QuoteProfile;
use App\Models\QuoteTemplate;
use Illuminate\Database\Seeder;

final class QuoteProfileSeeder extends Seeder
{
    /**
     * Three quote profiles (legacy speech 1, 2, 3), link calculation rules, and link products by message_id.
     */
    public function run(): void
    {
        $templates = QuoteTemplate::query()->whereIn('slug', ['speech-1', 'speech-2', 'speech-3'])->get()->keyBy('slug');
        $rules = QuoteCalculationRule::query()->get()->keyBy('key');

        $profiles = [
            [
                'name' => 'Speech 1 - Financiamiento 12 pagos',
                'slug' => 'speech-1',
                'quote_template_id' => $templates->get('speech-1')?->id,
                'conditions' => ['legacy_speech' => 1],
                'is_default' => true,
                'rule_keys_order' => ['tarifa_envio', 'pago3meses', 'abono_12_meses', 'string_pago_inicial_semana', 'precio_real'],
            ],
            [
                'name' => 'Speech 2 - Descuento 10%',
                'slug' => 'speech-2',
                'quote_template_id' => $templates->get('speech-2')?->id,
                'conditions' => ['legacy_speech' => 2],
                'is_default' => false,
                'rule_keys_order' => ['tarifa_envio', 'precio_real', 'precio_lista_10_descuento', 'plan_compuesto', 'string_pago_inicial_semana'],
            ],
            [
                'name' => 'Speech 3 - Promoción primer pago',
                'slug' => 'speech-3',
                'quote_template_id' => $templates->get('speech-3')?->id,
                'conditions' => ['legacy_speech' => 3],
                'is_default' => false,
                'rule_keys_order' => ['tarifa_envio', 'precio_real', 'plan_compuesto', 'string_pago_inicial_semana'],
            ],
        ];

        foreach ($profiles as $i => $p) {
            $templateId = $p['quote_template_id'];
            if (! $templateId) {
                continue;
            }
            $profile = QuoteProfile::query()->updateOrCreate(
                ['slug' => $p['slug']],
                [
                    'name' => $p['name'],
                    'quote_template_id' => $templateId,
                    'conditions' => $p['conditions'],
                    'is_default' => $p['is_default'],
                ]
            );

            $ruleKeys = $p['rule_keys_order'] ?? [];
            $sync = [];
            foreach ($ruleKeys as $order => $key) {
                $rule = $rules->get($key);
                if ($rule) {
                    $sync[$rule->id] = ['execution_order' => $order + 1];
                }
            }
            $profile->calculationRules()->sync($sync);
        }

        $this->linkProductsToProfiles();
    }

    private function linkProductsToProfiles(): void
    {
        $profiles = QuoteProfile::query()
            ->whereIn('slug', ['speech-1', 'speech-2', 'speech-3'])
            ->get()
            ->keyBy(fn (QuoteProfile $p) => (int) str_replace('speech-', '', $p->slug));

        foreach ([1, 2, 3] as $speech) {
            $profile = $profiles->get($speech);
            if ($profile) {
                Product::query()->where('message_id', $speech)->update(['quote_profile_id' => $profile->id]);
            }
        }
    }
}
