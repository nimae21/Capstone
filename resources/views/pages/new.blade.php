@extends('layouts.pages')

@section('title', 'New Arrivals')

@section('content')
<div class="category-header container">
    <div class="category-title">
        <i class="fas fa-sparkles"></i>
        <span>NEW // FRESH DROPS</span>
    </div>
    <div class="category-stats"><i class="fas fa-clock"></i> Just landed</div>
</div>

<div class="filter-bar">
    <div class="filter-tabs">
        <span class="filter-tag active" onclick="filterProducts('all')"><i class="fas fa-layer-group"></i> All</span>
        <span class="filter-tag" onclick="filterProducts('men')"><i class="fas fa-person"></i> Men</span>
        <span class="filter-tag" onclick="filterProducts('women')"><i class="fas fa-person-dress"></i> Women</span>
        <span class="filter-tag" onclick="filterProducts('kids')"><i class="fas fa-child"></i> Kids</span>
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
    @for ($i = 0; $i < 20; $i++)
    <div class="shoe-card" data-category="{{ ['men','women','kids'][$i % 3] }}" data-price="{{ 7800 + ($i % 20) * 280 }}">
        <span class="shoe-badge">JUST IN</span>
        <img class="shoe-image" src="https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?w=400" alt="shoe">
        <h3>{{ ['Nova Flow', 'Void Premium', 'Air Jade', 'Kid Spark', 'Force Max', 'Solace 2', 'Zenith X', 'Apex Neo', 'Velocity', 'Stride Pro', 'Fusion Elite', 'Rapid Air', 'Trail Blazer', 'Urban Edge', 'Carbon Lite', 'Hyper Flex', 'Eclipse Run', 'Phantom', 'Stealth', 'Aero Glide'][$i] }}</h3>
        <div class="shoe-category" style="color:#e53e3e;">fresh drop · {{ ['men','women','kids'][$i % 3] }}</div>
        <div class="price">₱{{ number_format(7800 + ($i % 20) * 280, 2) }}</div>
        
    </div>
    @endfor
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