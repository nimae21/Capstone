<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id('activity_log_id');

            // Nullable: some events (a failed login attempt, a webhook-triggered
            // change) have no authenticated user to attribute the action to.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // e.g. 'product.updated', 'order.cancelled', 'auth.login'
            $table->string('action');

            // Polymorphic reference to whatever the action was performed on
            // (a Product, an Order, a User) - matches Eloquent's morphTo()
            // naming convention ({relation}_type / {relation}_id).
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();

            // Old/new values for update events; free-form context for others
            // (e.g. a failed login stores the attempted email here).
            $table->json('changes')->nullable();

            $table->string('ip_address', 45)->nullable();

            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
            $table->index('user_id');
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};