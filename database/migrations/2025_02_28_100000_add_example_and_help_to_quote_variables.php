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
            $table->string('example_value')->nullable()->after('default_value');
            $table->text('help_text')->nullable()->after('example_value');
        });
    }

    public function down(): void
    {
        Schema::table('quote_variables', function (Blueprint $table) {
            $table->dropColumn(['example_value', 'help_text']);
        });
    }
};
