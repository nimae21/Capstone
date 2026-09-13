<?php

$variantFormat = strtolower((string) env('PRODUCT_IMAGE_VARIANT_FORMAT', 'webp'));

return [
    'disk' => env('PRODUCT_IMAGE_DISK', 'supabase'),
    'max_count' => max(1, min(20, (int) env('PRODUCT_IMAGE_MAX_COUNT', 10))),
    'max_filesize_kb' => max(256, min(20480, (int) env('PRODUCT_IMAGE_MAX_FILESIZE_KB', 5120))),
    'max_width' => max(256, min(12000, (int) env('PRODUCT_IMAGE_MAX_WIDTH', 6000))),
    'max_height' => max(256, min(12000, (int) env('PRODUCT_IMAGE_MAX_HEIGHT', 6000))),
    'max_pixels' => max(1000000, min(50000000, (int) env('PRODUCT_IMAGE_MAX_PIXELS', 24000000))),
    'jpeg_quality' => max(55, min(95, (int) env('PRODUCT_IMAGE_JPEG_QUALITY', 88))),
    'png_compression' => max(0, min(9, (int) env('PRODUCT_IMAGE_PNG_COMPRESSION', 6))),
    'variant_format' => in_array($variantFormat, ['webp', 'jpeg'], true) ? $variantFormat : 'webp',
    'variants' => [
        'thumbnail' => [
            'max_width' => max(160, min(800, (int) env('PRODUCT_IMAGE_THUMBNAIL_WIDTH', 480))),
            'max_height' => max(160, min(800, (int) env('PRODUCT_IMAGE_THUMBNAIL_HEIGHT', 480))),
            'quality' => max(50, min(95, (int) env('PRODUCT_IMAGE_THUMBNAIL_QUALITY', 82))),
        ],
        'medium' => [
            'max_width' => max(480, min(2000, (int) env('PRODUCT_IMAGE_MEDIUM_WIDTH', 1200))),
            'max_height' => max(480, min(2000, (int) env('PRODUCT_IMAGE_MEDIUM_HEIGHT', 1200))),
            'quality' => max(50, min(95, (int) env('PRODUCT_IMAGE_MEDIUM_QUALITY', 84))),
        ],
        'large' => [
            'max_width' => max(800, min(4000, (int) env('PRODUCT_IMAGE_LARGE_WIDTH', 2000))),
            'max_height' => max(800, min(4000, (int) env('PRODUCT_IMAGE_LARGE_HEIGHT', 2000))),
            'quality' => max(50, min(95, (int) env('PRODUCT_IMAGE_LARGE_QUALITY', 86))),
        ],
    ],
    'backfill_batch' => max(1, min(200, (int) env('PRODUCT_IMAGE_BACKFILL_BATCH', 25))),
    'backfill_max' => max(1, min(1000, (int) env('PRODUCT_IMAGE_BACKFILL_MAX', 100))),
];
