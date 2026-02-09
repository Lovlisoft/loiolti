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
        Schema::create('payment_plans', function (Blueprint $table) {
            $table->id();
            $table->integer('payments');
            $table->integer('down_payments')
                ->nullable();
            $table->decimal('amount');
            $table->decimal('down_amount')
                ->nullable();
            $table->timestamps();

            $table->unique([
                'payments',
                'down_payments',
                'amount',
                'down_amount',
            ], 'unique_pplan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_plans');
    }
};
