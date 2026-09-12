<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indexes for the queries the Super Admin mobile console actually runs.
 *
 * Production is PostgreSQL, where - unlike MySQL - a foreign key does not
 * create an index on the referencing column. Several joins and per-row lookups
 * behind the mobile screens were therefore scanning whole tables. Every index
 * below has a named consumer in App\Http\Controllers\Api; nothing is added
 * "just in case".
 *
 *   orders.user_id                          users list: withCount('orders')
 *   orders (sale_type, order_id)            orders list + dashboard: newest first
 *   orders (sale_type, status)              orders list filter + status counts
 *   order_items.order_id                    orders list eager load
 *   payments.order_id                       order detail
 *   stocks (product_variant_id, ...)        inventory list + dashboard totals
 *   approval_requests (requester_id, status) admin detail counts
 *   activity_logs.created_at                audit log date range filter
 *
 * Each index is created only when no index already covers those columns, so
 * running this against a database that came from MySQL (implicit foreign key
 * indexes) does not create duplicates.
 */
return new class extends Migration
{
    /** @var array<string, array<int, array{0: array<int, string>, 1: string}>> */
    private const INDEXES = [
        'orders' => [
            [['user_id'], 'orders_user_id_index'],
            [['sale_type', 'order_id'], 'orders_sale_type_order_id_index'],
            [['sale_type', 'status'], 'orders_sale_type_status_index'],
        ],
        'order_items' => [
            [['order_id'], 'order_items_order_id_index'],
        ],
        'payments' => [
            [['order_id'], 'payments_order_id_index'],
        ],
        'stocks' => [
            [['product_variant_id', 'is_archived', 'deliver_date'], 'stocks_variant_stock_lookup_index'],
        ],
        'approval_requests' => [
            [['requester_id', 'status'], 'approval_requests_requester_status_index'],
        ],
        'activity_logs' => [
            [['created_at'], 'activity_logs_created_at_index'],
        ],
    ];

    public function up(): void
    {
        foreach (self::INDEXES as $table => $indexes) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($indexes as [$columns, $name]) {
                // Already covered (a foreign key index on MySQL, or a previous
                // run of this migration): leaving it alone is the safe answer.
                if (Schema::hasIndex($table, $columns)) {
                    continue;
                }

                Schema::table($table, function (Blueprint $blueprint) use ($columns, $name) {
                    $blueprint->index($columns, $name);
                });
            }
        }
    }

    public function down(): void
    {
        foreach (self::INDEXES as $table => $indexes) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($indexes as [, $name]) {
                // Only drop the indexes this migration named: an implicit
                // MySQL foreign key index must survive a rollback.
                if (! Schema::hasIndex($table, $name)) {
                    continue;
                }

                Schema::table($table, function (Blueprint $blueprint) use ($name) {
                    $blueprint->dropIndex($name);
                });
            }
        }
    }
};
