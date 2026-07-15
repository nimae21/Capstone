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
        Schema::create('stocks', function (Blueprint $table) {
        $table->id('stock_id');
        $table->foreignId('product_variant_id')
              ->constrained('product_variants', 'product_variant_id')
              ->onDelete('cascade')
              ->onUpdate('cascade')
              ->comment('FK to product_variants');
        $table->decimal('price', 10, 2);
        $table->unsignedInteger('received_quantity');

        $table->unsignedInteger('remaining_quantity');  
        $table->date('deliver_date');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
