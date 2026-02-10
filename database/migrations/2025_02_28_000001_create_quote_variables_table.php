<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quote_variables', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('source'); // product, quote, system, calculated
            $table->string('data_type'); // string, number, currency, boolean
            $table->string('format')->nullable();
            $table->string('default_value')->nullable();
            $table->string('example_value')->nullable();
            $table->text('help_text')->nullable();
            $table->string('color', 7)->nullable()->comment('Hex color for pill in template editor, e.g. #3b82f6');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_variables');
    }
};
