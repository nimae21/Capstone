<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Deployment order:
 * 1. Deploy the carts:prepare-integrity command.
 * 2. Run it without --apply, review conflicts/back up, then run with --apply.
 * 3. Pause cart writes briefly and run this migration.
 *
 * The migration never deletes or merges production data. It refuses to add
 * constraints until an operator has completed the bounded preparation step.
 */
return new class extends Migration
{
    public function up(): void
    {
        if ($this->duplicateActiveCartsExist() || $this->duplicateItemsExist()) {
            throw new RuntimeException(
                'Cart duplicates remain. Run carts:prepare-integrity, review its output, and apply cleanup before migrating.'
            );
        }

        Schema::table('cart_items', function (Blueprint $table) {
            $table->unique(
                ['cart_id', 'product_variant_id'],
                'cart_items_cart_variant_unique',
            );
        });

        $driver = DB::getDriverName();
        if (in_array($driver, ['pgsql', 'sqlite'], true)) {
            DB::statement(
                'CREATE UNIQUE INDEX carts_one_active_per_user_unique ON carts (user_id) WHERE status = 0'
            );
        } elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
            Schema::table('carts', function (Blueprint $table) {
                $table->unsignedBigInteger('active_user_id')
                    ->nullable()
                    ->storedAs('CASE WHEN status = 0 THEN user_id ELSE NULL END');
                $table->unique('active_user_id', 'carts_one_active_per_user_unique');
            });
        } else {
            throw new RuntimeException("Unsupported database driver for active-cart uniqueness: {$driver}");
        }

        Schema::table('product_images', function (Blueprint $table) {
            $table->index(
                ['product_id', 'is_primary', 'display_order', 'image_id'],
                'product_images_storefront_lookup_index',
            );
        });
        Schema::table('stocks', function (Blueprint $table) {
            $table->index(
                ['product_variant_id', 'is_archived', 'deliver_date', 'stock_id'],
                'stocks_storefront_lookup_index',
            );
        });
    }

    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropIndex('stocks_storefront_lookup_index');
        });
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropIndex('product_images_storefront_lookup_index');
        });

        $driver = DB::getDriverName();
        if (in_array($driver, ['pgsql', 'sqlite'], true)) {
            DB::statement('DROP INDEX IF EXISTS carts_one_active_per_user_unique');
        } elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
            Schema::table('carts', function (Blueprint $table) {
                $table->dropUnique('carts_one_active_per_user_unique');
                $table->dropColumn('active_user_id');
            });
        }

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique('cart_items_cart_variant_unique');
        });
    }

    private function duplicateActiveCartsExist(): bool
    {
        return DB::table('carts')
            ->select('user_id')
            ->where('status', 0)
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->exists();
    }

    private function duplicateItemsExist(): bool
    {
        return DB::table('cart_items')
            ->select('cart_id', 'product_variant_id')
            ->groupBy('cart_id', 'product_variant_id')
            ->havingRaw('COUNT(*) > 1')
            ->exists();
    }
};
