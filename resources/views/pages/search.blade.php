@extends('layouts.pages')

@section('title', 'Search Results - ACHILLES')

@section('styles')

<style>
    /* ── Search Results ── */
    .search-container {
        max-width: 1280px;
        margin: 0 auto;
        padding: 2rem 1.5rem 3rem;
    }

    .category-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 2rem;
        padding: 1.25rem 1.5rem;
        background: #fafafc;
        border-radius: 1.5rem;
        border: 1px solid #f0f0f0;
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
        padding: 0.4rem 1rem;
        border-radius: 2rem;
    }

    /* ── Product Grid ── */
    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(min(100%, 280px), 1fr));
        gap: 1.75rem;
        margin-bottom: 3rem;
    }

    .shoe-card {
        display: flex;
        flex-direction: column;
        background: #ffffff;
        border-radius: 1.25rem;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
        border: 1px solid #f0f0f0;
        position: relative;
        color: inherit;
        text-decoration: none;
    }

    .shoe-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 30px -12px rgba(0, 0, 0, 0.12);
        border-color: #fee2e2;
    }

    .shoe-badge {
        position: absolute;
        top: 0.9rem;
        left: 0.9rem;
        background: linear-gradient(135deg, #dc2626, #b91c1c);
        color: #fff;
        font-size: 0.65rem;
        font-weight: 700;
        padding: 0.2rem 0.8rem;
        border-radius: 2rem;
        z-index: 2;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .shoe-image {
        width: 100%;
        aspect-ratio: 1 / 1;
        object-fit: cover;
        transition: transform 0.4s ease;
        background: #fafafc;
    }

    .shoe-card:hover .shoe-image {
        transform: scale(1.03);
    }

    /* ── Brand / Shoe Type Meta ── */
    .shoe-meta {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        margin: 0.85rem 1rem 0;
        flex-wrap: wrap;
    }

    .meta-brand {
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #1e293b;
    }

    .meta-dot {
        width: 3px;
        height: 3px;
        border-radius: 50%;
        background: #cbd5e1;
        flex-shrink: 0;
    }

    .meta-type {
        font-size: 0.7rem;
        font-weight: 600;
        color: #dc2626;
        background: #fef2f2;
        padding: 0.15rem 0.6rem;
        border-radius: 1rem;
        letter-spacing: 0.02em;
    }

    .shoe-card h3 {
        font-size: 1.05rem;
        font-weight: 700;
        margin: 0.35rem 1rem 0.3rem;
        color: #1e293b;
        line-height: 1.3;
    }

    .shoe-card .desc {
        font-size: 0.8rem;
        color: #64748b;
        margin: 0 1rem 0.75rem;
        line-height: 1.4;
        flex: 1;
    }

    .price {
        font-size: 1.25rem;
        font-weight: 800;
        color: #dc2626;
        margin: 0 1rem 0.75rem;
    }

    .btn-view {
        display: block;
        text-align: center;
        background: #f8fafc;
        color: #1e293b;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 0.6rem;
        margin: 0 1rem 1rem;
        border-radius: 2rem;
        transition: all 0.25s ease;
        border: 1px solid #e2e8f0;
    }

    .btn-view:hover {
        background: #dc2626;
        color: #fff;
        border-color: #dc2626;
        transform: translateY(-2px);
    }

    .btn-view i {
        margin-left: 0.4rem;
        transition: transform 0.2s;
    }

    .btn-view:hover i {
        transform: translateX(4px);
    }

    /* ── Pagination ── */
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.2rem;
        margin-top: 1.5rem;
        padding: 0.5rem 0;
    }

    .pagination > a,
    .pagination > span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 1.8rem;
        height: 1.8rem;
        padding: 0 0.4rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 0.4rem;
        text-decoration: none;
        transition: all 0.2s ease;
        color: #475569;
        background: transparent;
        border: 1px solid transparent;
    }

    .pagination a:hover {
        background: #f1f5f9;
        border-color: #e2e8f0;
        color: #dc2626;
    }

    .pagination .active span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 1.8rem;
        height: 1.8rem;
        padding: 0 0.4rem;
        font-size: 0.75rem;
        font-weight: 600;
        background: #dc2626;
        color: #fff;
        border-color: #dc2626;
        border-radius: 0.4rem;
    }

    .pagination .page-arrow {
        min-width: 1.8rem;
        height: 1.8rem;
        padding: 0;
        font-size: 0.65rem;
        border-radius: 0.4rem;
        border: 1px solid #e2e8f0;
        background: #fff;
    }

    .pagination .page-arrow:hover {
        background: #dc2626;
        border-color: #dc2626;
        color: #fff;
    }

    .pagination .page-arrow.disabled {
        opacity: 0.4;
        pointer-events: none;
        background: #f1f5f9;
    }

    .pagination svg {
        width: 12px !important;
        height: 12px !important;
        stroke: currentColor;
        stroke-width: 2;
        fill: none;
    }

    .pagination-dots {
        padding: 0 0.3rem;
        color: #94a3b8;
        font-size: 0.75rem;
        display: flex;
        align-items: center;
    }

    /* ── Empty State ── */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: #f8fafc;
        border-radius: 1.5rem;
        border: 1px dashed #e2e8f0;
    }

    .empty-state i {
        font-size: 4rem;
        color: #cbd5e1;
        margin-bottom: 1rem;
    }

    .empty-state h3 {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        color: #64748b;
    }

    /* ── Responsive ── */
    @media (max-width: 768px) {
        .category-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .product-grid {
            gap: 1rem;
        }

        .category-title span {
            font-size: 1.2rem;
        }

        .pagination {
            gap: 0.15rem;
        }

        .pagination > a,
        .pagination > span {
            min-width: 1.6rem;
            height: 1.6rem;
            font-size: 0.7rem;
        }

        .pagination .page-arrow {
            min-width: 1.6rem;
            height: 1.6rem;
        }

        .pagination .active span {
            min-width: 1.6rem;
            height: 1.6rem;
            font-size: 0.7rem;
        }
    }
