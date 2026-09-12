@once
    <link rel="stylesheet" href="{{ asset('css/hero-carousel.css') }}">
    <script src="{{ asset('js/hero-carousel.js') }}" defer></script>
@endonce
<div class="achilles-hero-carousel" data-hero-carousel role="region" aria-roledescription="carousel" aria-label="Featured footwear">
    <div class="achilles-hero-carousel__slides" aria-live="off">
        @forelse ($products as $product)
            <a class="achilles-hero-carousel__slide {{ $loop->first ? 'is-active' : '' }}"
               href="{{ route('product.show', $product->product_id) }}"
               aria-label="View {{ $product->product_name }}"
               aria-hidden="{{ $loop->first ? 'false' : 'true' }}"
               tabindex="{{ $loop->first ? '0' : '-1' }}" @if (!$loop->first) inert @endif>
                <img src="{{ $product->images->first()?->image_url ?? $fallback }}"
                     data-fallback="{{ $fallback }}" alt="{{ $product->product_name }}"
                     loading="{{ $loop->first ? 'eager' : 'lazy' }}" decoding="async">
            </a>
        @empty
            <div class="achilles-hero-carousel__slide is-active">
                <img src="{{ $fallback }}" alt="Achilles footwear" decoding="async">
            </div>
        @endforelse
    </div>
    @if ($products->count() > 1)
        <div class="achilles-hero-carousel__controls" hidden>
            <button type="button" data-previous aria-label="Previous product">&#8249;</button>
            <div class="achilles-hero-carousel__indicators">
                @foreach ($products as $product)
                    <button type="button" data-slide="{{ $loop->index }}"
                            aria-label="Show {{ $product->product_name }} ({{ $loop->iteration }} of {{ $loop->count }})"
                            aria-current="{{ $loop->first ? 'true' : 'false' }}"><span></span></button>
                @endforeach
            </div>
            <button type="button" data-next aria-label="Next product">&#8250;</button>
            <button type="button" data-pause aria-label="Pause slideshow" aria-pressed="false">&#10074;&#10074;</button>
        </div>
    @endif
</div>
