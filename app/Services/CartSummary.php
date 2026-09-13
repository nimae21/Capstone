<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CartSummary
{
    public function quantityForUser(int $userId): int
    {
        return (int) DB::table('cart_items')
            ->join('carts', 'carts.cart_id', '=', 'cart_items.cart_id')
            ->where('carts.user_id', $userId)
            ->where('carts.status', 0)
            ->sum('cart_items.quantity');
    }

    /** @return array{quantity: int, total: float} */
    public function forCart(int $cartId): array
    {
        $summary = DB::table('cart_items')
            ->where('cart_id', $cartId)
            ->selectRaw('COALESCE(SUM(quantity), 0) as quantity')
            ->selectRaw('COALESCE(SUM(price * quantity), 0) as total')
            ->first();

        return ['quantity' => (int) $summary->quantity, 'total' => (float) $summary->total];
    }
}
