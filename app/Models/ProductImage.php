<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    public const RESPONSIVE_COLUMNS = [
        'image_id', 'product_id', 'color', 'image_path', 'thumbnail_path', 'medium_path', 'large_path',
        'image_width', 'image_height', 'image_mime', 'image_size',
        'thumbnail_width', 'thumbnail_height', 'thumbnail_size',
        'medium_width', 'medium_height', 'medium_size',
        'large_width', 'large_height', 'large_size', 'variant_mime',
        'content_hash', 'variants_generated_at', 'is_primary', 'display_order',
    ];

    protected $primaryKey = 'image_id';

    protected $fillable = [
        'product_id', 'color', 'image_path', 'thumbnail_path', 'medium_path', 'large_path',
        'image_width', 'image_height', 'image_mime', 'image_size',
        'thumbnail_width', 'thumbnail_height', 'thumbnail_size',
        'medium_width', 'medium_height', 'medium_size',
        'large_width', 'large_height', 'large_size', 'variant_mime',
        'content_hash', 'variants_generated_at', 'is_primary', 'display_order',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'variants_generated_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function getImageUrlAttribute(): string
    {
        return $this->urlForPath($this->image_path);
    }

    public function getThumbnailUrlAttribute(): string
    {
        return $this->urlForPath($this->thumbnail_path ?: $this->image_path);
    }

    public function getMediumUrlAttribute(): string
    {
        return $this->urlForPath($this->medium_path ?: $this->image_path);
    }

    public function getLargeUrlAttribute(): string
    {
        return $this->urlForPath($this->large_path ?: $this->image_path);
    }

    public function getResponsiveSrcsetAttribute(): string
    {
        $candidates = [];
        foreach (['thumbnail', 'medium', 'large'] as $variant) {
            $path = $this->getAttribute($variant.'_path');
            $width = (int) $this->getAttribute($variant.'_width');
            if ($path && $width > 0) {
                $candidates[$width] = $this->urlForPath($path).' '.$width.'w';
            }
        }
        ksort($candidates);

        return implode(', ', $candidates);
    }

    public function hasResponsiveVariants(): bool
    {
        return (bool) ($this->thumbnail_path && $this->medium_path && $this->large_path && $this->variants_generated_at);
    }

    public function variantAttributes(): array
    {
        return collect($this->getAttributes())->only([
            'thumbnail_path', 'medium_path', 'large_path', 'image_width', 'image_height', 'image_mime', 'image_size',
            'thumbnail_width', 'thumbnail_height', 'thumbnail_size', 'medium_width', 'medium_height', 'medium_size',
            'large_width', 'large_height', 'large_size', 'variant_mime', 'content_hash', 'variants_generated_at',
        ])->all();
    }

    public function storagePaths(): array
    {
        return array_values(array_unique(array_filter([
            $this->image_path, $this->thumbnail_path, $this->medium_path, $this->large_path,
        ])));
    }

    private function urlForPath(string $path): string
    {
        return Storage::disk(config('product_images.disk', 'supabase'))->url($path);
    }
}
