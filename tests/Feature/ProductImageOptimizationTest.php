<?php

use App\Jobs\CleanupProductImageObjects;
use App\Jobs\ProcessProductImageVariants;
use App\Models\BackgroundOperation;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ShoeType;
use App\Models\User;
use App\Services\CatalogCache;
use App\Services\ProductImageProcessor;
use App\Services\ProductImageService;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    if (! extension_loaded('gd')) {
        $this->markTestSkipped('PHP GD is required for product-image processing tests.');
    }

    Storage::fake('supabase');
    config([
        'product_images.disk' => 'supabase',
        'product_images.max_filesize_kb' => 5120,
        'product_images.max_width' => 6000,
        'product_images.max_height' => 6000,
        'product_images.max_pixels' => 24000000,
        'product_images.variant_format' => 'webp',
        'queue.default' => 'sync',
    ]);
});

function phase3bImageBinary(string $format, int $width = 800, int $height = 600): string
{
    $image = imagecreatetruecolor($width, $height);
    imagealphablending($image, false);
    imagesavealpha($image, true);
    $background = imagecolorallocate($image, 220, 20, 60);
    imagefilledrectangle($image, 0, 0, $width, $height, $background);
    $accent = imagecolorallocate($image, 20, 80, 180);
    for ($x = 0; $x < $width; $x += max(1, intdiv($width, 16))) {
        imageline($image, $x, 0, $width - $x, $height - 1, $accent);
    }

    ob_start();
    match ($format) {
        'jpeg' => imagejpeg($image, null, 90),
        'png' => imagepng($image, null, 6),
        'webp' => imagewebp($image, null, 88),
    };
    $binary = ob_get_clean();
    imagedestroy($image);

    return $binary;
}

function phase3bUpload(string $format, int $width = 800, int $height = 600, ?string $name = null): UploadedFile
{
    $extension = $format === 'jpeg' ? 'jpg' : $format;

    return UploadedFile::fake()->createWithContent($name ?? "shoe.{$extension}", phase3bImageBinary($format, $width, $height));
}

function phase3bProduct(): Product
{
    $category = Category::create(['category_name' => 'Image test']);
    $brand = Brand::create(['brand_name' => 'Image brand', 'is_active' => true]);
    $type = ShoeType::create(['shoe_type_name' => 'Image type', 'is_active' => true, 'display_order' => 1]);

    return Product::create([
        'category_id' => $category->getKey(),
        'brand_id' => $brand->getKey(),
        'shoe_type_id' => $type->getKey(),
        'product_name' => 'Responsive runner',
        'product_description' => 'Image test product',
        'is_active' => true,
    ]);
}

it('decodes and re-encodes JPEG PNG and WebP with immutable unpredictable variant keys', function (string $format) {
    $descriptor = app(ProductImageProcessor::class)->stageMany([phase3bUpload($format)])[0];

    expect($descriptor['image_mime'])->toBe('image/'.$format)
        ->and($descriptor['variant_mime'])->toBe('image/webp')
        ->and($descriptor['thumbnail_width'])->toBe(480)
        ->and($descriptor['thumbnail_height'])->toBe(360)
        ->and($descriptor['medium_width'])->toBe(800)
        ->and($descriptor['large_width'])->toBe(800)
        ->and($descriptor['content_hash'])->toHaveLength(64);

    foreach (['image_path', 'thumbnail_path', 'medium_path', 'large_path'] as $key) {
        expect($descriptor[$key])->toStartWith('products/pending/v1/')
            ->and($descriptor[$key])->not->toContain('shoe')
            ->and(Storage::disk('supabase')->exists($descriptor[$key]))->toBeTrue();
    }
})->with(['jpeg', 'png', 'webp']);

