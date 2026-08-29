@extends('layouts.pages')

@section('title', 'Men\'s Collection - ACHILLES')

@section('styles')
<style>
    /* ── Reset / Base ── */
    body {
        background: #ffffff;
    }

    .container {
        max-width: 1280px;
        margin: 0 auto;
        padding: 0 1.5rem;
    }

    /* ── Category Header ── */
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

    /* ── Filter & Sort Bar ── */
    .filter-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 2rem;
        padding: 0.75rem 1.25rem;
        background: #f8fafc;
        border-radius: 1.25rem;
        border: 1px solid #e2e8f0;
    }

    .filter-group {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        align-items: center;
    }

    .filter-select {
        appearance: none;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 2rem;
        padding: 0.4rem 2rem 0.4rem 1rem;
        font-size: 0.85rem;
        font-weight: 500;
        color: #1e293b;
        cursor: pointer;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 0.7rem;
        transition: border-color 0.2s;
    }

    .filter-select:hover,
    .filter-select:focus {
        border-color: #dc2626;
        outline: none;
    }

    .filter-clear {
        color: #dc2626;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        padding: 0.25rem 0.75rem;
        border-radius: 2rem;
        background: #fef2f2;
        transition: background 0.2s;
    }

    .filter-clear:hover {
        background: #fee2e2;
    }

    .sort-group {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .sort-group i {
        color: #64748b;
        font-size: 0.9rem;
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

    .shoe-card h3 {
        font-size: 1.05rem;
        font-weight: 700;
        margin: 1rem 1rem 0.3rem;
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

    /* ── Pagination (compact & modern) ── */
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

        .filter-bar {
            flex-direction: column;
            align-items: stretch;
        }

        .filter-group {
            flex-wrap: wrap;
        }

        .sort-group {
            justify-content: flex-end;
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
        .pagination-dots {
    padding: 0 0.3rem;
    color: #94a3b8;
    font-size: 0.75rem;
    display: flex;
    align-items: center;
}
    }
</style>
@endsection

@section('content')
<div class="container" style="padding-top: 2rem; padding-bottom: 3rem;">

    <!-- Category Header -->
    <div class="category-header">
        <div class="category-title">
            <i class="fas fa-person"></i>
            <span>MEN'S COLLECTION</span>
        </div>
        <div class="category-stats">
            <i class="fas fa-fire"></i>
            <span>{{ $products->total() }}+ Performance Models</span>
        </div>
    </div>

    <!-- Filter & Sort Bar -->
    <div class="filter-bar">
        <div class="filter-group">
            <form method="GET" style="display:flex; gap: 0.75rem; flex-wrap: wrap; align-items:center;">
                <select name="brand" onchange="this.form.submit()" class="filter-select">
                    <option value="">All Brands</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->brand_id }}" {{ request('brand') == $brand->brand_id ? 'selected' : '' }}>
                            {{ $brand->brand_name }}
                        </option>
                    @endforeach
                </select>

                <select name="shoe_type" onchange="this.form.submit()" class="filter-select">
                    <option value="">All Types</option>
                    @foreach($shoeTypes as $shoeType)
                        <option value="{{ $shoeType->shoe_type_id }}" {{ request('shoe_type') == $shoeType->shoe_type_id ? 'selected' : '' }}>
                            {{ $shoeType->shoe_type_name }}
                        </option>
                    @endforeach
                </select>

                @if(request('brand') || request('shoe_type'))
                    <a href="{{ url()->current() }}" class="filter-clear">
                        <i class="fas fa-times-circle"></i> Clear
                    </a>
                @endif
            </form>
        </div>

        <div class="sort-group">
            <i class="fas fa-arrow-down-wide-short"></i>
            <select id="sortSelect" onchange="applySort(this.value)" class="filter-select" style="padding-right: 2rem;">
                <option value="">Featured</option>
                <option value="price-low-high" {{ request('sort') == 'price-low-high' ? 'selected' : '' }}>Price: Low → High</option>
                <option value="price-high-low" {{ request('sort') == 'price-high-low' ? 'selected' : '' }}>Price: High → Low</option>
            </select>
        </div>
    </div>

    <!-- Product Grid -->
    @if($products->count() > 0)
        <div class="product-grid" id="productGrid">
            @foreach($products as $product)
                @php
                    $variant = $product->variants->first();
                    $stock = $variant?->stocks?->sortByDesc('deliver_date')->first();
                    $price = $stock->price ?? 0;
                    $image = $product->images->first()?->image_url ?? 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400';
                    $description = $product->product_description ?? 'Premium performance footwear engineered for the relentless athlete.';

                    $badges = ['LIMITED EDITION', 'BESTSELLER', 'NEW DROP', 'PREMIUM'];
                    $badgeText = $badges[array_rand($badges)];
                @endphp

                <a href="{{ route('product.show', $product->product_id) }}" class="shoe-card" data-price="{{ $price }}" aria-label="View {{ $product->product_name }}">
                    <span class="shoe-badge">{{ $badgeText }}</span>

                    <img class="shoe-image"
                         src="{{ $image }}"
                         alt="{{ $product->product_name }}">

                    <h3>{{ $product->product_name }}</h3>
                    <p class="desc">{{ Str::limit($description, 55) }}</p>
                    <p class="price">₱{{ number_format($price, 2) }}</p>

                    <span class="btn-view">
                        View Product <i class="fas fa-arrow-right"></i>
                    </span>
                </a>
            @endforeach
        </div>

        <!-- Compact Pagination -->
<div class="pagination">
    {{-- Previous --}}
    @if ($products->onFirstPage())
        <span class="page-arrow disabled">
            <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </span>
    @else
        <a href="{{ $products->previousPageUrl() }}" class="page-arrow" rel="prev">
            <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
    @endif

    {{-- Page numbers, limited to 1 on each side of current + first/last --}}
    @foreach ($products->appends(request()->query())->onEachSide(1)->linkCollection() as $item)
        @if(is_string($item))
            <span class="pagination-dots">…</span>
        @else
            @foreach($item as $page => $url)
                @if ($page == $products->currentPage())
                    <span class="active"><span>{{ $page }}</span></span>
                @else
                    <a href="{{ $url }}">{{ $page }}</a>
                @endif
            @endforeach
        @endif
    @endforeach

    {{-- Next --}}
    @if ($products->hasMorePages())
        <a href="{{ $products->nextPageUrl() }}" class="page-arrow" rel="next">
            <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </a>
    @else
        <span class="page-arrow disabled">
            <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </span>
    @endif
</div>

    @else
        <div class="empty-state">
            <i class="fas fa-box-open"></i>
            <h3>No Products Found</h3>
            <p>Check back later for new arrivals!</p>
        </div>
    @endif

</div>

@push('scripts')
<script>
    // Apply sort and preserve pagination
    function applySort(value) {
        const url = new URL(window.location.href);
        if (value) {
            url.searchParams.set('sort', value);
        } else {
            url.searchParams.delete('sort');
        }
        url.searchParams.delete('page'); // reset to page 1
        window.location.href = url.toString();
    }

    // Preserve sort parameter on pagination links
    document.addEventListener('DOMContentLoaded', function() {
        const sortSelect = document.querySelector('#sortSelect');
        const currentSort = sortSelect?.value;

        if (currentSort) {
            document.querySelectorAll('.pagination a:not(.page-arrow)').forEach(link => {
                const href = link.getAttribute('href');
                if (href) {
                    const url = new URL(href, window.location.origin);
                    url.searchParams.set('sort', currentSort);
                    link.setAttribute('href', url.toString());
                }
            });

            // Also for arrow links (prev/next)
            document.querySelectorAll('.pagination .page-arrow').forEach(link => {
                const href = link.getAttribute('href');
                if (href) {
                    const url = new URL(href, window.location.origin);
                    url.searchParams.set('sort', currentSort);
                    link.setAttribute('href', url.toString());
                }
            });
        }
    });
</script>
@endpush
@include('partials.recommendations')
@endsection