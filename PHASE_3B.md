# Phase 3B - secure product images and responsive delivery

Date: 2026-09-13. This checkpoint changes Laravel only. It does not run a production migration, touch Supabase objects, execute a production backfill, or begin Phase 4.

## Lifecycle audit

Before this checkpoint, approval uploads accepted files declared as JPEG, PNG, GIF, or WebP, up to 5 MiB each and 10 per request. Laravel's request image rule inspected headers, but files were copied directly to the Supabase S3-compatible disk without a full decode, dimension/pixel bound, orientation normalization, metadata removal, or responsive variants. Laravel generated a random filename below `products/pending`; the client filename was not used as the storage key. Product cards, recommendations, gallery, carousel, cart, checkout, orders, and admin image management all requested `image_path`, which was the original object.

Product-image deletion previously deleted the remote object before deleting the database row. A storage outage therefore blocked the admin action, while a database error after remote deletion could leave a live row pointing at a missing object. Only the original key was deleted. Catalog cache invalidation already observed ProductImage commits.

Images are remote in production through the `supabase` S3-compatible disk. Tests use an in-memory fake. POS and reports currently do not render product images, so no image request was added to those pages.

The local XAMPP installation contains `php_gd.dll`, and the bundled GD build reports JPEG, PNG, WebP, GIF, and AVIF codec support. The CLI `php.ini` has `extension=gd` commented out. EXIF and fileinfo are enabled. Imagick and the ImageMagick `magick` executable are unavailable. Phase 3B uses GD with JPEG, PNG, and WebP plus PHP EXIF for JPEG orientation. AVIF remains disabled because deployment support is not confirmed.

## Upload and processing behavior

Uploads are constrained by configuration and validated by decoded content:

- JPEG, PNG, and WebP are fully decoded and safely re-encoded.
- GIF is rejected, whether static or animated. SVG and every other decoded type are rejected.
- Defaults are 10 images, 5 MiB per image, 6000 px width, 6000 px height, and 24 million total pixels.
- JPEG EXIF orientation is normalized. Re-encoding strips EXIF and other unneeded metadata.
- A malformed image, content/MIME spoof, unavailable codec, or exceeded dimension/pixel bound fails validation before an approval record is created.
- Storage keys are beneath `products/pending/v1/<128-bit-random>/`, include a SHA-256 content hash and version, and never include the user filename.
- Processing only reads the server-managed upload temporary file or an object key already stored in the configured product-image disk. It never accepts an external URL or caller-controlled filesystem path.
- A later failure in a multi-file request deletes objects staged earlier in that request. A database failure also cleans staged objects.

The request synchronously decodes and re-encodes only the safe original. Variant generation is durable background work because the representative 1600x1200 three-variant operation took about one second locally. The approval transaction creates a `product_image_variants` background operation and dispatches `ProcessProductImageVariants` after commit. A rollback dispatches nothing. Until the job completes, every accessor and view falls back to `image_path`.

Variant jobs are encrypted, unique by durable operation key, retryable, and idempotent. Deterministic per-image variant keys allow a crash or retry to overwrite the same objects instead of creating abandoned copies. If concurrent workers race, loser cleanup checks every image reference before deleting any generated object. The job performs storage I/O without a database transaction, verifies all objects, then uses a short row-locked transaction to publish metadata and complete the durable operation. ProductImage's existing after-commit observer invalidates catalog cache versions when variants become visible. `background:reconcile` redispatches missed variant and cleanup operations.

## Variants and delivery

The default preferred variant format is WebP. JPEG is the configured fallback format, though deployed GD must still support WebP decoding to preserve accepted WebP uploads. Images retain aspect ratio and are never upscaled.

| Use | Maximum dimensions | Quality |
| --- | ---: | ---: |
| Thumbnail: cards, cart, checkout, orders, admin | 480 x 480 | 82 |
| Medium: product detail candidates | 1200 x 1200 | 84 |
| Large: hero and detail main image | 2000 x 2000 | 86 |
| Sanitized JPEG original | Source dimensions | 88 |
| Sanitized PNG original | Source dimensions | Compression 6 |

Variant objects receive `Cache-Control: public, max-age=31536000, immutable`. Their versioned, content-hash paths do not change in place for a different source image. Responsive views provide `srcset`, `sizes`, intrinsic width/height, asynchronous decoding, and lazy loading offscreen. The first hero image and product-detail main image remain eager with high fetch priority.

The representative striped JPEG fixture measured:

| Asset | Bytes |
| --- | ---: |
| Uploaded source | 117,130 |
| Sanitized original | 97,135 |
| Card thumbnail | 4,042 |
| Medium gallery candidate | 24,052 |
| Large candidate | 49,810 |

Across the final focused and full-suite Windows/XAMPP runs, safe-original request staging took 67.6-90.3 ms and queued three-variant generation took 1,028.6-1,664.1 ms, with a 25,165,824 byte measured PHP peak-memory delta. A backfilled list card changes from one original request per visible product to zero original requests; the browser selects a responsive variant. A gallery changes from originals for every thumbnail and main view to thumbnails plus a medium/large responsive candidate. These fixture figures are not production latency or CDN measurements.

