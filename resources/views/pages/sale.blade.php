@extends('layouts.pages')

@section('title', 'Sale - Up to 50% Off - ACHILLES')

@section('styles')
<style>
    body { background: #ffffff; }
    
    .category-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f0f0f0;
    }
    
    .category-title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .category-title i {
        font-size: 1.75rem;
        color: #dc2626;
    }
    
    .category-title span {
        font-size: 1.5rem;
        font-weight: 800;
        background: linear-gradient(135deg, #1a1a2e, #dc2626);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        letter-spacing: -0.02em;
    }
    
    .category-stats {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: #dc2626;
        background: #fef2f2;
        padding: 0.5rem 1rem;
        border-radius: 2rem;
    }
    
    .filter-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 2rem;
    }
    
    .filter-tabs {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .filter-tag {
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        font-size: 0.8rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #64748b;
    }
    
    .filter-tag:hover, .filter-tag.active {
        background: #dc2626;
        color: white;
        border-color: #dc2626;
    }
    
    .sort-select {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        background: #f8fafc;
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        border: 1px solid #e2e8f0;
    }
    
    .sort-select select {
        border: none;
        background: transparent;
        font-size: 0.875rem;
        font-weight: 500;
        color: #1e293b;
        cursor: pointer;
        outline: none;
    }
    
    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 2rem;
        margin-bottom: 3rem;
    }
    
    .shoe-card {
        background: #ffffff;
        border-radius: 1.5rem;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
        border: 1px solid #f0f0f0;
        position: relative;
    }
    
    .shoe-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 30px -12px rgba(0, 0, 0, 0.1);
        border-color: #fee2e2;
    }
    
    .sale-badge {
        position: absolute;
        top: 1rem;
        left: 1rem;
        background: linear-gradient(135deg, #dc2626, #b91c1c);
        color: white;
        font-size: 0.7rem;
        font-weight: 700;
        padding: 0.25rem 0.75rem;
        border-radius: 2rem;
        z-index: 2;
    }
    
    .shoe-image {
        width: 100%;
        aspect-ratio: 1 / 1;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    
    .shoe-card:hover .shoe-image {
        transform: scale(1.05);
    }
    
    .shoe-card h3 {
        font-size: 1.1rem;
        font-weight: 700;
        margin: 1rem 1rem 0.5rem;
        color: #1e293b;
    }
    
    .price {
        font-size: 1.3rem;
        font-weight: 800;
        color: #dc2626;
        margin: 0 1rem 1rem;
    }
    
    .price small {
        font-size: 0.8rem;
        color: #94a3b8;
        text-decoration: line-through;
        font-weight: 500;
        margin-left: 0.5rem;
    }
    
    .btn-card {
        display: block;
        text-align: center;
        background: #f8fafc;
        color: #1e293b;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.875rem;
        padding: 0.75rem;
        margin: 0 1rem 1rem;
        border-radius: 2rem;
        transition: all 0.3s ease;
        border: 1px solid #e2e8f0;
    }
    
    .btn-card:hover {
        background: #dc2626;
        color: white;
        border-color: #dc2626;
        transform: translateY(-2px);
    }
    
    .container {
        max-width: 1280px;
        margin: 0 auto;
        padding: 0 1.5rem;
    }
    
    @media (max-width: 768px) {
        .category-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
        .filter-bar {
            flex-direction: column;
            align-items: stretch;
        }
        .product-grid {
            gap: 1rem;
        }
    }
</style>
@endsection

@section('content')
<div class="container" style="padding-top: 2rem; padding-bottom: 3rem;">
    
    <div class="category-header">
        <div class="category-title">
            <i class="fas fa-tags"></i>
            <span>SALE COLLECTION</span>
        </div>
        <div class="category-stats">
            <i class="fas fa-fire"></i>
            <span>Up to 50% OFF</span>
        </div>
    </div>
    
    <div class="filter-bar">
        <div class="filter-tabs">
            <span class="filter-tag active" data-filter="all">All</span>
            <span class="filter-tag" data-filter="men">Men</span>
            <span class="filter-tag" data-filter="women">Women</span>
            <span class="filter-tag" data-filter="kids">Kids</span>
        </div>
        <div class="sort-select">
            <i class="fas fa-arrow-down-wide-short"></i>
            <select id="sortSelect">
                <option value="">Featured</option>
                <option value="price-low-high">Price: Low to High</option>
                <option value="price-high-low">Price: High to Low</option>
                <option value="discount">Biggest Discount</option>
            </select>
        </div>
    </div>
    
    <div class="product-grid" id="productGrid">
        @foreach($products as $product)
        @php
            $variant = $product->variants->first();
            $stock = $variant?->stocks?->last();
            $originalPrice = $stock->price ?? 0;
            $discountPercent = rand(15, 50);
            $salePrice = $originalPrice * (1 - $discountPercent / 100);
            $image = $variant->image ?? null;
            $category = $product->category->category_name ?? 'men';
        @endphp
        
        <div class="shoe-card" data-category="{{ strtolower($category) }}" data-price="{{ round($salePrice) }}" data-discount="{{ $discountPercent }}">
            <span class="sale-badge">-{{ $discountPercent }}%</span>
            <img class="shoe-image" src="{{ $image ? asset('storage/' . $image) : 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400' }}" alt="{{ $product->product_name }}">
            <h3>{{ $product->product_name }}</h3>
            <div class="price">₱{{ number_format(round($salePrice), 2) }} <small>₱{{ number_format($originalPrice, 2) }}</small></div>
            <a href="{{ route('product.show', $product->product_id) }}" class="btn-card">Shop Now <i class="fas fa-arrow-right ml-1"></i></a>
        </div>
        @endforeach
    </div>
    
</div>

@push('scripts')
<script>
    const filterTags = document.querySelectorAll('.filter-tag');
    const sortSelect = document.getElementById('sortSelect');
    const grid = document.getElementById('productGrid');
    
    function filterAndSort() {
        const activeFilter = document.querySelector('.filter-tag.active')?.dataset.filter || 'all';
        const sortValue = sortSelect.value;
        
        let cards = Array.from(grid.children);
        
        // Filter
        cards.forEach(card => {
            const category = card.dataset.category;
            if (activeFilter === 'all' || category === activeFilter) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
        
        // Get visible cards for sorting
        let visibleCards = cards.filter(card => card.style.display !== 'none');
        
        // Sort
        if (sortValue === 'price-low-high') {
            visibleCards.sort((a, b) => parseFloat(a.dataset.price) - parseFloat(b.dataset.price));
        } else if (sortValue === 'price-high-low') {
            visibleCards.sort((a, b) => parseFloat(b.dataset.price) - parseFloat(a.dataset.price));
        } else if (sortValue === 'discount') {
            visibleCards.sort((a, b) => parseFloat(b.dataset.discount) - parseFloat(a.dataset.discount));
        }
        
        // Re-append in new order
        visibleCards.forEach(card => grid.appendChild(card));
    }
    
    filterTags.forEach(tag => {
        tag.addEventListener('click', () => {
            filterTags.forEach(t => t.classList.remove('active'));
            tag.classList.add('active');
            filterAndSort();
        });
    });
    
    sortSelect.addEventListener('change', filterAndSort);
</script>
@endpush
@endsection