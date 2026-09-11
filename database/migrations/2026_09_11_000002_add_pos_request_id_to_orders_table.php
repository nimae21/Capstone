<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->uuid('pos_request_id')->nullable()->unique();
        });
    }

    public function down(): void
    {
        throw new LogicException('Rollback is disabled to preserve POS idempotency records.');
    }
};
