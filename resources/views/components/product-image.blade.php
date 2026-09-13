@props([
    'image',
    'variant' => 'thumbnail',
    'alt' => 'Product image',
    'sizes' => null,
    'eager' => false,
])

@php
    $src = match ($variant) {
        'large' => $image->large_url,
        'medium' => $image->medium_url,
        default => $image->thumbnail_url,
    };
    $width = (int) ($image->getAttribute($variant . '_width') ?: $image->image_width ?: match ($variant) {
        'large' => 2000,
        'medium' => 1200,
        default => 480,
    });
    $height = (int) ($image->getAttribute($variant . '_height') ?: $image->image_height ?: $width);
    $srcset = $image->responsive_srcset;
@endphp

<img
    src="{{ $src }}"
    @if($srcset !== '') srcset="{{ $srcset }}" @endif
    @if($sizes) sizes="{{ $sizes }}" @endif
    width="{{ $width }}"
    height="{{ $height }}"
    alt="{{ $alt }}"
    loading="{{ $eager ? 'eager' : 'lazy' }}"
    decoding="async"
    @if($eager) fetchpriority="high" @endif
    {{ $attributes }}
>
