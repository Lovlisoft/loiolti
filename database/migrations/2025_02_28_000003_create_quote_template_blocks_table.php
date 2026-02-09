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
        Schema::create('quote_template_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_template_id')
                ->constrained('quote_templates')
                ->cascadeOnDelete();
            $table->string('key');
            $table->longText('content');
            $table->string('condition_type'); // variable_exists, expression, equals
            $table->json('condition_config')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['quote_template_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_template_blocks');
    }
};
