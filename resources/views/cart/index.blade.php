<h1>Your Cart</h1>

@if($cart && $cart->items->count())

    @php $total = 0; @endphp

    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>Product</th>
                <th>Details</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
        @foreach($cart->items as $item)

            @php
                $variant = $item->variant;
                $product = $variant->product;
                $stock = $variant->stocks->last();

                $subtotal = $item->price * $item->quantity;
                $total += $subtotal;
            @endphp

            <tr>
                <!-- PRODUCT -->
                <td>
                    <strong>{{ $product->product_name }}</strong>
                </td>

                <!-- VARIANT DETAILS -->
                <td>
                    Color: {{ $variant->color }} <br>
                    Size: {{ $variant->size }}
                </td>

                <!-- PRICE -->
                <td>
                    ₱{{ number_format($item->price, 2) }}
                </td>

                <!-- QUANTITY -->
                <td>
                    {{ $item->quantity }}
                </td>

                <!-- SUBTOTAL -->
                <td>
                    ₱{{ number_format($subtotal, 2) }}
                </td>

                <!-- ACTIONS -->
                <td>
                    <!-- INCREASE -->
                    <button 
                        onclick="increaseItem({{ $item->cart_item_id }})"
                        {{ $item->quantity >= $stock->quantity ? 'disabled' : '' }}>
                        +
                    </button>

                    <!-- DECREASE -->
                    <button 
                        onclick="decreaseItem({{ $item->cart_item_id }})"
                        {{ $item->quantity <= 1 ? 'disabled' : '' }}>
                        -
                    </button>

                    <!-- REMOVE -->
                    <button onclick="removeItem({{ $item->cart_item_id }})">
                        Remove
                    </button>
                </td>
            </tr>

        @endforeach
        </tbody>
    </table>

    <h3>Total: ₱{{ number_format($total, 2) }}</h3>

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
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.error) {
            alert(data.error);
        } else {
            location.reload();
        }
    });
}

function decreaseItem(id) {
    fetch(`/cart/item/${id}/decrease`, {
        method: 'PATCH',
        credentials: 'same-origin',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
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