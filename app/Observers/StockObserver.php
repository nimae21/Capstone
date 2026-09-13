<?php

namespace App\Observers;

use App\Models\Stock;
use App\Services\SuperAdminNotifier;

/**
 * Inventory alerts fire when a variant actually crosses into low or zero
 * stock - not on every sale - so the Super Admin gets signal instead of noise.
 */
class StockObserver
{
    public function updated(Stock $stock): void
    {
        if (! $stock->wasChanged('remaining_quantity')) {
            return;
        }

        app(SuperAdminNotifier::class)->queueInventoryCheck((int) $stock->product_variant_id);
    }
}
