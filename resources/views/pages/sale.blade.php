@extends('layouts.pages')

@section('title', 'Sale - Up to 50% Off')

@section('content')
<div class="category-header container">
    <div class="category-title">
        <i class="fas fa-tags"></i>
        <span>SALE // UP TO 50% OFF</span>
    </div>
    <div class="category-stats"><i class="fas fa-fire"></i> Limited time offers</div>
</div>

<div class="filter-bar">
    <div class="filter-tabs">
        <span class="filter-tag active" onclick="filterProducts('all')"><i class="fas fa-percent"></i> All sale</span>
        <span class="filter-tag" onclick="filterProducts('men')"><i class="fas fa-person"></i> Men</span>
        <span class="filter-tag" onclick="filterProducts('women')"><i class="fas fa-person-dress"></i> Women</span>
        <span class="filter-tag" onclick="filterProducts('kids')"><i class="fas fa-child"></i> Kids</span>
    </div>
    <div class="sort-select">
        <i class="fas fa-arrow-down-wide-short"></i>
        <select id="sortSelect" onchange="sortProducts(this.value)">
            <option value="biggest">Biggest Discount</option>
            <option value="price-low-high">Price: Low to High</option>
            <option value="price-high-low">Price: High to Low</option>
        </select>
    </div>
</div>

<div class="product-grid container" id="productGrid">
    @php
        $discounts = [30, 40, 25, 20, 35, 15, 28, 32, 45, 38];
    @endphp
    @for ($i = 0; $i < 24; $i++)
    @php
        $origPrice = 5500 + ($i % 30) * 280;
        $discount = $discounts[$i % count($discounts)];
        $salePrice = $origPrice * (1 - $discount / 100);
    @endphp
    <div class="shoe-card" data-category="{{ ['men','women','kids'][$i % 3] }}" data-price="{{ round($salePrice) }}">
        <span class="sale-badge">-{{ $discount }}%</span>
        <img class="shoe-image" src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400" alt="shoe">
        <h3>{{ ['Hyperfuse', 'Wildwood', 'Rapid Jr', 'Downtown', 'Flex 2.0', 'Mini Star', 'Trail Edge', 'Pure Motion', 'Velocity Sale', 'Apex Discount', 'Stride Pro', 'Urban Sale', 'Carbon Deal', 'Neo Flash', 'Quantum Drop', 'Rush Hour', 'Blaze Outlet', 'Fury Clearance', 'Tempest Sale', 'Vortex Deal', 'Ignite Offer', 'Spark Discount', 'Rush Limited', 'Pace Final'][$i] }}</h3>
        <div class="shoe-category" style="color:#e53e3e;">limited sale · {{ ['men','women','kids'][$i % 3] }}</div>
        <div class="price">₱{{ number_format(round($salePrice), 2) }} <small>₱{{ number_format($origPrice, 2) }}</small></div>
        
       
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