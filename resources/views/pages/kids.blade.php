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
    @for ($i = 0; $i < 24; $i++)
    <div class="shoe-card" data-category="{{ ['toddler','little kids','big kids','running','play'][$i % 5] }}" data-price="{{ 3300 + ($i % 12) * 220 }}">
        @if($i < 8)
        <span class="shoe-badge">NEW</span>
        @endif
        <img class="shoe-image" src="https://images.unsplash.com/photo-1514989940723-e8e51635b782?w=400" alt="shoe">
        <h3>{{ ['Mini Grip', 'Blaze Lit', 'Fusion Jr', 'Wild Run', 'Kid Stomp', 'Speed Jr', 'Play Day', 'Rapid Kid', 'Tiny Runner', 'Little Hero', 'Jump Star', 'Fun Sprint'][$i % 12] }} {{ floor($i / 12) + 1 }}</h3>
        <div class="shoe-category" style="color:#e53e3e;">kids · {{ ['toddler','little kids','big kids','running','play'][$i % 5] }}</div>
        <div class="price">₱{{ number_format(3300 + ($i % 12) * 220, 2) }}</div>
       
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