## Deletion and recovery

Deleting an image now removes the row and promotes the next primary image inside a short transaction. It creates a `product_image_cleanup` durable operation in that transaction. `CleanupProductImageObjects` runs after commit, retries partial provider failures, treats duplicate delivery as complete, and refuses:

- paths outside `products/`;
- any object still referenced as an original, thumbnail, medium, or large image by another row.

A rejected approval schedules the same durable cleanup for its staged sanitized originals. Queue dispatch failure does not roll back the committed admin/customer action because the durable operation remains available to the scheduled reconciler. Failed cleanup is logged on the operation and remains retryable.

## Migration, deploy, backfill, and rollback

Migration `2026_09_13_000008_add_product_image_variants.php` only adds nullable paths, dimensions, MIME, byte sizes, content hash, and completion time. Existing rows render from `image_path` immediately. The down migration removes only new metadata columns; it does not delete or rewrite an original object.

Deploy in this order:

1. Enable PHP GD with JPEG, PNG, and WebP support plus PHP EXIF in the web, worker, scheduler, and one-off command services.
2. Configure the image environment values and existing Supabase S3 credentials.
3. Run the additive migration.
4. Deploy the code.
5. Keep the existing `background` queue worker and minute scheduler running.
6. Run a dry run, then explicitly approve bounded production batches after storage/browser QA.

Commands:

```text
php artisan product-images:backfill --max=100 --batch=25
php artisan product-images:backfill --apply --max=100 --batch=25
php artisan product-images:backfill --product=123 --max=25
php artisan product-images:backfill --apply --image=456
```

Dry-run is the default. `--apply` is required to write. Completed rows are skipped, each run has hard batch/max bounds, each result is verified before metadata is saved, failures are reported without deleting originals, and rerunning resumes remaining rows. Do not execute this command against production until a dry-run count, Supabase permissions, capacity, and rollback window are approved.

For rollback, deploy the earlier application code first, confirm it reads only `image_path`, and then run the down migration if required. Generated variants can remain harmlessly in storage; rollback never deletes originals. Do not delete variant objects as part of this rollout.

## Required Railway and Supabase configuration

Set these in every Laravel service that processes or renders images:

```text
PRODUCT_IMAGE_DISK=supabase
PRODUCT_IMAGE_MAX_COUNT=10
PRODUCT_IMAGE_MAX_FILESIZE_KB=5120
PRODUCT_IMAGE_MAX_WIDTH=6000
PRODUCT_IMAGE_MAX_HEIGHT=6000
PRODUCT_IMAGE_MAX_PIXELS=24000000
PRODUCT_IMAGE_JPEG_QUALITY=88
PRODUCT_IMAGE_PNG_COMPRESSION=6
PRODUCT_IMAGE_VARIANT_FORMAT=webp
PRODUCT_IMAGE_THUMBNAIL_WIDTH=480
PRODUCT_IMAGE_THUMBNAIL_HEIGHT=480
PRODUCT_IMAGE_THUMBNAIL_QUALITY=82
PRODUCT_IMAGE_MEDIUM_WIDTH=1200
PRODUCT_IMAGE_MEDIUM_HEIGHT=1200
PRODUCT_IMAGE_MEDIUM_QUALITY=84
PRODUCT_IMAGE_LARGE_WIDTH=2000
PRODUCT_IMAGE_LARGE_HEIGHT=2000
PRODUCT_IMAGE_LARGE_QUALITY=86
PRODUCT_IMAGE_BACKFILL_BATCH=25
PRODUCT_IMAGE_BACKFILL_MAX=100
IMAGE_PROCESSING_JOB_TIMEOUT=180
IMAGE_CLEANUP_JOB_TIMEOUT=60
```

Keep the existing `SUPABASE_STORAGE_*`, Redis, queue, and scheduler variables. The worker must consume the configured `BACKGROUND_QUEUE` (currently `background`) with a timeout greater than `IMAGE_PROCESSING_JOB_TIMEOUT`. The scheduler must continue running `background:reconcile` every minute. Confirm the Supabase bucket permits private server writes/deletes and public reads through the configured URL, and confirm its CDN preserves the object Cache-Control metadata.

Local development may keep the sync queue, but GD must be enabled in `C:\xampp\php\php.ini` for web uploads. Tests can run in the current shell with `php -d extension=gd vendor\bin\pest`; `php artisan test` starts a child process without the command-line extension flag in this installation.

## Manual validation still required

- Run PostgreSQL migrations and concurrent duplicate-job/deletion checks in a disposable PostgreSQL environment.
- Validate real Supabase S3 metadata, partial-delete retries, object visibility, and CDN cache headers.
- Measure multi-image processing, worker memory, queue latency, storage growth, and real card/gallery transfer sizes with production-like photos.
- Check Chrome, Safari, Firefox, Android WebView, high-DPI cards, color switching, broken-image fallback, hero LCP, cumulative layout shift, and zoom quality.
- Confirm Railway web/worker/scheduler images all load the same GD codecs.
- Continue the previously deferred strict CSP and device QA work separately.
