<?php

namespace App\Console\Commands;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PrepareCartIntegrity extends Command
{
    protected $signature = 'carts:prepare-integrity
        {--apply : Merge duplicate records after the audit passes}
        {--max-groups=500 : Refuse to process more than this many duplicate groups in one run}
        {--max-items=5000 : Refuse when a locked merge set contains more item rows}';

    protected $description = 'Audit or safely merge duplicate active carts and cart items before enabling unique indexes';

    public function handle(): int
    {
        $max = max(1, (int) $this->option('max-groups'));
        $activeUsers = DB::table('carts')
            ->select('user_id')
            ->where('status', 0)
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->orderBy('user_id')
            ->limit($max + 1)
            ->pluck('user_id');

        $duplicateItems = DB::table('cart_items')
            ->select('cart_id', 'product_variant_id')
            ->groupBy('cart_id', 'product_variant_id')
            ->havingRaw('COUNT(*) > 1')
            ->orderBy('cart_id')
            ->orderBy('product_variant_id')
            ->limit($max + 1)
            ->get();

        $groups = $activeUsers->count() + $duplicateItems->count();
        $this->line("Duplicate active-cart users: {$activeUsers->count()}");
        $this->line("Duplicate cart-item groups: {$duplicateItems->count()}");

        if ($groups === 0) {
            $this->info('Cart integrity audit passed; no duplicate records remain.');

            return self::SUCCESS;
        }

        if ($groups > $max) {
            $this->error("Refusing to process {$groups} groups; --max-groups is {$max}. Review the volume and rerun with an intentional bound.");

            return self::FAILURE;
        }

        if (! $this->option('apply')) {
            $this->warn('Audit only. Review stock conflicts, back up the database, then rerun with --apply.');

            return self::FAILURE;
        }

        foreach ($activeUsers as $userId) {
            $merged = DB::transaction(fn () => $this->mergeActiveCarts((int) $userId), 3);
            if (! $merged) {
                return self::FAILURE;
            }
        }

        // Active-cart merging can create or remove duplicate item groups, so
        // discover them again and process in deterministic key order.
        $remaining = DB::table('cart_items')
            ->select('cart_id', 'product_variant_id')
            ->groupBy('cart_id', 'product_variant_id')
            ->havingRaw('COUNT(*) > 1')
            ->orderBy('cart_id')
            ->orderBy('product_variant_id')
            ->limit($max + 1)
            ->get();

        if ($remaining->count() > $max) {
            $this->error('Refusing the second pass because its duplicate group count exceeds --max-groups.');

            return self::FAILURE;
        }

        foreach ($remaining as $group) {
            $merged = DB::transaction(
                fn () => $this->mergeItemGroup((int) $group->cart_id, (int) $group->product_variant_id),
                3,
            );
            if (! $merged) {
                return self::FAILURE;
            }
        }

        $activeRemain = DB::table('carts')->where('status', 0)
            ->groupBy('user_id')->havingRaw('COUNT(*) > 1')->exists();
        $itemsRemain = DB::table('cart_items')
            ->groupBy('cart_id', 'product_variant_id')->havingRaw('COUNT(*) > 1')->exists();

        if ($activeRemain || $itemsRemain) {
            $this->error('Duplicate records remain. No unique indexes should be deployed yet.');

            return self::FAILURE;
        }

        $this->info('Cart records are ready for the integrity migration.');

        return self::SUCCESS;
    }

    private function mergeActiveCarts(int $userId): bool
    {
        User::whereKey($userId)->lockForUpdate()->firstOrFail();

        $carts = Cart::where('user_id', $userId)
            ->where('status', 0)
            ->orderBy('cart_id')
            ->lockForUpdate()
            ->get();

        if ($carts->count() < 2) {
            return true;
        }

        $cartIds = $carts->pluck('cart_id');
        $itemQuery = CartItem::whereIn('cart_id', $cartIds);
        if ((clone $itemQuery)->count() > (int) $this->option('max-items')) {
            $this->error("User {$userId}: refusing to lock more than --max-items cart rows.");

            return false;
        }

        $items = $itemQuery
            ->orderBy('product_variant_id')
            ->orderBy('cart_item_id')
            ->lockForUpdate()
            ->get();

        foreach ($items->groupBy('product_variant_id') as $variantId => $variantItems) {
            $quantity = (int) $variantItems->sum('quantity');
            $available = (int) DB::table('stocks')
                ->where('product_variant_id', $variantId)
                ->where('is_archived', false)
                ->sum('remaining_quantity');

            if ($quantity > $available) {
                $this->error("User {$userId}, variant {$variantId}: quantity {$quantity} exceeds available stock {$available}; no carts for this user were changed.");

                return false;
            }
        }

        $canonical = $carts->first();
        foreach ($items->groupBy('product_variant_id') as $variantItems) {
            $keeper = $variantItems->first();
            $keeper->update([
                'cart_id' => $canonical->cart_id,
                'quantity' => (int) $variantItems->sum('quantity'),
            ]);
            CartItem::whereIn('cart_item_id', $variantItems->pluck('cart_item_id')->skip(1))->delete();
        }

        Cart::whereIn('cart_id', $cartIds->skip(1))->delete();

        return true;
    }

    private function mergeItemGroup(int $cartId, int $variantId): bool
    {
        $hint = Cart::whereKey($cartId)->first();
        if (! $hint) {
            return true;
        }

        User::whereKey($hint->user_id)->lockForUpdate()->firstOrFail();
        $cart = Cart::whereKey($cartId)->lockForUpdate()->first();
        if (! $cart) {
            return true;
        }

        $itemQuery = CartItem::where('cart_id', $cartId)
            ->where('product_variant_id', $variantId);
        if ((clone $itemQuery)->count() > (int) $this->option('max-items')) {
            $this->error("Cart {$cartId}, variant {$variantId}: refusing to lock more than --max-items rows.");

            return false;
        }

        $items = $itemQuery
            ->where('product_variant_id', $variantId)
            ->orderBy('cart_item_id')
            ->lockForUpdate()
            ->get();

        if ($items->count() < 2) {
            return true;
        }

        $quantity = (int) $items->sum('quantity');
        if ($cart->status === 0) {
            $available = (int) DB::table('stocks')
                ->where('product_variant_id', $variantId)
                ->where('is_archived', false)
                ->sum('remaining_quantity');

            if ($quantity > $available) {
                $this->error("Cart {$cartId}, variant {$variantId}: quantity {$quantity} exceeds available stock {$available}; this group was not changed.");

                return false;
            }
        }

        $keeper = $items->first();
        $keeper->update(['quantity' => $quantity]);
        CartItem::whereIn('cart_item_id', $items->pluck('cart_item_id')->skip(1))->delete();

        return true;
    }
}
