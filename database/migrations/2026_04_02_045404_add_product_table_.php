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
            $table->id('product_id'); // custom primary key

            // Foreign key columns
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('brand_id');

            $table->string('product_name');
            $table->text('product_description')->nullable();
            $table->timestamps();

            // Explicit foreign keys with names
            $table->foreign('category_id', 'fk_products_category')
                  ->references('category_id') // matches categories PK
                  ->on('categories')
                  ->onDelete('cascade');

            $table->foreign('brand_id', 'fk_products_brand')
                  ->references('brand_id') // matches brands PK
                  ->on('brands')
                  ->onDelete('cascade');
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