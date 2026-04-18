<h2>Order Details</h2>

<p><strong>Order ID:</strong> {{ $order->order_id }}</p>
<p><strong>Name:</strong> {{ $order->full_name }}</p>
<p><strong>Address:</strong> 
    {{ $order->street }}, 
    {{ $order->barangay }}, 
    {{ $order->city }}, 
    {{ $order->province }}
</p>

<hr>

<h3>Items</h3>

@foreach($order->items as $item)
    <div style="margin-bottom:10px;">
        <p>
            {{ $item->variant->product->name ?? 'Product' }}
        </p>
        <p>Qty: {{ $item->quantity }}</p>
        <p>Price: ₱{{ $item->price }}</p>
    </div>
@endforeach