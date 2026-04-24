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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id('stock_movement_id');
            $table->foreignId('stock_id')
                  ->constrained('stocks', 'stock_id')
                  ->onDelete('cascade')
                  ->onUpdate('cascade')
                  ->comment('FK to stocks');
            $table->foreignId('order_item_id')
                  ->nullable()
                  ->constrained('order_items', 'order_item_id')
                  ->onDelete('cascade')
                  ->onUpdate('cascade')
                  ->comment('FK to order_items (nullable for adjustments)');
            $table->integer('quantity');
            $table->string('type'); // 'in', 'out', 'adjustment'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
