<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Team;

class AddCompanySettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $team = Team::all()->first();

        $settings = [
            'base_distance' => 3000,
            'kilometer_price' => 30,
            'location_source' => "25.694905,-100.3520877",
        ];

        foreach ($settings as $key => $setting) {
            $team->updateSetting($key, $setting);
        }
    }
}
