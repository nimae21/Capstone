<style>
    .rec-section {
        max-width: 1280px;
        margin: 0 auto;
        padding: 3rem 1.5rem;
    }

    .rec-header {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        margin-bottom: 1.75rem;
    }

    .rec-header-icon {
        width: 42px;
        height: 42px;
        border-radius: 0.9rem;
        background: linear-gradient(135deg, #dc2626, #7f1d1d);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 0.9rem;
        flex-shrink: 0;
        box-shadow: 0 8px 16px -6px rgba(220, 38, 38, 0.5);
    }

    .rec-title {
        font-size: 1.5rem;
        font-weight: 900;
        letter-spacing: -0.01em;
        background: linear-gradient(135deg, #0a0a0f, #dc2626);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        margin: 0;
    }

    .rec-subtitle {
        font-size: 0.85rem;
        color: #9ca3af;
        margin: 0.15rem 0 0;
    }

    .rec-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.25rem;
    }

    @media (min-width: 640px) {
        .rec-grid { grid-template-columns: repeat(3, 1fr); gap: 1.5rem; }
    }

    @media (min-width: 1024px) {
        .rec-grid { grid-template-columns: repeat(4, 1fr); }
    }

    .rec-card {
        display: block;
        background: #ffffff;
        border-radius: 1.1rem;
        overflow: hidden;
        border: 1px solid #f0f0f0;
        text-decoration: none;
        color: inherit;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
        transition: all 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
    }

    .rec-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 18px 28px -12px rgba(220, 38, 38, 0.18);
        border-color: #fecaca;
    }

    .rec-image-wrap {
        position: relative;
        aspect-ratio: 1 / 1;
        background: linear-gradient(135deg, #fafafa, #f0f0f0);
        overflow: hidden;
    }

    .rec-image-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .rec-card:hover .rec-image-wrap img {
        transform: scale(1.08);
    }

    .rec-image-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #d1d5db;
        font-size: 2.2rem;
    }

    .rec-badge {
        position: absolute;
        top: 0.6rem;
        left: 0.6rem;
        background: rgba(10, 10, 15, 0.72);
        backdrop-filter: blur(4px);
        color: #fff;
        font-size: 0.6rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        padding: 0.25rem 0.65rem;
        border-radius: 40px;
    }

    .rec-body {
        padding: 0.9rem 1rem 1.1rem;
    }

    .rec-brand {
        font-size: 0.62rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #9ca3af;
        font-weight: 700;
        margin: 0;
    }

    .rec-name {
        font-size: 0.88rem;
        font-weight: 700;
        color: #111827;
        margin: 0.3rem 0 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        transition: color 0.2s;
    }

    .rec-card:hover .rec-name {
        color: #dc2626;
    }

    .rec-price-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 0.5rem;
    }

    .rec-price {
        font-size: 1.1rem;
        font-weight: 900;
        color: #dc2626;
        margin: 0;
    }

    .rec-arrow {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: #f9fafb;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
        font-size: 0.65rem;
        transition: all 0.2s;
    }

    .rec-card:hover .rec-arrow {
        background: #dc2626;
        color: #fff;
    }

    .rec-empty {
        border: 1.5px dashed #e5e7eb;
        background: #fafafa;
        border-radius: 1.1rem;
        padding: 3.5rem 1.5rem;
        text-align: center;
    }

    .rec-empty-icon {
        width: 54px;
        height: 54px;
        margin: 0 auto 1rem;
        border-radius: 50%;
        background: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #dc2626;
        font-size: 1.1rem;
    }

    .rec-empty h3 {
        font-weight: 700;
        color: #374151;
        margin: 0 0 0.3rem;
        font-size: 1rem;
    }

    .rec-empty p {
        font-size: 0.85rem;
        color: #9ca3af;
        max-width: 340px;
        margin: 0 auto;
    }
</style>

<div class="rec-section">

    @if($recommendations->isNotEmpty())

        <div class="rec-header">
            <div class="rec-header-icon"><i class="fas fa-star"></i></div>
            <div>
                <h2 class="rec-title">You Might Like This</h2>
                <p class="rec-subtitle">Picked based on what you've been browsing</p>
            </div>
        </div>

        <div class="rec-grid">
            @foreach($recommendations as $product)
                <a href="{{ route('product.show', $product->product_id) }}" class="rec-card">

                    <div class="rec-image-wrap">
                        @if($product->images->first())
                            <img src="{{ $product->images->first()->image_url }}" alt="{{ $product->product_name }}">
                        @else
                            <div class="rec-image-placeholder"><i class="fas fa-shoe-prints"></i></div>
                        @endif
                        <span class="rec-badge">For You</span>
                    </div>

                    <div class="rec-body">
                        <p class="rec-brand">{{ $product->brand->brand_name ?? 'Achilles' }}</p>
                        <h3 class="rec-name">{{ $product->product_name }}</h3>
                        <div class="rec-price-row">
                            <p class="rec-price">₱{{ number_format($product->display_price, 2) }}</p>
                            <span class="rec-arrow"><i class="fas fa-arrow-right"></i></span>
                        </div>
                    </div>

                </a>
            @endforeach
        </div>

    @else

        <div class="rec-header">
            <div class="rec-header-icon"><i class="fas fa-star"></i></div>
            <div>
                <h2 class="rec-title">Recommended For You</h2>
                <p class="rec-subtitle">Picked based on what you've been browsing</p>
            </div>
        </div>

        <div class="rec-empty">
            <div class="rec-empty-icon"><i class="fas fa-magic"></i></div>
            <h3>No recommendations yet</h3>
            <p>Browse a few products or add something to your cart, and we'll start tailoring picks just for you.</p>
        </div>

    @endif

</div>