<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('refund_status')->nullable();
            $table->string('paymongo_refund_id')->nullable()->unique();
            $table->unsignedBigInteger('refund_amount')->nullable(); // centavos
            $table->uuid('refund_request_key')->nullable()->unique();
            $table->timestamp('refund_requested_at')->nullable();
            $table->unsignedBigInteger('refund_updated_at')->nullable(); // provider timestamp
            $table->string('refund_error')->nullable();
            $table->index('paymongo_payment_id');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['paymongo_payment_id']);
            $table->dropUnique(['paymongo_refund_id']);
            $table->dropUnique(['refund_request_key']);
            $table->dropColumn([
                'refund_status', 'paymongo_refund_id', 'refund_amount',
                'refund_request_key', 'refund_requested_at', 'refund_updated_at', 'refund_error',
            ]);
        });
    }
};