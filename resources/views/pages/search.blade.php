@extends('layouts.pages')

@section('title', 'Search Results - ACHILLES')

@section('content')
<div class="container" style="padding-top: 2rem; padding-bottom: 3rem;">

    <div class="category-header">
        <div class="category-title">
            <i class="fas fa-search"></i>
            <span>SEARCH RESULTS</span>
        </div>
        @if($query)
            <div class="category-stats">
                <i class="fas fa-magnifying-glass"></i>
                <span>{{ $products->total() ?? 0 }} results for "{{ $query }}"</span>
            </div>
        @endif
    </div>

    @if(!$query)
        <div class="empty-state">
            <i class="fas fa-search"></i>
            <h3 class="font-bold text-gray-800 mb-2">Search for something</h3>
            <p class="text-gray-500">Use the search bar above to find products.</p>
        </div>
    @elseif($products->count() > 0)
        <div class="product-grid">
            @foreach($products as $product)
                @php
                    $variant = $product->variants->first();
                    $stock = $variant?->stocks?->sortByDesc('deliver_date')->first();
                    $price = $stock->price ?? 0;
                    $image = $product->images->first()->image_path ?? null;
                @endphp

                <div class="shoe-card">
                    <img class="shoe-image"
                         src="{{ $image ? asset('storage/' . $image) : 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400' }}"
                         alt="{{ $product->product_name }}">
                    <h3>{{ $product->product_name }}</h3>
                    <p style="font-size:0.8rem; color:#64748b; margin: 0 0 0.75rem;">
                        {{ $product->brand->brand_name ?? '' }}
                    </p>
                    <p class="price">₱{{ number_format($price, 2) }}</p>
                    <a href="{{ route('product.show', $product->product_id) }}" class="btn-card">
                        View Product <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            @endforeach
        </div>

        @if($products->hasPages())
            <div class="pagination">
                {{ $products->links() }}
            </div>
        @endif
    @else
        <div class="empty-state">
            <i class="fas fa-box-open"></i>
            <h3 class="font-bold text-gray-800 mb-2">No results found</h3>
            <p class="text-gray-500">Try a different search term.</p>
        </div>
    @endif

</div>
@include('partials.recommendations')
@endsection