<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mobile_push_deliveries', function (Blueprint $table) {
            // Links a phone alert back to the in-app notification row so tapping
            // the push can mark the matching notification as read.
            $table->uuid('notification_id')->nullable();
            $table->json('data')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('mobile_push_deliveries', function (Blueprint $table) {
            $table->dropColumn(['notification_id', 'data']);
        });
    }
};