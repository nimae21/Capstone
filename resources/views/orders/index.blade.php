<h1>My Orders</h1>

@foreach($orders as $order)
    <div style="border:1px solid #ccc; margin:10px; padding:10px;">
        <p>Order #{{ $order->order_id }}</p>
        <p>Total: {{ $order->total_amount }}</p>
        <p>Status: {{ $order->status }}</p>

        <a href="{{ route('orders.show', $order->order_id) }}">
            View Details
        </a>
    </div>
@endforeach