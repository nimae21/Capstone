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
     */
    public function availableQuantity(ProductVariant $variant): int
    {
        return (int) $variant->stocks()
            
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

    public function currentPrice(ProductVariant $variant): float
    {
        $stock = $variant->stocks()
            ->where('is_archived', false)
            ->latest('deliver_date')
            ->first();

        return (float) ($stock->price ?? 0);
    }
}