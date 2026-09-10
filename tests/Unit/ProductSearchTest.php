<?php

namespace Tests\Unit;

use App\Http\Controllers\PageController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProductSearchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null, 'session.driver' => 'array']);
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
            $table->string('image_path');
        });
    }

    public function test_suggestions_match_active_products_case_insensitively_and_limit_results(): void
    {
        for ($i = 1; $i <= 7; $i++) {
            DB::table('products')->insert(['product_name' => 'Runner '.$i, 'is_active' => true]);
        }
        DB::table('products')->insert(['product_name' => 'Runner 0 hidden', 'is_active' => false]);
        DB::table('products')->insert(['product_name' => 'Basketball', 'is_active' => true]);
        $response = app(PageController::class)->searchSuggestions(Request::create('/search/suggestions', 'GET', ['q' => ' RUNNER ']));
        $products = $response->getData(true)['products'];
        $this->assertCount(3, $products);
        $this->assertSame('Runner 1', $products[0]['name']);
        $this->assertNull($products[0]['image']);
        $this->assertStringEndsWith('/product/1', $products[0]['url']);
    }

    public function test_empty_short_and_unmatched_queries_return_no_suggestions(): void
    {
        foreach (['', ' ', 'a', 'unmatched'] as $query) {
            $response = app(PageController::class)->searchSuggestions(Request::create('/search/suggestions', 'GET', ['q' => $query]));
            $this->assertSame([], $response->getData(true)['products']);
        }
    }

    public function test_suggestions_select_one_preview_image_after_selecting_the_batch(): void
    {
        DB::table('products')->insert(['product_name' => 'Runner', 'is_active' => true]);
        DB::table('product_images')->insert([
            ['product_id' => 1, 'is_primary' => false, 'display_order' => 0, 'image_path' => 'other.jpg'],
            ['product_id' => 1, 'is_primary' => true, 'display_order' => 5, 'image_path' => 'primary.jpg'],
        ]);
        $disk = \Mockery::mock();
        $disk->shouldReceive('url')->once()->with('primary.jpg')->andReturn('https://images.example/primary.jpg');
        \Illuminate\Support\Facades\Storage::shouldReceive('disk')->with('supabase')->andReturn($disk);
        DB::enableQueryLog();
        DB::flushQueryLog();
        $response = app(PageController::class)->searchSuggestions(Request::create('/search/suggestions', 'GET', ['q' => 'runner']));
        $this->assertSame('https://images.example/primary.jpg', $response->getData(true)['products'][0]['image']);
        $this->assertCount(2, DB::getQueryLog());
        DB::disableQueryLog();
    }

    public function test_suggestions_rank_exact_then_prefix_then_contains_and_paginate_without_duplicates(): void
    {
        foreach (['Trail Runner', 'Runner Z', 'Runner', 'Runner A', 'Road Runner', 'Runner B', 'Runner C'] as $name) {
            DB::table('products')->insert(['product_name' => $name, 'is_active' => true]);
        }
        $names = [];
        foreach ([1, 2, 3] as $page) {
            $response = app(PageController::class)->searchSuggestions(Request::create('/search/suggestions', 'GET', ['q' => 'runner', 'page' => $page]));
            $data = $response->getData(true);
            $this->assertSame($page === 3 ? null : $page + 1, $data['next_page']);
            $this->assertLessThanOrEqual(3, count($data['products']));
            $names = array_merge($names, array_column($data['products'], 'name'));
        }
        $this->assertSame(['Runner', 'Runner A', 'Runner B', 'Runner C', 'Runner Z', 'Road Runner', 'Trail Runner'], $names);
    }

    public function test_search_wildcards_are_literal(): void
    {
        foreach (['Shoe 50%', 'Shoe 500', 'Shoe XX'] as $name) {
            DB::table('products')->insert(['product_name' => $name, 'is_active' => true]);
        }
        $data = app(PageController::class)->searchSuggestions(Request::create('/search/suggestions', 'GET', ['q' => '50%']))->getData(true);
        $this->assertSame(['Shoe 50%'], array_column($data['products'], 'name'));
        $this->assertNull($data['next_page']);
    }
    public function test_suggestions_require_login_and_sale_route_is_removed(): void
    {
        $this->getJson('/search/suggestions?q=runner')->assertRedirect(route('login'));
        $this->get('/sale')->assertNotFound();
    }
}