it('uses decoded content and rejects spoofed SVG corrupt files and all GIF images', function (UploadedFile $file) {
    expect(fn () => app(ProductImageProcessor::class)->stageMany([$file]))
        ->toThrow(ValidationException::class);
})->with([
    'svg named jpeg' => fn () => UploadedFile::fake()->createWithContent('shoe.jpg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
    'corrupt png' => fn () => UploadedFile::fake()->createWithContent('shoe.png', "\x89PNG\r\ncorrupt"),
    'animated GIF policy' => fn () => UploadedFile::fake()->createWithContent('shoe.gif', 'GIF89a'.str_repeat("\0", 64)),
]);

it('enforces the configured request image-count limit before staging', function () {
    config(['product_images.max_count' => 1]);
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $product = phase3bProduct();

    $this->withoutVite();
    $this->actingAs($admin)->post(route('admin.products.images.upload', $product), [
        'images' => [phase3bUpload('jpeg'), phase3bUpload('png')],
    ])->assertSessionHasErrors('images');

    expect(Storage::disk('supabase')->allFiles())->toBeEmpty();
});
it('enforces independent dimensions and pixel-count safety limits', function () {
    config(['product_images.max_width' => 100]);
    expect(fn () => app(ProductImageProcessor::class)->stageMany([phase3bUpload('png', 101, 20)]))
        ->toThrow(ValidationException::class);

    config(['product_images.max_width' => 6000, 'product_images.max_pixels' => 1000000]);
    expect(fn () => app(ProductImageProcessor::class)->stageMany([phase3bUpload('jpeg', 1200, 1000)]))
        ->toThrow(ValidationException::class);
});

it('never upscales small images and deduplicates identical work within a request', function () {
    $upload = phase3bUpload('png', 120, 80);
    $duplicate = UploadedFile::fake()->createWithContent('duplicate.png', $upload->getContent());
    $descriptors = app(ProductImageProcessor::class)->stageMany([$upload, $duplicate]);

    expect($descriptors[0])->toBe($descriptors[1])
        ->and($descriptors[0]['thumbnail_width'])->toBe(120)
        ->and($descriptors[0]['thumbnail_height'])->toBe(80)
        ->and($descriptors[0]['large_width'])->toBe(120)
        ->and(Storage::disk('supabase')->allFiles())->toHaveCount(4);
});

it('preserves PNG and WebP transparency in sanitized originals and variants', function (string $format) {
    $source = imagecreatetruecolor(40, 30);
    imagealphablending($source, false);
    imagesavealpha($source, true);
    $transparent = imagecolorallocatealpha($source, 0, 0, 0, 127);
    imagefilledrectangle($source, 0, 0, 39, 29, $transparent);
    $opaque = imagecolorallocatealpha($source, 220, 20, 60, 0);
    imagefilledrectangle($source, 10, 10, 20, 20, $opaque);
    ob_start();
    $format === 'png' ? imagepng($source) : imagewebp($source, null, 88);
    $binary = ob_get_clean();
    imagedestroy($source);

    $descriptor = app(ProductImageProcessor::class)->stageMany([
        UploadedFile::fake()->createWithContent('transparent.'.$format, $binary),
    ])[0];

    foreach (['image_path', 'thumbnail_path'] as $pathKey) {
        $decoded = imagecreatefromstring(Storage::disk('supabase')->get($descriptor[$pathKey]));
        expect($decoded)->toBeInstanceOf(GdImage::class)
            ->and((imagecolorat($decoded, 0, 0) >> 24) & 0x7F)->toBeGreaterThan(0);
        imagedestroy($decoded);
    }
})->with(['png', 'webp']);

it('normalizes JPEG EXIF orientation and strips the metadata on re-encode', function () {
    $jpeg = phase3bImageBinary('jpeg', 40, 20);
    $exif = "Exif\0\0MM".pack('nNn', 42, 8, 1).pack('nnNnnN', 0x0112, 3, 1, 6, 0, 0);
    $oriented = substr($jpeg, 0, 2)."\xFF\xE1".pack('n', strlen($exif) + 2).$exif.substr($jpeg, 2);
    $file = UploadedFile::fake()->createWithContent('oriented.jpg', $oriented);

    $descriptor = app(ProductImageProcessor::class)->stageMany([$file])[0];
    $stored = Storage::disk('supabase')->get($descriptor['image_path']);

    expect($descriptor['image_width'])->toBe(20)
        ->and($descriptor['image_height'])->toBe(40)
        ->and($stored)->not->toContain('Exif');
});

it('cleans staged objects when the database cannot create image records', function () {
    $missingProduct = new Product;
    $missingProduct->product_id = 999999;
    $missingProduct->exists = true;

    expect(fn () => app(ProductImageService::class)->storeMany($missingProduct, [phase3bUpload('jpeg')]))
        ->toThrow(QueryException::class);
    expect(Storage::disk('supabase')->allFiles())->toBeEmpty();
});

it('dispatches durable variant processing after commit and nothing after rollback', function () {
    Queue::fake();
    $product = phase3bProduct();
    $descriptor = app(ProductImageProcessor::class)->stageMany([phase3bUpload('jpeg')], false)[0];

    DB::beginTransaction();
    $image = ProductImage::create($descriptor + [
        'product_id' => $product->getKey(),
        'is_primary' => true,
        'display_order' => 1,
    ]);
    app(ProductImageService::class)->scheduleVariantGeneration($image);
    Queue::assertNothingPushed();
    DB::rollBack();

    Queue::assertNothingPushed();
    expect(BackgroundOperation::where('type', 'product_image_variants')->count())->toBe(0);

    DB::beginTransaction();
    $image = ProductImage::create($descriptor + [
        'product_id' => $product->getKey(),
        'is_primary' => true,
        'display_order' => 1,
    ]);
    app(ProductImageService::class)->scheduleVariantGeneration($image);
    Queue::assertNothingPushed();
    DB::commit();

    Queue::assertPushed(ProcessProductImageVariants::class, 1);
    expect(BackgroundOperation::where('type', 'product_image_variants')->count())->toBe(1);
});

it('processes a durable variant operation idempotently across duplicate delivery', function () {
    Queue::fake();
    $product = phase3bProduct();
    $descriptor = app(ProductImageProcessor::class)->stageMany([phase3bUpload('jpeg', 900, 600)], false)[0];
    $image = ProductImage::create($descriptor + [
        'product_id' => $product->getKey(),
        'is_primary' => true,
        'display_order' => 1,
    ]);
    $operation = app(ProductImageService::class)->scheduleVariantGeneration($image);
    $job = new ProcessProductImageVariants($operation->operation_key);

    app()->call([$job, 'handle']);
    $paths = $image->fresh()->storagePaths();
    app()->call([$job, 'handle']);

    expect($image->fresh()->hasResponsiveVariants())->toBeTrue()
        ->and($operation->fresh()->processed_at)->not->toBeNull()
        ->and($image->fresh()->storagePaths())->toBe($paths)
        ->and(Storage::disk('supabase')->allFiles())->toHaveCount(4);
});
it('does not delete variants published by a concurrent worker', function () {
    Queue::fake();
    $product = phase3bProduct();
    $descriptor = app(ProductImageProcessor::class)->stageMany([phase3bUpload('jpeg', 900, 600)], false)[0];
    $image = ProductImage::create($descriptor + [
        'product_id' => $product->getKey(),
        'is_primary' => true,
        'display_order' => 1,
    ]);
    $operation = app(ProductImageService::class)->scheduleVariantGeneration($image);
    $attributes = app(ProductImageProcessor::class)->generateVariants($image);

    $processor = Mockery::mock(ProductImageProcessor::class);
    $processor->shouldReceive('generateVariants')->once()->andReturnUsing(function () use ($image, $operation, $attributes) {
        $image->fresh()->update($attributes);
        $operation->fresh()->update(['processed_at' => now()]);

        return $attributes;
    });
    $processor->shouldReceive('deleteUnreferenced')->once()->with(Mockery::on(function (array $paths) use ($attributes) {
        app(ProductImageProcessor::class)->deleteUnreferenced($paths);

        return $paths === [
            $attributes['thumbnail_path'],
            $attributes['medium_path'],
            $attributes['large_path'],
        ];
    }));

    app()->call([new ProcessProductImageVariants($operation->operation_key), 'handle'], ['processor' => $processor]);

    foreach (['thumbnail_path', 'medium_path', 'large_path'] as $key) {
        expect(Storage::disk('supabase')->exists($attributes[$key]))->toBeTrue();
    }
});
it('refuses traversal-like stored paths without reading outside product storage', function () {
    $image = new ProductImage(['image_path' => '../outside.jpg']);

    expect(fn () => app(ProductImageProcessor::class)->generateVariants($image))
        ->toThrow(RuntimeException::class, 'invalid stored product-image path');
});

it('rolls variant metadata back without deleting or unlinking original rows', function () {
    $product = phase3bProduct();
    $image = ProductImage::create([
        'product_id' => $product->getKey(),
        'image_path' => 'products/legacy/original.jpg',
        'thumbnail_path' => 'products/variants/thumb.webp',
        'is_primary' => true,
        'display_order' => 1,
    ]);
    $migration = require database_path('migrations/2026_09_13_000008_add_product_image_variants.php');

    $migration->down();
    expect(Schema::hasColumn('product_images', 'image_path'))->toBeTrue()
        ->and(Schema::hasColumn('product_images', 'thumbnail_path'))->toBeFalse()
        ->and(DB::table('product_images')->where('image_id', $image->getKey())->value('image_path'))
        ->toBe('products/legacy/original.jpg');

    $migration->up();
});
it('backfill is dry-run by default bounded resumable and preserves originals', function () {
    $product = phase3bProduct();
    foreach (range(1, 3) as $index) {
        $path = "products/legacy/{$index}.jpg";
        Storage::disk('supabase')->put($path, phase3bImageBinary('jpeg', 700, 500));
        ProductImage::create([
            'product_id' => $product->getKey(),
            'image_path' => $path,
            'is_primary' => $index === 1,
            'display_order' => $index,
        ]);
    }

    $this->artisan('product-images:backfill', ['--max' => 2])
        ->expectsOutputToContain('this bounded run would inspect 2')
        ->assertSuccessful();
    expect(ProductImage::whereNotNull('variants_generated_at')->count())->toBe(0);

    $version = app(CatalogCache::class)->version();
    $this->artisan('product-images:backfill', ['--apply' => true, '--batch' => 1, '--max' => 2])
        ->assertSuccessful();
    expect(ProductImage::whereNotNull('variants_generated_at')->count())->toBe(2)
        ->and(ProductImage::whereNull('variants_generated_at')->count())->toBe(1)
        ->and(Storage::disk('supabase')->exists('products/legacy/1.jpg'))->toBeTrue()
        ->and(app(CatalogCache::class)->version())->toBeGreaterThan($version);

    $this->artisan('product-images:backfill', ['--apply' => true, '--image' => ProductImage::whereNull('variants_generated_at')->value('image_id')])
        ->assertSuccessful();
    expect(ProductImage::whereNull('variants_generated_at')->count())->toBe(0);
});

it('deletes every variant durably idempotently and never deletes shared objects', function () {
    $product = phase3bProduct();
    $paths = [
        'image_path' => 'products/shared/original.jpg',
        'thumbnail_path' => 'products/shared/thumb.webp',
        'medium_path' => 'products/shared/medium.webp',
        'large_path' => 'products/shared/large.webp',
    ];
    foreach ($paths as $path) {
        Storage::disk('supabase')->put($path, 'image');
    }
    $first = ProductImage::create($paths + ['product_id' => $product->getKey(), 'is_primary' => true, 'display_order' => 1]);
    $second = ProductImage::create($paths + ['product_id' => $product->getKey(), 'is_primary' => false, 'display_order' => 2]);

    app(ProductImageService::class)->delete($first);
    foreach ($paths as $path) {
        expect(Storage::disk('supabase')->exists($path))->toBeTrue();
    }

    app(ProductImageService::class)->delete($second);
    foreach ($paths as $path) {
        expect(Storage::disk('supabase')->exists($path))->toBeFalse();
    }
    expect(BackgroundOperation::where('type', 'product_image_cleanup')->count())->toBe(2)
        ->and(BackgroundOperation::whereNull('processed_at')->count())->toBe(0);
});

it('renders responsive markup and falls back to the original for legacy images', function () {
    $legacy = new ProductImage(['image_path' => 'products/legacy.jpg']);
    $legacyHtml = Blade::render('<x-product-image :image="$image" alt="Legacy shoe" class="shoe" />', ['image' => $legacy]);

    $responsive = new ProductImage([
        'image_path' => 'products/original.jpg',
        'thumbnail_path' => 'products/thumb.webp',
        'medium_path' => 'products/medium.webp',
        'large_path' => 'products/large.webp',
        'image_width' => 1600,
        'image_height' => 1200,
        'thumbnail_width' => 480,
        'thumbnail_height' => 360,
        'medium_width' => 1200,
        'medium_height' => 900,
        'large_width' => 1600,
        'large_height' => 1200,
    ]);
    $html = Blade::render('<x-product-image :image="$image" variant="medium" sizes="50vw" alt="Runner" />', ['image' => $responsive]);

    expect($legacyHtml)->toContain('products/legacy.jpg')
        ->and($legacyHtml)->not->toContain('srcset=')
        ->and($html)->toContain('products/medium.webp')
        ->and($html)->toContain('srcset=')
        ->and($html)->toContain('sizes="50vw"')
        ->and($html)->toContain('width="1200"')
        ->and($html)->toContain('loading="lazy"')
        ->and($html)->toContain('decoding="async"');
});

it('cleans earlier staged objects when a later file in the request is invalid', function () {
    $valid = phase3bUpload('jpeg');
    $invalid = UploadedFile::fake()->createWithContent('second.svg', '<svg xmlns="http://www.w3.org/2000/svg"/>');

    expect(fn () => app(ProductImageProcessor::class)->stageMany([$valid, $invalid]))
        ->toThrow(ValidationException::class);
    expect(Storage::disk('supabase')->allFiles())->toBeEmpty();
});

it('keeps failed cleanup operations pending for reconciliation and retry', function () {
    $operation = BackgroundOperation::create([
        'operation_key' => 'product-image-cleanup:invalid-test',
        'type' => 'product_image_cleanup',
        'payload' => ['paths' => ['../outside-storage']],
        'available_at' => now(),
    ]);

    expect(fn () => app()->call([new CleanupProductImageObjects($operation->operation_key), 'handle']))
        ->toThrow(RuntimeException::class);
    expect($operation->fresh()->processed_at)->toBeNull()
        ->and($operation->fresh()->attempts)->toBe(1)
        ->and($operation->fresh()->last_error)->toContain('invalid');
});

it('refuses traversal-shaped cleanup keys inside the product prefix', function () {
    $operation = BackgroundOperation::create([
        'operation_key' => 'product-image-cleanup:traversal-test',
        'type' => 'product_image_cleanup',
        'payload' => ['paths' => ['products/../outside-storage']],
        'available_at' => now(),
    ]);

    expect(fn () => app()->call([new CleanupProductImageObjects($operation->operation_key), 'handle']))
        ->toThrow(RuntimeException::class, 'invalid product-image cleanup path');
    expect($operation->fresh()->processed_at)->toBeNull();
});

it('measures representative request staging and queued variant costs', function () {
    $binary = phase3bImageBinary('jpeg', 1600, 1200);
    $beforeMemory = memory_get_usage(true);
    $stagingStarted = hrtime(true);
    $original = app(ProductImageProcessor::class)->stageMany([
        UploadedFile::fake()->createWithContent('representative.jpg', $binary),
    ], false)[0];
    $stagingMilliseconds = (hrtime(true) - $stagingStarted) / 1_000_000;

    $product = phase3bProduct();
    $image = ProductImage::create($original + [
        'product_id' => $product->getKey(),
        'is_primary' => true,
        'display_order' => 1,
    ]);
    $variantStarted = hrtime(true);
    $variants = app(ProductImageProcessor::class)->generateVariants($image);
    $variantMilliseconds = (hrtime(true) - $variantStarted) / 1_000_000;
    $memoryDelta = max(0, memory_get_peak_usage(true) - $beforeMemory);

    fwrite(STDOUT, sprintf(
        "\nPhase3B fixture: source=%dB sanitized=%dB card=%dB gallery=%dB large=%dB request_stage=%.1fms queued_variants=%.1fms peak_delta=%dB\n",
        strlen($binary),
        $original['image_size'],
        $variants['thumbnail_size'],
        $variants['medium_size'],
        $variants['large_size'],
        $stagingMilliseconds,
        $variantMilliseconds,
        $memoryDelta,
    ));

    expect($variants['thumbnail_size'])->toBeLessThan(strlen($binary))
        ->and($variants['medium_size'])->toBeLessThan(strlen($binary))
        ->and($variants['thumbnail_width'])->toBe(480)
        ->and($variants['medium_width'])->toBe(1200);
});
it('uses responsive image components throughout active product surfaces', function () {
    foreach ([
        'resources/views/components/hero-carousel.blade.php',
        'resources/views/partials/recommendations.blade.php',
        'resources/views/product/show.blade.php',
        'resources/views/cart/index.blade.php',
        'resources/views/checkout/index.blade.php',
        'resources/views/orders/show.blade.php',
        'resources/views/admin/products/edit.blade.php',
        'resources/views/guest/index.blade.php',
        'resources/views/pages/men.blade.php',
        'resources/views/pages/women.blade.php',
        'resources/views/pages/kids.blade.php',
        'resources/views/pages/new.blade.php',
        'resources/views/pages/search.blade.php',
    ] as $view) {
        expect(file_get_contents(base_path($view)))->toContain('x-product-image');
    }
});
