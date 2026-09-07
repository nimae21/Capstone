<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\StockMovement;

class StockService
{
    /**
     * Total remaining stock across all active batches for a variant.
     *
     * Uses the already-loaded `stocks` relation when available (e.g. after
     * `Product::with('variants.stocks')`) instead of firing a fresh query —
     * calling ->stocks() here would silently bypass eager loading and cause
     * one DB round-trip per variant, which is fine for a handful of variants
     * but becomes a real bottleneck once you're rendering a page (like POS)
     * that lists many products/variants at once.
     */
    public function availableQuantity(ProductVariant $variant): int
    {
        if ($variant->relationLoaded('stocks')) {
            return (int) $variant->stocks
                ->where('is_archived', false)
                ->sum('remaining_quantity');
        }

        return (int) $variant->stocks()
            ->where('is_archived', false)
            ->sum('remaining_quantity');
    }

    public function hasStock(ProductVariant $variant, int $quantity): bool
    {
        return $this->availableQuantity($variant) >= $quantity;
    }

    /**
     * Deduct quantity using FIFO (oldest delivery date first), spreading
     * across multiple batches if needed. Locks rows to prevent a race
     * condition where two concurrent checkouts oversell the same last unit.
     *
     * This intentionally always queries fresh with lockForUpdate() rather
     * than trusting any pre-loaded relation — a locking read has to hit the
     * database to actually take the lock, so there's no eager-load shortcut
     * here the way there is for the read-only display methods above.
     *
     * @throws InsufficientStockException
     */
    public function deduct(ProductVariant $variant, int $quantity, ?int $orderItemId = null): void
    {
        $remaining = $quantity;

        $batches = $variant->stocks()
            ->where('is_archived', false)
            ->where('remaining_quantity', '>', 0)
            ->orderBy('deliver_date')
            ->lockForUpdate()
            ->get();

        if ($batches->sum('remaining_quantity') < $quantity) {
            throw new InsufficientStockException(
                "Insufficient stock for {$variant->product->product_name} ({$variant->size}/{$variant->color}). ".
                "Requested {$quantity}, available {$batches->sum('remaining_quantity')}."
            );
        }

        foreach ($batches as $batch) {
            if ($remaining <= 0) break;

            $take = min($batch->remaining_quantity, $remaining);
            $batch->decrement('remaining_quantity', $take);

            StockMovement::create([
                'stock_id'      => $batch->stock_id,
                'order_item_id' => $orderItemId,
                'quantity'      => $take,
                'type'          => 'out',
            ]);

            $remaining -= $take;
        }
    }

    /**
     * Restore stock for a cancelled order item by reversing the exact
     * StockMovement records created when it was deducted — not a guess
     * at "the latest batch." Keeps batch-level quantities accurate.
     */
    public function restore(OrderItem $orderItem): void
    {
        $movements = StockMovement::where('order_item_id', $orderItem->order_item_id)
            ->where('type', 'out')
            ->get();

        foreach ($movements as $movement) {
            $movement->stock->increment('remaining_quantity', $movement->quantity);

            StockMovement::create([
                'stock_id'      => $movement->stock_id,
                'order_item_id' => $orderItem->order_item_id,
                'quantity'      => $movement->quantity,
                'type'          => 'adjustment',
            ]);
        }
    }

    /**
     * Same eager-load-aware pattern as availableQuantity() — reads from the
     * loaded collection when present instead of issuing a new query.
     */
    public function currentPrice(ProductVariant $variant): float
    {
        if ($variant->relationLoaded('stocks')) {
            $stock = $variant->stocks
                ->where('is_archived', false)
                ->sortByDesc('deliver_date')
                ->first();

            return (float) ($stock->price ?? 0);
        }

        $stock = $variant->stocks()
            ->where('is_archived', false)
            ->latest('deliver_date')
            ->first();

        return (float) ($stock->price ?? 0);
    }
}