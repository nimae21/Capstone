<?php

namespace App\Services;

use App\Models\ProductImage;
use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class ProductImageProcessor
{
    /** @return array<int, array<string, int|string|null>> */
    public function stageMany(array $files, bool $withVariants = true): array
    {
        $result = [];
        $byHash = [];

        try {
            foreach ($files as $file) {
                if (! $file instanceof UploadedFile || ! $file->isValid()) {
                    throw ValidationException::withMessages(['images' => 'One of the uploaded images could not be read.']);
                }

                $hash = hash_file('sha256', $file->getRealPath());
                if (isset($byHash[$hash])) {
                    $result[] = $byHash[$hash];

                    continue;
                }

                $byHash[$hash] = $this->processAndStore($file->getRealPath(), 'products/pending', true, $withVariants);
                $result[] = $byHash[$hash];
            }

            return $result;
        } catch (Throwable $exception) {
            $this->deleteQuietly(array_merge(...array_map(
                fn (array $descriptor) => array_filter([
                    $descriptor['image_path'] ?? null,
                    $descriptor['thumbnail_path'] ?? null,
                    $descriptor['medium_path'] ?? null,
                    $descriptor['large_path'] ?? null,
                ]),
                array_values($byHash),
            )));
            throw $exception;
        }
    }

    /** @return array<string, int|string|null> */
    public function generateVariants(ProductImage $image): array
    {
        if ($image->hasResponsiveVariants()) {
            return $image->variantAttributes();
        }

        if (! str_starts_with($image->image_path, 'products/')
            || str_contains($image->image_path, '..')
            || str_contains($image->image_path, chr(92))
            || str_contains($image->image_path, chr(0))) {
            throw new RuntimeException('Refusing an invalid stored product-image path.');
        }

        $disk = Storage::disk(config('product_images.disk'));
        $stream = $disk->readStream($image->image_path);
        if (! is_resource($stream)) {
            throw new RuntimeException('The stored original could not be opened.');
        }

        $temporary = tmpfile();
        if ($temporary === false) {
            fclose($stream);
            throw new RuntimeException('A secure temporary image file could not be created.');
        }

        try {
            $limit = ((int) config('product_images.max_filesize_kb') * 1024) + 1;
            $copied = stream_copy_to_stream($stream, $temporary, $limit);
            if ($copied === false || $copied >= $limit) {
                throw ValidationException::withMessages(['image' => 'The stored image exceeds the configured processing limit.']);
            }
            $path = stream_get_meta_data($temporary)['uri'];
            $token = 'image-'.$image->getKey().'-'.substr(hash('sha256', $image->image_path), 0, 16);
            $attributes = $this->processAndStore($path, 'products/variants', false, true, $token);
            unset($attributes['image_path']);

            return $attributes;
        } finally {
            fclose($stream);
            fclose($temporary);
        }
    }

    /** @return array<string, int|string|null> */
    private function processAndStore(string $path, string $prefix, bool $storeOriginal, bool $withVariants, ?string $keyToken = null): array
    {
        $this->assertRuntimeSupport();
        [$source, $type, $width, $height, $inputSize] = $this->decode($path);
        $stored = [];

        try {
            $source = $this->orient($source, $path, $type);
            $width = imagesx($source);
            $height = imagesy($source);
            $hash = hash_file('sha256', $path);
            $directory = trim($prefix, '/').'/v1/'.($keyToken ?: bin2hex(random_bytes(16)));
            $attributes = [
                'image_width' => $width,
                'image_height' => $height,
                'image_mime' => $this->mimeForType($type),
                'image_size' => $inputSize,
                'content_hash' => $hash,
            ];

            if ($storeOriginal) {
                [$original, $extension, $mime] = $this->encodeOriginal($source, $type);
                $originalHash = hash('sha256', $original);
                $originalPath = "{$directory}/{$originalHash}-original.{$extension}";
                $this->putImmutable($originalPath, $original, $mime);
                $stored[] = $originalPath;
                $attributes['image_path'] = $originalPath;
                $attributes['image_size'] = strlen($original);
                $attributes['content_hash'] = $originalHash;
            }

            if (! $withVariants) {
                return $attributes;
            }

            foreach (config('product_images.variants') as $name => $settings) {
                [$variant, $variantWidth, $variantHeight] = $this->resize(
                    $source,
                    (int) $settings['max_width'],
                    (int) $settings['max_height'],
                );
                [$binary, $extension, $mime] = $this->encodeVariant($variant, (int) $settings['quality']);
                imagedestroy($variant);
                $variantPath = "{$directory}/".hash('sha256', $binary)."-{$name}.{$extension}";
                $this->putImmutable($variantPath, $binary, $mime);
                $stored[] = $variantPath;
                $attributes[$name.'_path'] = $variantPath;
                $attributes[$name.'_width'] = $variantWidth;
                $attributes[$name.'_height'] = $variantHeight;
                $attributes[$name.'_size'] = strlen($binary);
                $attributes['variant_mime'] = $mime;
            }

            $attributes['variants_generated_at'] = now()->toISOString();

            return $attributes;
        } catch (Throwable $exception) {
            $this->deleteQuietly($stored);
            throw $exception;
        } finally {
            imagedestroy($source);
        }
    }

    /** @return array{GdImage, int, int, int, int} */
    private function decode(string $path): array
    {
        $size = filesize($path);
        $maxBytes = (int) config('product_images.max_filesize_kb') * 1024;
        if ($size === false || $size < 1 || $size > $maxBytes) {
            throw ValidationException::withMessages(['images' => 'Each image must be a non-empty file no larger than '.config('product_images.max_filesize_kb').' KB.']);
        }

        $info = $this->withoutWarnings(fn () => getimagesize($path));
        if (! is_array($info) || ! isset($info[0], $info[1], $info[2])) {
            throw ValidationException::withMessages(['images' => 'An uploaded file is not a decodable image.']);
        }

        [$width, $height, $type] = [(int) $info[0], (int) $info[1], (int) $info[2]];
        if ($type === IMAGETYPE_GIF) {
            throw ValidationException::withMessages(['images' => 'GIF images, including animations, are not supported. Use JPEG, PNG, or WebP.']);
        }
        if (! in_array($type, [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            throw ValidationException::withMessages(['images' => 'Only decoded JPEG, PNG, and WebP images are supported.']);
        }
        if ($width < 1 || $height < 1
            || $width > (int) config('product_images.max_width')
            || $height > (int) config('product_images.max_height')
            || $width > intdiv((int) config('product_images.max_pixels'), $height)) {
            throw ValidationException::withMessages(['images' => 'The image dimensions or total pixel count exceed the configured safety limit.']);
        }

        $decoder = match ($type) {
            IMAGETYPE_JPEG => 'imagecreatefromjpeg',
            IMAGETYPE_PNG => 'imagecreatefrompng',
            IMAGETYPE_WEBP => 'imagecreatefromwebp',
        };
        $image = $this->withoutWarnings(fn () => $decoder($path));
        if (! $image instanceof GdImage) {
            throw ValidationException::withMessages(['images' => 'The image data is corrupt or unsupported by this server.']);
        }
        if (in_array($type, [IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            imagealphablending($image, false);
            imagesavealpha($image, true);
        }

        return [$image, $type, $width, $height, $size];
    }

    private function orient(GdImage $image, string $path, int $type): GdImage
    {
        if ($type !== IMAGETYPE_JPEG) {
            return $image;
        }
        if (! function_exists('exif_read_data')) {
            throw ValidationException::withMessages(['images' => 'Secure JPEG orientation processing is unavailable. Enable PHP EXIF support.']);
        }

        $exif = $this->withoutWarnings(fn () => exif_read_data($path, 'IFD0', true, false));
        $orientation = (int) ($exif['IFD0']['Orientation'] ?? $exif['Orientation'] ?? 1);
        if (in_array($orientation, [2, 4, 5, 7], true)) {
            imageflip($image, in_array($orientation, [5, 7], true) ? IMG_FLIP_HORIZONTAL : ($orientation === 4 ? IMG_FLIP_VERTICAL : IMG_FLIP_HORIZONTAL));
        }
        $angle = match ($orientation) {
            3, 4 => 180,
            5, 6 => -90,
            7, 8 => 90,
            default => 0,
        };
        if ($angle === 0) {
            return $image;
        }
        $rotated = imagerotate($image, $angle, imagecolorallocatealpha($image, 0, 0, 0, 127));
        if (! $rotated instanceof GdImage) {
            throw new RuntimeException('The image orientation could not be normalized.');
        }
        imagesavealpha($rotated, true);
        imagedestroy($image);

        return $rotated;
    }

    /** @return array{GdImage, int, int} */
    private function resize(GdImage $source, int $maxWidth, int $maxHeight): array
    {
        $width = imagesx($source);
        $height = imagesy($source);
        $scale = min(1, $maxWidth / $width, $maxHeight / $height);
        $targetWidth = max(1, (int) floor($width * $scale));
        $targetHeight = max(1, (int) floor($height * $scale));
        $target = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($target, false);
        imagesavealpha($target, true);
        $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
        imagefilledrectangle($target, 0, 0, $targetWidth, $targetHeight, $transparent);
        if (! imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height)) {
            imagedestroy($target);
            throw new RuntimeException('An image variant could not be resized.');
        }

        return [$target, $targetWidth, $targetHeight];
    }

    private function encodeOriginal(GdImage $image, int $type): array
    {
        return match ($type) {
            IMAGETYPE_JPEG => [$this->capture(fn () => imagejpeg($image, null, (int) config('product_images.jpeg_quality'))), 'jpg', 'image/jpeg'],
            IMAGETYPE_PNG => [$this->capture(fn () => imagepng($image, null, (int) config('product_images.png_compression'))), 'png', 'image/png'],
            IMAGETYPE_WEBP => [$this->capture(fn () => imagewebp($image, null, (int) config('product_images.jpeg_quality'))), 'webp', 'image/webp'],
        };
    }

    private function encodeVariant(GdImage $image, int $quality): array
    {
        if (config('product_images.variant_format') === 'webp' && function_exists('imagewebp')) {
            return [$this->capture(fn () => imagewebp($image, null, $quality)), 'webp', 'image/webp'];
        }

        return [$this->capture(fn () => imagejpeg($image, null, $quality)), 'jpg', 'image/jpeg'];
    }

    private function capture(callable $encoder): string
    {
        ob_start();
        $success = $encoder();
        $binary = ob_get_clean();
        if (! $success || ! is_string($binary) || $binary === '') {
            throw new RuntimeException('The image could not be safely re-encoded.');
        }

        return $binary;
    }

    private function putImmutable(string $path, string $contents, string $mime): void
    {
        $stored = Storage::disk(config('product_images.disk'))->put($path, $contents, [
            'visibility' => 'public',
            'ContentType' => $mime,
            'CacheControl' => 'public, max-age=31536000, immutable',
        ]);
        if (! $stored) {
            throw new RuntimeException('An image object could not be stored.');
        }
    }

    public function deleteQuietly(array $paths): void
    {
        foreach (array_unique(array_filter($paths)) as $path) {
            try {
                Storage::disk(config('product_images.disk'))->delete($path);
            } catch (Throwable $exception) {
                report($exception);
            }
        }
    }

    public function deleteUnreferenced(array $paths): void
    {
        foreach (array_unique(array_filter($paths)) as $path) {
            $referenced = ProductImage::query()->where(function ($query) use ($path) {
                $query->where('image_path', $path)
                    ->orWhere('thumbnail_path', $path)
                    ->orWhere('medium_path', $path)
                    ->orWhere('large_path', $path);
            })->exists();

            if (! $referenced) {
                $this->deleteQuietly([$path]);
            }
        }
    }

    private function assertRuntimeSupport(): void
    {
        if (! extension_loaded('gd') || ! function_exists('imagecreatefromjpeg')
            || ! function_exists('imagecreatefrompng') || ! function_exists('imagecreatefromwebp')
            || ! function_exists('imagewebp')) {
            throw ValidationException::withMessages(['images' => 'Secure image processing is unavailable. Enable PHP GD with JPEG, PNG, and WebP support.']);
        }
    }

    private function mimeForType(int $type): string
    {
        return match ($type) {
            IMAGETYPE_JPEG => 'image/jpeg',
            IMAGETYPE_PNG => 'image/png',
            IMAGETYPE_WEBP => 'image/webp',
        };
    }

    private function withoutWarnings(callable $callback): mixed
    {
        set_error_handler(static fn () => true);
        try {
            return $callback();
        } finally {
            restore_error_handler();
        }
    }
}
