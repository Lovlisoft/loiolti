<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

final class ImportLegacyItemsSeeder extends Seeder
{
    /**
     * Import legacy `items` table from SQL dump (database/data/items.sql).
     * Run this before LoadProductsFromLegacyDatabase so the table exists.
     */
    public function run(): void
    {
        $path = database_path('data/items.sql');

        if (! File::exists($path)) {
            $this->command?->warn("SQL file not found: {$path}");
            return;
        }

        $sql = File::get($path);
        $statements = array_filter(
            array_map('trim', explode(";\n", $sql)),
            static fn (string $s): bool => $s !== ''
        );

        foreach ($statements as $statement) {
            if (
                str_starts_with($statement, 'DROP TABLE')
                || str_starts_with($statement, 'CREATE TABLE')
                || str_starts_with($statement, 'INSERT INTO')
            ) {
                DB::unprepared($statement . ';');
            }
        }

        $this->command?->info('Legacy items table imported from database/data/items.sql');
    }
}
