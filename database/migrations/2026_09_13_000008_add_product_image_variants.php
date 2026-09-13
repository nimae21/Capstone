<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->string('thumbnail_path')->nullable()->after('image_path');
            $table->string('medium_path')->nullable()->after('thumbnail_path');
            $table->string('large_path')->nullable()->after('medium_path');
            $table->unsignedInteger('image_width')->nullable()->after('large_path');
            $table->unsignedInteger('image_height')->nullable()->after('image_width');
            $table->string('image_mime', 32)->nullable()->after('image_height');
            $table->unsignedBigInteger('image_size')->nullable()->after('image_mime');
            $table->unsignedInteger('thumbnail_width')->nullable()->after('image_size');
            $table->unsignedInteger('thumbnail_height')->nullable()->after('thumbnail_width');
            $table->unsignedBigInteger('thumbnail_size')->nullable()->after('thumbnail_height');
            $table->unsignedInteger('medium_width')->nullable()->after('thumbnail_size');
            $table->unsignedInteger('medium_height')->nullable()->after('medium_width');
            $table->unsignedBigInteger('medium_size')->nullable()->after('medium_height');
            $table->unsignedInteger('large_width')->nullable()->after('medium_size');
            $table->unsignedInteger('large_height')->nullable()->after('large_width');
            $table->unsignedBigInteger('large_size')->nullable()->after('large_height');
            $table->string('variant_mime', 32)->nullable()->after('large_size');
            $table->string('content_hash', 64)->nullable()->after('variant_mime');
            $table->timestamp('variants_generated_at')->nullable()->after('content_hash');
        });
    }

    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropColumn([
                'thumbnail_path', 'medium_path', 'large_path', 'image_width', 'image_height',
                'image_mime', 'image_size', 'thumbnail_width', 'thumbnail_height', 'thumbnail_size',
                'medium_width', 'medium_height', 'medium_size', 'large_width', 'large_height',
                'large_size', 'variant_mime', 'content_hash', 'variants_generated_at',
            ]);
        });
    }
};
