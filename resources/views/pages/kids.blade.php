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

{{-- SUB-CATEGORIES COMPLETELY REMOVED --}}
<div class="filter-bar">
    <div class="filter-tabs">
        {{-- All filter tags removed --}}
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
        @php
            // Get first variant + stock for preview price
            $firstVariant = $product->variants->first();
            $stock = $firstVariant ? $firstVariant->stocks->last() : null;
            
            // Determine badge text based on product category or random for demo
            $badgeText = '';
            if(isset($product->category) && $product->category == 'new') {
                $badgeText = 'JUST IN';
            } elseif(isset($product->category) && $product->category == 'sale') {
                $badgeText = 'SALE';
            } elseif(isset($product->is_bestseller) && $product->is_bestseller) {
                $badgeText = 'BESTSELLER';
            } else {
                // Fallback: random badge for visual variety (optional)
                $badges = ['LIMITED', 'NEW', 'PREMIUM', 'PLAYFUL'];
                $badgeText = $badges[array_rand($badges)];
            }
        @endphp

        <div class="shoe-card" 
             data-category="all" 
             data-price="{{ $stock->price ?? 0 }}">
            
            {{-- BADGE (like New Drops "JUST IN" style) --}}
            <span class="shoe-badge">{{ $badgeText }}</span>
            
            {{-- PRODUCT IMAGE: Uses the first variant image if available, else kids shoe placeholder --}}
            <img class="shoe-image" 
                 src="{{ $firstVariant && $firstVariant->image ? asset('storage/' . $firstVariant->image) : 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?w=400' }}" 
                 alt="{{ $product->product_name }}">

            <h3>{{ $product->product_name }}</h3>
            
            {{-- OPTIONAL: Show category badge like New Drops (men/women/kids) --}}
            @if(isset($product->gender_category))
            <div class="shoe-category" style="color:#e53e3e;">{{ $product->gender_category }}</div>
            @endif

            <p class="price">
                ₱{{ number_format($stock->price ?? 0, 2) }}
            </p>

            {{-- VIEW PRODUCT BUTTON --}}
            <a href="{{ route('product.show', $product->product_id) }}" class="btn btn-card">
                View Product
            </a>
        </div>
    @endforeach
</div>

@push('scripts')
<script>
    // filterProducts function removed since no filters exist
    // All products are always shown
    
    function sortProducts(sortValue) {
        const grid = document.getElementById('productGrid');
        const cards = Array.from(grid.children);
        
        if (sortValue === 'price-low-high') {
            cards.sort((a, b) => parseFloat(a.dataset.price) - parseFloat(b.dataset.price));
        } else if (sortValue === 'price-high-low') {
            cards.sort((a, b) => parseFloat(b.dataset.price) - parseFloat(a.dataset.price));
        } else if (sortValue === 'newest') {
            // Optional: Reset to original order (newest)
            // You can implement custom logic here if needed
            return;
        }
        
        cards.forEach(card => grid.appendChild(card));
    }
</script>
@endpush
@endsection