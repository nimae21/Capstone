<?php

use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\UserAddress;

class OrderController extends Controller
{
public function store(Request $request)
{
    DB::transaction(function () use ($request) {

        // 1. Get selected address
        $address = UserAddress::findOrFail($request->address_id);

        // 2. Create order (snapshot address)
        $order = Order::create([
            'user_id' => auth()->id(),
            'total_amount' => 0,
            'status' => 'pending',

            'full_name' => $address->full_name,
            'phone_number' => $address->phone_number,
            'street' => $address->street,
            'barangay' => $address->barangay,
            'city' => $address->city,
            'province' => $address->province,
            'postal_code' => $address->postal_code,
        ]);

        $total = 0;

        // 3. Loop through items
        foreach ($request->items as $item) {

            $variant = ProductVariant::findOrFail($item['product_variant_id']);

            // Get latest stock
            $stock = $variant->stocks()->latest()->first();

            if (!$stock || $stock->quantity < $item['quantity']) {
                throw new \Exception('Not enough stock for variant ID: ' . $variant->product_variant_id);
            }

            // 4. Deduct stock
            $stock->quantity -= $item['quantity'];
            $stock->save();

            // 5. Create order item
            OrderItem::create([
                'order_id' => $order->order_id,
                'product_variant_id' => $variant->product_variant_id,
                'quantity' => $item['quantity'],
                'price' => $stock->price,
            ]);

            // 6. Add to total
            $total += $stock->price * $item['quantity'];
        }

        // 7. Update total
        $order->update([
            'total_amount' => $total
        ]);
    });

    return "Order placed successfully!";
}
}