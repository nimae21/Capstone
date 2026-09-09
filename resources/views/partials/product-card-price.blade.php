<p class="price product-card-price">
    @if($product->display_price !== null)
        From ₱{{ number_format($product->display_price, 2) }}
    @else
        Price unavailable
    @endif
</p>