<?php

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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price');
            $table->foreignId('material_id')
                ->constrained('product_materials');
            $table->foreignId('message_id')
                ->constrained('product_messages');
            $table->text('included')
                ->nullable();
            $table->integer('trips_required')
                ->default(1);
            $table->foreignId('payment_plan_id')
                ->nullable()
                ->constrained('payment_plans');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
