<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('background_operations', function (Blueprint $table) {
            $table->id();
            $table->string('operation_key', 100)->unique();
            $table->string('type', 40);
            $table->json('payload');
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->timestamp('available_at');
            $table->timestamp('processed_at')->nullable();
            $table->string('last_error', 255)->nullable();
            $table->timestamps();
            $table->index(['processed_at', 'available_at'], 'background_operations_pending_index');
        });

        Schema::create('inventory_alert_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')
                ->constrained('product_variants', 'product_variant_id')->cascadeOnDelete();
            $table->string('level', 10);
            $table->timestamp('last_notified_at');
            $table->timestamps();
            $table->unique(['product_variant_id', 'level'], 'inventory_alert_variant_level_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_alert_states');
        Schema::dropIfExists('background_operations');
    }
};
