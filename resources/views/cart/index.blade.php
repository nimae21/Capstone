<h1>Your Cart</h1>

@if($cart && $cart->items->count())
    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>Product</th>
                <th>Variant</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp

            @foreach($cart->items as $item)
                @php
                    $subtotal = $item->price * $item->quantity;
                    $total += $subtotal;
                @endphp

                <tr>
                    <td>{{ $item->variant->product->product_name }}</td>
                    <td>
                        Size: {{ $item->variant->size }},
                        Color: {{ $item->variant->color }}
                    </td>
                    <td>{{ number_format($item->price, 2) }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3>Total: {{ number_format($total, 2) }}</h3>

@else
    <p>Your cart is empty.</p>
@endif