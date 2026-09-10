<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('paymongo_payment_intent_id')->nullable()->index();
            $table->json('previous_checkout_session_ids')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['paymongo_payment_intent_id']);
            $table->dropColumn(['paymongo_payment_intent_id', 'previous_checkout_session_ids']);
        });
    }
};
