@extends('layouts.pages')

@section('title', 'Men\'s Collection')

@section('content')
<div class="category-header container">
    <div class="category-title">
        <i class="fas fa-person"></i>
        <span>MEN'S // 100+ MODELS</span>
    </div>
    <div class="category-stats"><i class="fas fa-fire"></i> Performance collection</div>
</div>

<div class="filter-bar">
    <div class="filter-tabs">
        <span class="filter-tag active" onclick="filterProducts('all')"><i class="fas fa-shoe-prints"></i> All</span>
        <span class="filter-tag" onclick="filterProducts('running')"><i class="fas fa-person-running"></i> Running</span>
        <span class="filter-tag" onclick="filterProducts('basketball')"><i class="fas fa-basketball-ball"></i> Basketball</span>
        <span class="filter-tag" onclick="filterProducts('trail')"><i class="fas fa-hiking"></i> Trail</span>
        <span class="filter-tag" onclick="filterProducts('training')"><i class="fas fa-dumbbell"></i> Training</span>
        <span class="filter-tag" onclick="filterProducts('casual')"><i class="fas fa-walking"></i> Casual</span>
    </div>
    <div class="sort-select">
        <i class="fas fa-arrow-down-wide-short"></i>
        <select id="sortSelect" onchange="sortProducts(this.value)">
            <option value="featured">Featured</option>
            <option value="price-low-high">Price: Low to High</option>
            <option value="price-high-low">Price: High to Low</option>
        </select>
    </div>
</div>

<div class="product-grid container" id="productGrid">
    @foreach($products as $product)
    <div style="border:1px solid #ccc; margin:10px; padding:10px;">
        <h3>{{ $product->product_name }}</h3>
        <p>{{ $product->product_description }}</p>

        @foreach($product->variants as $variant)
            @php
                $stock = $variant->stocks->last();
            @endphp

            <div style="margin-left:20px;">
                Size: {{ $variant->size }} |
                Color: {{ $variant->color }} <br>

                Price: {{ $stock->price ?? 0 }} |
                Stock: {{ $stock->quantity ?? 0 }}

                <!-- ✅ MOVE FORM HERE -->
                <form action="{{ route('cart.add') }}" method="POST">
                    @csrf

                    <input type="hidden" name="product_variant_id" value="{{ $variant->product_variant_id }}">

                    <input type="number" name="quantity" min="1" value="1" required>

                    <button type="submit">Add to Cart</button>
                </form>
            </div>
        @endforeach
    </div>
@endforeach


</div>

@push('scripts')
<script>
    function filterProducts(category) {
        const cards = document.querySelectorAll('#productGrid .shoe-card');
        cards.forEach(card => {
            const cardCategory = card.dataset.category;
            if (category === 'all' || cardCategory === category) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
        
        document.querySelectorAll('.filter-tag').forEach(tag => tag.classList.remove('active'));
        event.target.classList.add('active');
    }
    
    function sortProducts(sortValue) {
        const grid = document.getElementById('productGrid');
        const cards = Array.from(grid.children);
        
        if (sortValue === 'price-low-high') {
            cards.sort((a, b) => parseFloat(a.dataset.price) - parseFloat(b.dataset.price));
        } else if (sortValue === 'price-high-low') {
            cards.sort((a, b) => parseFloat(b.dataset.price) - parseFloat(a.dataset.price));
        }
        
        cards.forEach(card => grid.appendChild(card));
    }
</script>
@endpush
@endsection