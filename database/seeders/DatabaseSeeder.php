<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Laravel\Jetstream\Features;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Order matches migration/model dependencies: user & team → settings → legacy items →
     * quote (variables → context → templates → blocks → rules → profiles) → products.
     * Run migrate:fresh --seed for a clean install with this structure.
     */
    public function run(): void
    {
        $user = User::query()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        if (Features::hasTeamFeatures()) {
            $team = $user->ownedTeams()->create([
                'name' => "Test User's Team",
                'personal_team' => true,
            ]);
            $user->update(['current_team_id' => $team->id]);
        }

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
