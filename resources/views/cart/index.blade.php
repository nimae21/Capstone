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
                <th>Actions</th>
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
                    <td><button onclick="increaseItem({{ $item->cart_item_id }})">+</button>
<button onclick="decreaseItem({{ $item->cart_item_id }})">-</button>
<button onclick="removeItem({{ $item->cart_item_id }})">Remove</button></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3>Total: {{ number_format($total, 2) }}</h3>

@else
    <p>Your cart is empty.</p>
@endif

<script>
function increaseItem(id) {
    fetch(`/cart/item/${id}/increase`, {
        method: 'PATCH',
        credentials: 'same-origin',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'

            
        }
    })
    .then(res => res.json())
    .then(data => location.reload());
}

function decreaseItem(id) {
    fetch(`/cart/item/${id}/decrease`, {
        method: 'PATCH',
        credentials: 'same-origin',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => location.reload());
}

function removeItem(id) {
    fetch(`/cart/item/${id}`, {
        method: 'DELETE',
        credentials: 'same-origin',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(res => res.json())
    .then(data => location.reload());
}
</script>