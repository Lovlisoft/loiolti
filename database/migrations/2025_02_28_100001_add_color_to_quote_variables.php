<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quote_variables', function (Blueprint $table) {
            $table->string('color', 7)->nullable()->after('help_text')->comment('Hex color for pill in template editor, e.g. #3b82f6');
        });
    }

    public function down(): void
    {
        Schema::table('quote_variables', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
};
