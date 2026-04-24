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
        Schema::create('payments', function (Blueprint $table) {
            $table->id('payment_id');
            $table->foreignId('order_id')
                  ->constrained('orders', 'order_id')
                  ->onDelete('cascade')
                  ->onUpdate('cascade')
                  ->comment('FK to orders');
            $table->string('method'); // credit_card, debit_card, gcash, paypal, etc.
            $table->string('status')->default('pending'); // pending, completed, failed, cancelled
            $table->timestamp('payment_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
