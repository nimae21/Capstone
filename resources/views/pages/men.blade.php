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
    @for ($i = 0; $i < 24; $i++)
    <div class="shoe-card" data-category="{{ ['running','basketball','trail','training','casual'][$i % 5] }}" data-price="{{ 6700 + ($i % 15) * 280 }}">
        @if($i < 8)
        <span class="shoe-badge">NEW</span>
        @endif
        <img class="shoe-image" src="https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?w=400" alt="shoe">
        <h3>{{ ['Alpha Force', 'Rock Grid', 'Court Phantom', 'Urban Walk', 'Max Train', 'Lifestyle Pro', 'Rapid Strike', 'Terra Grip', 'Carbon X', 'Hyper Jump', 'Wildfire', 'Zenith Run'][$i % 12] }} {{ floor($i / 12) + 1 }}</h3>
        <div class="shoe-category" style="color:#e53e3e;">men · {{ ['running','basketball','trail','training','casual'][$i % 5] }}</div>
        <div class="price">₱{{ number_format(6700 + ($i % 15) * 280, 2) }}</div>
       
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