@extends('layouts.pages')

@section('title', 'Kids\' Collection')

@section('content')
<div class="category-header container">
    <div class="category-title">
        <i class="fas fa-child"></i>
        <span>KIDS' // 100+ MODELS</span>
    </div>
    <div class="category-stats"><i class="fas fa-smile"></i> Playful collection</div>
</div>

<div class="filter-bar">
    <div class="filter-tabs">
        <span class="filter-tag active" onclick="filterProducts('all')"><i class="fas fa-baby"></i> All</span>
        <span class="filter-tag" onclick="filterProducts('toddler')"><i class="fas fa-baby-carriage"></i> Toddler</span>
        <span class="filter-tag" onclick="filterProducts('little kids')"><i class="fas fa-child"></i> Little kids</span>
        <span class="filter-tag" onclick="filterProducts('big kids')"><i class="fas fa-person-walking"></i> Big kids</span>
        <span class="filter-tag" onclick="filterProducts('running')"><i class="fas fa-person-running"></i> Running</span>
    </div>
    <div class="sort-select">
        <i class="fas fa-arrow-down-wide-short"></i>
        <select id="sortSelect" onchange="sortProducts(this.value)">
            <option value="newest">Newest</option>
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