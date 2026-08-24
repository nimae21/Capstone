<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Support\Facades\Cache;

class ActivityTrackingService
{
    public function logView(User $user, Product $product): void
    {
        $this->log($user, $product, 'view');
    }

    public function logSearch(User $user, Product $product): void
    {
        $this->log($user, $product, 'search');
    }

    public function logAddToCart(User $user, Product $product): void
    {
        $this->log($user, $product, 'add_to_cart');
    }

    private function log(User $user, Product $product, string $type): void
    {
        UserActivity::create([
            'user_id' => $user->id,
            'product_id' => $product->product_id,
            'activity_type' => $type,
        ]);

        if ($type !== 'view') {
            Cache::forget("recommendations.user.{$user->id}.8");
        }
    }
}
