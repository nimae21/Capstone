<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mobile_push_devices', function (Blueprint $table) {
            $table->id();
            $table->uuid('installation_id')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('personal_access_token_id')->constrained()->cascadeOnDelete();
            $table->text('token');
            $table->string('token_hash', 64)->unique();
            $table->boolean('enabled')->default(true);
            $table->timestamp('last_seen_at');
            $table->timestamps();
        });
        Schema::create('mobile_push_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('mobile_push_devices')->cascadeOnDelete();
            $table->foreignId('personal_access_token_id')->constrained()->cascadeOnDelete();
            $table->string('event_key', 100);
            $table->string('kind', 30);
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->timestamp('available_at');
            $table->timestamp('locked_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->string('last_error', 100)->nullable();
            $table->timestamps();
            $table->unique(['device_id', 'personal_access_token_id', 'event_key'], 'mobile_push_event_unique');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('mobile_push_deliveries');
        Schema::dropIfExists('mobile_push_devices');
    }
};
