<h1>Order Details</h1>

<p>Order ID: {{ $order->order_id }}</p>
<p>Total: {{ $order->total_amount }}</p>
<p>Status: {{ $order->status }}</p>

<h3>Items</h3>

@foreach($order->items as $item)
    <p>
        {{ $item->variant->product->product_name }}
        - Qty: {{ $item->quantity }}
        - Price: {{ $item->price }}
    </p>
@endforeach