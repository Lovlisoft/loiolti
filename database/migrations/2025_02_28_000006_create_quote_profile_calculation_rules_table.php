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
        Schema::create('quote_profile_calculation_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_profile_id')
                ->constrained('quote_profiles', null, 'qpr_calc_rules_profile_fk')
                ->cascadeOnDelete();
            $table->foreignId('quote_calculation_rule_id')
                ->constrained('quote_calculation_rules', null, 'qpr_calc_rules_rule_fk')
                ->cascadeOnDelete();
            $table->unsignedInteger('execution_order')->default(0);
            $table->timestamps();

            $table->unique(['quote_profile_id', 'quote_calculation_rule_id'], 'qpr_calc_rules_profile_rule_uniq');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_profile_calculation_rules');
    }
};
