@extends('layouts.pages')

@section('title', 'Kids\' Collection - ACHILLES')

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
        justify-content: flex-end;
        margin-bottom: 2rem;
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
    
    .sort-select i {
        color: #64748b;
        font-size: 0.875rem;
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
    
    .shoe-badge {
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
        letter-spacing: 0.02em;
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
    
    .shoe-card p {
        font-size: 0.8rem;
        color: #64748b;
        margin: 0 1rem 0.75rem;
        line-height: 1.4;
    }
    
    .price {
        font-size: 1.3rem;
        font-weight: 800;
        color: #dc2626;
        margin: 0 1rem 1rem;
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
    
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
        margin-top: 2rem;
        padding: 1rem 0;
    }
    
    .pagination a, .pagination span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2rem;
        height: 2rem;
        padding: 0 0.5rem;
        font-size: 0.8rem;
        font-weight: 500;
        border-radius: 0.5rem;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    
    .pagination a {
        color: #64748b;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
    }
    
    .pagination a:hover {
        background: #dc2626;
        color: white;
        border-color: #dc2626;
    }
    
    .pagination .active span {
        background: #dc2626;
        color: white;
        border-color: #dc2626;
    }
    
    .pagination svg {
        width: 14px;
        height: 14px;
    }
    
    .container {
        max-width: 1280px;
        margin: 0 auto;
        padding: 0 1.5rem;
    }
    
    .empty-state {
        text-align: center;
        padding: 4rem;
        background: #f8fafc;
        border-radius: 1.5rem;
    }
    
    @media (max-width: 768px) {
        .category-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
        .product-grid {
            gap: 1rem;
        }
        .category-title span {
            font-size: 1.2rem;
        }
    }
</style>
@endsection

@section('content')
<div class="container" style="padding-top: 2rem; padding-bottom: 3rem;">
    
    <div class="category-header">
        <div class="category-title">
            <i class="fas fa-child"></i>
            <span>KIDS' COLLECTION</span>
        </div>
        <div class="category-stats">
            <i class="fas fa-smile"></i>
            <span>{{ $products->total() }}+ Playful Styles</span>
        </div>
    </div>
    
    <div class="filter-bar">
        <div class="sort-select">
            <i class="fas fa-arrow-down-wide-short"></i>
            <select id="sortSelect" onchange="applySort(this.value)">
                <option value="">Featured</option>
                <option value="price-low-high" {{ request('sort') == 'price-low-high' ? 'selected' : '' }}>Price: Low to High</option>
                <option value="price-high-low" {{ request('sort') == 'price-high-low' ? 'selected' : '' }}>Price: High to Low</option>
            </select>
        </div>
    </div>
    
    @if($products->count() > 0)
    <div class="product-grid">
        @foreach($products as $product)
        @php
            $variant = $product->variants->first();
            $stock = $variant?->stocks?->last();
            $price = $stock->price ?? 0;
            $image = $variant->image ?? null;
            $description = $product->product_description ?? 'Durable and comfortable shoes for active kids.';
            $badges = ['LIMITED EDITION', 'BESTSELLER', 'NEW DROP', 'PLAYFUL'];
            $badgeText = $badges[array_rand($badges)];
        @endphp
        
        <div class="shoe-card" data-price="{{ $price }}">
            <span class="shoe-badge">{{ $badgeText }}</span>
            <img class="shoe-image" src="{{ $image ? asset('storage/' . $image) : 'https://images.unsplash.com/photo-1514989940723-e8e51635b782?w=400' }}" alt="{{ $product->product_name }}">
            <h3>{{ $product->product_name }}</h3>
            <p>{{ Str::limit($description, 55) }}</p>
            <p class="price">₱{{ number_format($price, 2) }}</p>
            <a href="{{ route('product.show', $product->product_id) }}" class="btn-card">View Product <i class="fas fa-arrow-right ml-1"></i></a>
        </div>
        @endforeach
    </div>
    
    <div class="pagination">
        @if ($products->onFirstPage())
            <span class="disabled" style="opacity: 0.5;"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></span>
        @else
            <a href="{{ $products->previousPageUrl() }}"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></a>
        @endif
        
        @foreach ($products->getUrlRange(1, $products->lastPage()) as $page => $url)
            @if ($page == $products->currentPage())
                <span class="active"><span>{{ $page }}</span></span>
            @else
                <a href="{{ $url }}">{{ $page }}</a>
            @endif
        @endforeach
        
        @if ($products->hasMorePages())
            <a href="{{ $products->nextPageUrl() }}"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></a>
        @else
            <span class="disabled" style="opacity: 0.5;"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></span>
        @endif
    </div>
    
    @else
    <div class="empty-state">
        <i class="fas fa-box-open"></i>
        <h3 class="font-bold text-gray-800 mb-2">No Products Found</h3>
        <p class="text-gray-500">Check back later for new arrivals!</p>
    </div>
    @endif
    
</div>

@push('scripts')
<script>
function applySort(value) {
    const url = new URL(window.location.href);
    if (value) { url.searchParams.set('sort', value); } 
    else { url.searchParams.delete('sort'); }
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

document.addEventListener('DOMContentLoaded', function() {
    const paginationLinks = document.querySelectorAll('.pagination a');
    const currentSort = document.querySelector('#sortSelect')?.value;
    paginationLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href && currentSort) {
            const url = new URL(href, window.location.origin);
            url.searchParams.set('sort', currentSort);
            link.setAttribute('href', url.toString());
        }
    });
});
</script>
@endpush
@endsection