</style>

@endsection

@section('content')

<div class="search-container">

```
{{-- Search Header --}}
<div class="category-header">
    <div class="category-title">
        <i class="fas fa-search"></i>
        <span>SEARCH RESULTS</span>
    </div>

    @if($query)
        <div class="category-stats">
            <i class="fas fa-magnifying-glass"></i>
            <span>{{ $products->total() }} results for "{{ $query }}"</span>
        </div>
    @endif
</div>

{{-- No Search Query --}}
@if(!$query)

    <div class="empty-state">
        <i class="fas fa-search"></i>
        <h3>Search for something</h3>
        <p>Use the search bar above to find products.</p>
    </div>

{{-- Search Results --}}
@elseif($products->count() > 0)

    <div class="product-grid">

        @foreach($products as $product)

            @php
                /*
                 * IMPORTANT:
                 * Use the same image_url field as the fixed
                 * Men's Collection page.
                 */
                $image = $product->images->first()?->image_url
                    ?? 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400';

                /*
                 * Use the same price source as the fixed page.
                 */
                $price = $product->display_price ?? 0;

                $description = $product->product_description
                    ?? 'Premium performance footwear engineered for the relentless athlete.';

                $brandName = $product->brand->brand_name ?? null;

                $shoeTypeName = $product->shoeType->shoe_type_name ?? null;

            @endphp

            <a href="{{ route('product.show', ['id' => $product->product_id, 'return_to' => request()->fullUrl()]) }}"
               class="shoe-card"
               aria-label="View {{ $product->product_name }}">

                @if ($product->is_new_arrival)<span class="shoe-badge">NEW</span>@endif

                {{-- Product Image --}}
                <img class="shoe-image"
                     src="{{ $image }}"
                     alt="{{ $product->product_name }}"
                     onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400';">

                {{-- Brand / Shoe Type --}}
                @if($brandName || $shoeTypeName)
                    <div class="shoe-meta">

                        @if($brandName)
                            <span class="meta-brand">{{ $brandName }}</span>
                        @endif

                        @if($brandName && $shoeTypeName)
                            <span class="meta-dot"></span>
                        @endif

                        @if($shoeTypeName)
                            <span class="meta-type">{{ $shoeTypeName }}</span>
                        @endif

                    </div>
                @endif

                {{-- Product Name --}}
                <h3>{{ $product->product_name }}</h3>

                {{-- Description --}}
                <p class="desc">
                    {{ Str::limit($description, 55) }}
                </p>

                {{-- Price --}}
                <p class="price">
                    From ₱{{ number_format($price, 2) }}
                </p>

                {{-- View Product --}}
                <span class="btn-view">
                    View Product <i class="fas fa-arrow-right"></i>
                </span>

            </a>

        @endforeach

    </div>

    {{-- Pagination --}}
    @if($products->hasPages())

        <div class="pagination">

            {{-- Previous --}}
            @if ($products->onFirstPage())

                <span class="page-arrow disabled">
                    <svg viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M15 19l-7-7 7-7"/>
                    </svg>
                </span>

            @else

                <a href="{{ $products->previousPageUrl() }}"
                   class="page-arrow"
                   rel="prev">

                    <svg viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M15 19l-7-7 7-7"/>
                    </svg>

                </a>

            @endif

            {{-- Page Numbers --}}
            @foreach ($products->appends(request()->query())->onEachSide(1)->linkCollection() as $item)

                @if(is_string($item))

                    <span class="pagination-dots">…</span>

                @else

                    @foreach($item as $page => $url)

                        @if ($page == $products->currentPage())

                            <span class="active">
                                <span>{{ $page }}</span>
                            </span>

                        @else

                            <a href="{{ $url }}">{{ $page }}</a>

                        @endif

                    @endforeach

                @endif

            @endforeach

            {{-- Next --}}
            @if ($products->hasMorePages())

                <a href="{{ $products->nextPageUrl() }}"
                   class="page-arrow"
                   rel="next">

                    <svg viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M9 5l7 7-7 7"/>
                    </svg>

                </a>

            @else

                <span class="page-arrow disabled">
                    <svg viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M9 5l7 7 7-7"/>
                    </svg>
                </span>

            @endif

        </div>

    @endif

{{-- No Results --}}
@else

    <div class="empty-state">
        <i class="fas fa-box-open"></i>
        <h3>No Products Found</h3>
        <p>Try a different search term.</p>
    </div>

@endif
```

</div>

@include('partials.recommendations')
@endsection
