<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

final class QuoteLegacySeeder extends Seeder
{
    /**
     * Seed quote template mechanics to replicate legacy cotizarString behaviour.
     */
    public function run(): void
    {
        $this->call([
            QuoteVariableSeeder::class,
            QuoteContextSeeder::class,
            QuoteTemplateSeeder::class,
            QuoteTemplateBlockSeeder::class,
            QuoteCalculationRuleSeeder::class,
            QuoteProfileSeeder::class,
        ]);
    }
}
