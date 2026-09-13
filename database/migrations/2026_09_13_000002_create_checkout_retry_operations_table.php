<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checkout_retry_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments', 'payment_id')->cascadeOnDelete();
            $table->uuid('operation_key')->unique();
            $table->string('source_session_id');
            $table->string('target_session_id')->nullable()->unique();
            $table->string('target_payment_intent_id')->nullable();
            $table->text('checkout_url')->nullable();
            $table->string('status', 24)->default('pending');
            $table->unsignedInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->unique(['payment_id', 'source_session_id']);
            $table->index(['status', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkout_retry_operations');
    }
};
