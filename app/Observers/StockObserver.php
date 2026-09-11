<?php

namespace App\Observers;

use App\Models\Stock;
use App\Services\SuperAdminNotifier;
use Illuminate\Support\Facades\Cache;

/**
 * Inventory alerts fire when a variant actually crosses into low or zero
 * stock - not on every sale - so the Super Admin gets signal instead of noise.
 */
class StockObserver
{
    private const LOW_STOCK_THRESHOLD = 5;

    public function updated(Stock $stock): void
    {
        if (! $stock->wasChanged('remaining_quantity')) {
            return;
        }

        $variant = $stock->variant()->with('product')->first();
        if (! $variant || ! $variant->product) {
            return;
        }

        $remaining = (int) $variant->stocks()->where('is_archived', false)->sum('remaining_quantity');
        if ($remaining > self::LOW_STOCK_THRESHOLD) {
            return;
        }

        $level = $remaining <= 0 ? 'out' : 'low';
        // One alert per variant per level per half day, even during a busy sale run.
        if (! Cache::add('inventory-alert:'.$variant->getKey().':'.$level, true, now()->addHours(12))) {
            return;
        }

        app(SuperAdminNotifier::class)->inventoryAlert(
            $level,
            (string) $variant->product->product_name,
            'Size '.$variant->size.' / '.$variant->color,
            max(0, $remaining),
        );
    }
}