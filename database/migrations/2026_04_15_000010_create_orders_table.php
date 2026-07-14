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
    Schema::create('orders', function (Blueprint $table) {
        $table->id('order_id');

        // User who placed the order
        $table->foreignId('user_id')
              ->constrained()
              ->onDelete('cascade');

        // Order total
        $table->decimal('total_amount', 10, 2)->default(0);

        // Order status
        $table->string('status')->default('pending'); 
        // pending, paid, shipped, completed, cancelled

        // ===== SNAPSHOT ADDRESS =====
        $table->string('full_name');
        $table->string('phone_number');
        $table->string('street');
        $table->string('barangay');
        $table->string('city');
        $table->string('province');
        $table->string('postal_code');

        // Optional (future-proof)
        $table->string('payment_method')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
