<h2>{{ $product->product_name }}</h2>
<p>{{ $product->product_description }}</p>

<!-- COLOR SELECT -->
<label>Color:</label>
<select id="variantSelect">
    @foreach($product->variants as $variant)
        @php $stock = $variant->stocks->last(); @endphp

        <option 
            value="{{ $variant->product_variant_id }}"
            data-price="{{ $stock->price ?? 0 }}"
            data-stock="{{ $stock->quantity ?? 0 }}"
        >
            {{ $variant->color }} - Size {{ $variant->size }}
        </option>
    @endforeach
</select>

<!-- DISPLAY PRICE -->
<p>Price: <span id="priceDisplay"></span></p>

<!-- DISPLAY STOCK -->
<p>Stock: <span id="stockDisplay"></span></p>

<!-- ADD TO CART -->
<form action="{{ route('cart.add') }}" method="POST">
    @csrf

    <input type="hidden" name="product_variant_id" id="selectedVariant">

    <input type="number" name="quantity" min="1" value="1">

    <button type="submit">Add to Cart</button>
</form>


<script>
const select = document.getElementById('variantSelect');
const priceDisplay = document.getElementById('priceDisplay');
const stockDisplay = document.getElementById('stockDisplay');
const selectedVariant = document.getElementById('selectedVariant');

function updateVariant() {
    const option = select.options[select.selectedIndex];

    priceDisplay.innerText = option.dataset.price;
    stockDisplay.innerText = option.dataset.stock;
    selectedVariant.value = option.value;
}

select.addEventListener('change', updateVariant);

// initialize
updateVariant();
</script>