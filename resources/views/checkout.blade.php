<h1>Checkout</h1>

<form action="{{ route('checkout.store') }}" method="POST">
    @csrf

    <!-- Address -->
    <h3>Select Address</h3>
    <select name="address_id" required>
        @foreach($addresses as $address)
            <option value="{{ $address->address_id }}">
                {{ $address->full_name }} - {{ $address->city }}
            </option>
        @endforeach
    </select>

    <hr>

    <!-- Items -->
    <h3>Select Items</h3>

    @foreach($variants as $index => $variant)
        <div style="margin-bottom:10px; border:1px solid #ccc; padding:10px;">
            <p>
                Variant ID: {{ $variant->product_variant_id }} |
                Size: {{ $variant->size }} |
                Color: {{ $variant->color }}
            </p>

            <input type="hidden" name="items[{{ $index }}][product_variant_id]"
                   value="{{ $variant->product_variant_id }}">

            <input type="number"
                   name="items[{{ $index }}][quantity]"
                   placeholder="Quantity"
                   min="0">
        </div>
    @endforeach

    <button type="submit">Place Order</button>
</form>