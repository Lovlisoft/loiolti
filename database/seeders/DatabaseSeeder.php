<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Order respects dependencies: user/team → settings → legacy items → quote (variables → context → templates → blocks → rules → profiles) → products.
     */
    public function run(): void
    {
        User::factory()->withPersonalTeam()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->call([
            AddCompanySettingsSeeder::class,
            ImportLegacyItemsSeeder::class,
            QuoteVariableSeeder::class,
            QuoteContextSeeder::class,
            QuoteTemplateSeeder::class,
            QuoteTemplateBlockSeeder::class,
            QuoteCalculationRuleSeeder::class,
            QuoteProfileSeeder::class,
            LoadProductsFromLegacyDatabase::class,
        ]);
    }
}
