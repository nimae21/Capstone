<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const INDEXES = [
        'orders' => [
            ['columns' => ['status', 'created_at'], 'name' => 'orders_status_created_at_index'],
        ],
        'order_items' => [
            ['columns' => ['product_variant_id'], 'name' => 'order_items_variant_index'],
        ],
    ];

    public function up(): void
    {
        foreach (self::INDEXES as $table => $indexes) {
            foreach ($indexes as $index) {
                if (Schema::hasIndex($table, $index['columns'])) {
                    continue;
                }

                Schema::table($table, function (Blueprint $blueprint) use ($index) {
                    $blueprint->index($index['columns'], $index['name']);
                });
            }
        }
    }

    public function down(): void
    {
        foreach (self::INDEXES as $table => $indexes) {
            foreach ($indexes as $index) {
                if (! Schema::hasIndex($table, $index['name'])) {
                    continue;
                }

                Schema::table($table, function (Blueprint $blueprint) use ($index) {
                    $blueprint->dropIndex($index['name']);
                });
            }
        }
    }
};
