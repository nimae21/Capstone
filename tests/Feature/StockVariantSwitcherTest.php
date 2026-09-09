<?php

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

it('renders distinct color groups and links each size to its own stock page', function () {
    $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
    $category = DB::table('categories')->insertGetId(['category_name' => 'Test'], 'category_id');
    $brand = DB::table('brands')->insertGetId(['brand_name' => 'Test'], 'brand_id');
    $type = DB::table('shoe_types')->insertGetId(['shoe_type_name' => 'Test'], 'shoe_type_id');
    $product = Product::create([
        'category_id' => $category, 'brand_id' => $brand, 'shoe_type_id' => $type,
        'product_name' => 'Switcher Test Shoe',
    ]);
    $variants = collect(['Blue/White', 'Blue White'])->map(fn ($color) => ProductVariant::create([
        'product_id' => $product->product_id, 'color' => $color, 'size' => '42', 'is_active' => true,
    ]));
    foreach ($variants as $variant) {
        $response = $this->actingAs($admin)->get(route('admin.stocks.index', $variant))->assertOk();
        $response->assertSee('value="'.$variant->color.'" selected', false)
            ->assertSee('data-color="Blue/White"', false)
            ->assertSee('data-color="Blue White"', false)
            ->assertSee('.size-switcher[hidden] { display: none; }', false)
            ->assertSee('Stock Summary');
        $html = $response->getContent();
        expect(strpos($html, 'stock-variant-switcher.js'))->toBeGreaterThan(strpos($html, 'id="variantColor"'));
        foreach ($variants as $target) {
            $response->assertSee(route('admin.stocks.index', ['variant' => $target->product_variant_id]), false);
        }
    }
});
