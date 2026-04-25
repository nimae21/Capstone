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

{{-- SUB-CATEGORIES COMPLETELY REMOVED --}}
<div class="filter-bar">
    <div class="filter-tabs">
        {{-- All filter tags removed --}}
    </div>
    <div class="sort-select">
        <i class="fas fa-arrow-down-wide-short"></i>
        <select id="sortSelect" onchange="applySort(this.value)">
    <option value="">Featured</option>
    <option value="price-low-high">Price: Low to High</option>
    <option value="price-high-low">Price: High to Low</option>
</select>
    </div>
</div>

<div class="product-grid container" id="productGrid">
    @foreach($products as $product)
    @php
        $variant = $product->variants->first();

        // Get latest stock safely
        $stock = $variant?->stocks?->last();

        // Safe values
        $price = $stock->price ?? 0;
        $image = $variant->image ?? null;
        $description = $product->description ?? 'No description available';

        // Badge
        $badges = ['LIMITED', 'BESTSELLER', 'NEW', 'PREMIUM'];
        $badgeText = $badges[array_rand($badges)];
    @endphp

    <div class="shoe-card" data-price="{{ $price }}">

        <span class="shoe-badge">{{ $badgeText }}</span>

        {{-- IMAGE --}}
        <img class="shoe-image"
             src="{{ $image ? asset('storage/' . $image) : 'https://via.placeholder.com/400' }}"
             alt="{{ $product->product_name }}">

        {{-- NAME --}}
        <h3>{{ $product->product_name }}</h3>

        {{-- DESCRIPTION (THIS WAS MISSING) --}}
        <p style="font-size: 12px; color: gray;">
            {{ Str::limit($description, 60) }}
        </p>

        {{-- PRICE --}}
        <p class="price">
            ₱{{ number_format($price, 2) }}
        </p>

        <a href="{{ route('product.show', $product->product_id) }}" class="btn btn-card">
            View Product
        </a>
    </div>
@endforeach
    <div style="margin-top: 20px;">
    {{ $products->links() }}
</div>
</div>

@push('scripts')
<script>
    // All products are always shown
    
    <script>
function applySort(value) {
    const url = new URL(window.location.href);
    
    if (value) {
        url.searchParams.set('sort', value);
    } else {
        url.searchParams.delete('sort');
    }

    window.location.href = url.toString();
}
</script>
</script>
@endpush
@endsection