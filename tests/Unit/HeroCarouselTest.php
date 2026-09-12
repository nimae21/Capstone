<?php

namespace Tests\Unit;

use App\View\Components\HeroCarousel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HeroCarouselTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null]);
        DB::purge('sqlite');
        Schema::create('products', function (Blueprint $table) {
            $table->increments('product_id');
            $table->string('product_name');
            $table->boolean('is_active');
        });
        Schema::create('product_images', function (Blueprint $table) {
            $table->increments('image_id');
            $table->integer('product_id');
            $table->boolean('is_primary');
            $table->integer('display_order')->default(0);
            $table->string('image_path')->nullable();
        });
    }

    public function test_active_products_use_primary_then_ordered_images_without_n_plus_one_queries(): void
    {
        DB::table('products')->insert([
            ['product_id' => 1, 'product_name' => 'Primary shoe', 'is_active' => true],
            ['product_id' => 2, 'product_name' => 'Ordered shoe', 'is_active' => true],
            ['product_id' => 3, 'product_name' => 'Hidden shoe', 'is_active' => false],
        ]);
        DB::table('product_images')->insert([
            ['product_id' => 1, 'is_primary' => false, 'display_order' => 0, 'image_path' => 'other.jpg'],
            ['product_id' => 1, 'is_primary' => true, 'display_order' => 5, 'image_path' => 'primary.jpg'],
            ['product_id' => 2, 'is_primary' => true, 'display_order' => 0, 'image_path' => ''],
            ['product_id' => 2, 'is_primary' => false, 'display_order' => 2, 'image_path' => 'later.jpg'],
            ['product_id' => 2, 'is_primary' => false, 'display_order' => 1, 'image_path' => 'first.jpg'],
        ]);
        DB::enableQueryLog();
        DB::flushQueryLog();
        $view = (new HeroCarousel)->render();
        $products = $view->getData()['products'];
        $this->assertCount(2, DB::getQueryLog());
        DB::disableQueryLog();
        $this->assertSame([2, 1], $products->pluck('product_id')->all());
        $this->assertSame('first.jpg', $products[0]->images->first()->image_path);
        $this->assertSame('primary.jpg', $products[1]->images->first()->image_path);
        $disk = \Mockery::mock();
        $disk->shouldReceive('url')->with('first.jpg')->andReturn('https://storage.example/first.jpg');
        $disk->shouldReceive('url')->with('primary.jpg')->andReturn('https://storage.example/primary.jpg');
        Storage::shouldReceive('disk')->with('supabase')->andReturn($disk);
        $html = $view->render();
        $this->assertStringContainsString('https://storage.example/primary.jpg', $html);
        $this->assertStringContainsString(route('product.show', 2), $html);
        $this->assertStringNotContainsString('Hidden shoe', $html);
    }

    public function test_missing_images_and_empty_catalog_render_fallback_without_controls(): void
    {
        $view = (new HeroCarousel)->render();
        $this->assertStringContainsString($view->getData()['fallback'], $view->render());
        $this->assertStringNotContainsString('data-next', $view->render());
        DB::table('products')->insert(['product_name' => 'No image', 'is_active' => true]);
        $view = (new HeroCarousel)->render();
        $html = $view->render();
        $this->assertStringContainsString($view->getData()['fallback'], $html);
        $this->assertStringContainsString(route('product.show', 1), $html);
        $this->assertStringNotContainsString('data-next', $html);
    }

    public function test_hero_is_bounded_to_eight_products(): void
    {
        for ($i = 0; $i < 12; $i++) {
            DB::table('products')->insert(['product_name' => 'Shoe '.$i, 'is_active' => true]);
        }
        $this->assertCount(8, (new HeroCarousel)->render()->getData()['products']);
    }
}
