<h2>{{ $product->product_name }}</h2>
<p>{{ $product->product_description }}</p>

<!-- COLOR SELECT -->
<h4>Color:</h4>
<div id="variant-container">
    @foreach($product->variants as $variant)
        <button 
            class="variant-btn"
            data-id="{{ $variant->product_variant_id }}">
            {{ $variant->color }}
        </button>
    @endforeach
</div>

<!-- SIZE SELECT -->
<h4>Size:</h4>
<div id="size-container">
    <p>Select color first</p>
</div>

<!-- DISPLAY PRICE -->
<p>Price: <span id="priceDisplay">0</span></p>

<!-- DISPLAY STOCK -->
<p>Stock: <span id="stockDisplay">0</span></p>

<!-- ADD TO CART -->
<form action="{{ route('cart.add') }}" method="POST">
    @csrf

    <input type="hidden" name="product_variant_id" id="selectedVariant">
    <input type="hidden" name="size" id="selectedSize">

    <input type="number" name="quantity" min="1" value="1">

    <button type="submit" onclick="return validateSelection()">Add to Cart</button>
</form>

<script>
const variants = @json($product->variants);

let selectedVariant = null;
let selectedSize = null;

/* =========================
   COLOR CLICK
========================= */
document.querySelectorAll('.variant-btn').forEach(button => {
    button.addEventListener('click', function () {

        document.querySelectorAll('.variant-btn')
            .forEach(btn => btn.classList.remove('active'));

        this.classList.add('active');

        selectedVariant = this.dataset.id;
        document.getElementById('selectedVariant').value = selectedVariant;

        renderSizes(selectedVariant);
    });
});

/* =========================
   RENDER SIZES (FROM VARIANT)
========================= */
function renderSizes(variantId) {
    const sizeContainer = document.getElementById('size-container');
    sizeContainer.innerHTML = '';

    const variant = variants.find(v => v.product_variant_id == variantId);

    if (!variant) {
        console.error("Variant not found");
        return;
    }

    // SIZE IS FROM VARIANT (NOT STOCK)
    const button = document.createElement('button');
    button.classList.add('size-btn');

    button.dataset.size = variant.size;

    button.innerText = variant.size;

    sizeContainer.appendChild(button);

    attachSizeEvents();
}

/* =========================
   SIZE CLICK
========================= */
function attachSizeEvents() {
    document.querySelectorAll('.size-btn').forEach(button => {

        button.addEventListener('click', function () {

            document.querySelectorAll('.size-btn')
                .forEach(btn => btn.classList.remove('active'));

            this.classList.add('active');

            selectedSize = this.dataset.size;

            document.getElementById('selectedSize').value = selectedSize;

            // STOCK INFO COMES FROM STOCK TABLE (FIRST RECORD)
            const variant = variants.find(v => v.product_variant_id == selectedVariant);
            const stock = variant.stocks?.[0];

            if (stock) {
                document.getElementById('priceDisplay').innerText = stock.price;
                document.getElementById('stockDisplay').innerText = stock.quantity;
            }
        });
    });
}

/* =========================
   VALIDATION
========================= */
function validateSelection() {
    if (!selectedVariant || !selectedSize) {
        alert('Please select color and size');
        return false;
    }
    return true;
}
</script>