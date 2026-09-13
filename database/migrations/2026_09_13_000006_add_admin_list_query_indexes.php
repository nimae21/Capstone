<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indexes used by the paginated admin lists in this checkpoint.
 *
 * orders.created_at                         unfiltered newest-order page
 * orders (sale_type, created_at)            sale-type filter plus newest order
 * products (is_active, product_name)        active product list and name order
 * stocks (variant, archived, created_at)     active batches for one variant
 */
return new class extends Migration
{
    /** @var array<string, array<int, array{columns: array<int, string>, name: string}>> */
    private const INDEXES = [
        'orders' => [
            ['columns' => ['created_at'], 'name' => 'orders_created_at_index'],
            ['columns' => ['sale_type', 'created_at'], 'name' => 'orders_sale_type_created_at_index'],
        ],
        'products' => [
            ['columns' => ['is_active', 'product_name'], 'name' => 'products_active_name_index'],
        ],
        'stocks' => [
            ['columns' => ['product_variant_id', 'is_archived', 'created_at'], 'name' => 'stocks_variant_active_created_index'],
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
