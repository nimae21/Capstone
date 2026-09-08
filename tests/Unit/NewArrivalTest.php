<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\PageController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class NewArrivalTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null, 'session.driver' => 'array']);
        DB::purge('sqlite');
        $this->travelTo(now()->setDate(2026, 9, 8)->setTime(12, 0));
        Schema::create('products', function (Blueprint $table) {
            $table->increments('product_id');
            $table->string('product_name');
            $table->string('product_description')->nullable();
            $table->integer('category_id')->default(1);
            $table->integer('brand_id')->default(1);
            $table->integer('shoe_type_id')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        (require database_path('migrations/2026_09_08_000001_add_new_arrival_until_to_products_table.php'))->up();
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->increments('activity_log_id');
            $table->integer('user_id')->nullable();
            $table->string('action');
            $table->string('subject_type');
            $table->integer('subject_id');
            $table->text('changes')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
        foreach (['categories' => 'category_id', 'brands' => 'brand_id', 'shoe_types' => 'shoe_type_id'] as $name => $key) {
            Schema::create($name, function (Blueprint $table) use ($key, $name) {
                $table->increments($key);
                $table->boolean('is_active')->default(true);
                $table->integer('display_order')->default(0);
                $table->string($name === 'categories' ? 'category_name' : ($name === 'brands' ? 'brand_name' : 'shoe_type_name'))->default('Test');
            });
            DB::table($name)->insert([$key => 1]);
        }
        Schema::create('product_variants', function (Blueprint $table) {
            $table->increments('product_variant_id');
            $table->integer('product_id');
        });
        Schema::create('stocks', function (Blueprint $table) {
            $table->increments('stock_id');
            $table->integer('product_variant_id');
            $table->decimal('price');
        });
        Schema::create('product_images', function (Blueprint $table) {
            $table->increments('image_id');
            $table->integer('product_id');
            $table->integer('display_order');
        });
    }

    public function test_default_is_thirty_days_and_edits_do_not_renew_it(): void
    {
        $product = Product::create(['product_name' => 'Runner'])->fresh();
        $expires = now()->addDays(30)->endOfDay()->format('Y-m-d H:i:s');
        $this->assertSame($expires, $product->new_arrival_until->format('Y-m-d H:i:s'));
        $this->assertTrue($product->is_new_arrival);
        $this->travel(5)->days();
        $product->update(['product_name' => 'Renamed Runner']);
        $this->assertSame($expires, $product->fresh()->new_arrival_until->format('Y-m-d H:i:s'));
    }

    public function test_expiration_boundary_null_and_inactive_products_are_not_new(): void
    {
        $new = Product::create(['product_name' => 'New', 'new_arrival_until' => now()->addSecond()])->fresh();
        $off = Product::create(['product_name' => 'Off', 'new_arrival_until' => null])->fresh();
        $inactive = Product::create(['product_name' => 'Inactive', 'is_active' => false])->fresh();
        $this->assertTrue($new->is_new_arrival);
        $this->assertFalse($off->is_new_arrival);
        $this->assertFalse($inactive->is_new_arrival);
        $this->travel(1)->seconds();
        $this->assertFalse($new->is_new_arrival);
        $this->assertSame(0, Product::newArrivals()->count());
    }

    public function test_migration_leaves_existing_products_untagged(): void
    {
        $migration = require database_path('migrations/2026_09_08_000001_add_new_arrival_until_to_products_table.php');
        $migration->down();
        DB::table('products')->insert(['product_name' => 'Existing inventory']);
        $migration->up();
        $this->assertNull(Product::first()->new_arrival_until);
        $this->assertFalse(Product::first()->is_new_arrival);
    }

    public function test_admin_can_override_clear_and_preserve_the_date(): void
    {
        $product = Product::create(['product_name' => 'Runner'])->fresh();
        $fields = ['product_name' => 'Runner', 'category_id' => 1, 'brand_id' => 1, 'shoe_type_id' => 1];
        $controller = app(ProductController::class);
        $controller->update(Request::create('/admin/products/1', 'PUT', $fields + ['new_arrival_until' => '2026-10-20']), $product);
        $this->assertSame('2026-10-20 23:59:59', $product->fresh()->new_arrival_until->format('Y-m-d H:i:s'));
        $controller->update(Request::create('/admin/products/1', 'PUT', $fields), $product);
        $this->assertSame('2026-10-20', $product->fresh()->new_arrival_until->format('Y-m-d'));
        $controller->update(Request::create('/admin/products/1', 'PUT', $fields + ['new_arrival_until' => null]), $product);
        $this->assertNull($product->fresh()->new_arrival_until);
    }

    public function test_admin_creation_respects_a_cleared_or_custom_date(): void
    {
        $fields = ['category_id' => 1, 'brand_id' => 1, 'shoe_type_id' => 1];
        $controller = app(ProductController::class);
        $controller->store(Request::create('/admin/products', 'POST', $fields + ['product_name' => 'No tag', 'new_arrival_until' => null]));
        $this->assertNull(Product::where('product_name', 'No Tag')->firstOrFail()->new_arrival_until);
        $controller->store(Request::create('/admin/products', 'POST', $fields + ['product_name' => 'Custom tag', 'new_arrival_until' => '2026-10-01']));
        $this->assertSame('2026-10-01 23:59:59', Product::where('product_name', 'Custom Tag')->firstOrFail()->new_arrival_until->format('Y-m-d H:i:s'));
    }

    public function test_admin_rejects_an_invalid_expiration_date(): void
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        app(ProductController::class)->store(Request::create('/admin/products', 'POST', [
            'product_name' => 'Invalid date', 'category_id' => 1, 'brand_id' => 1, 'shoe_type_id' => 1, 'new_arrival_until' => 'not-a-date',
        ]));
    }

    public function test_new_page_includes_all_categories_and_excludes_old_stock(): void
    {
        foreach ([1, 2, 5] as $category) Product::create(['product_name' => 'Shoe '.$category, 'category_id' => $category]);
        Product::create(['product_name' => 'Expired', 'new_arrival_until' => now()->subDay()]);
        Product::create(['product_name' => 'Untagged', 'new_arrival_until' => null]);
        Product::create(['product_name' => 'Inactive', 'is_active' => false]);
        $view = app(PageController::class)->new(Request::create('/new'));
        $this->assertEqualsCanonicalizing([1, 2, 5], $view->getData()['products']->pluck('category_id')->all());
        $html = $view->render();
        $this->assertStringContainsString('Shoe 5', $html);
        $this->assertStringNotContainsString('Expired', $html);
        $this->assertStringNotContainsString('Untagged', $html);
    }
}
