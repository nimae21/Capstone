<?php

use App\Http\Controllers\PageController;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ShoeType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

it('loads catalog card data with a bounded query count and one image per product', function (string $page, int $categoryId) {
    DB::table('categories')->insert(['category_id' => $categoryId, 'category_name' => 'Catalog']);
    $brand = Brand::create(['brand_name' => 'Catalog Brand', 'is_active' => true]);
    $type = ShoeType::create(['shoe_type_name' => 'Catalog Type', 'is_active' => true, 'display_order' => 0]);
    foreach (range(1, 9) as $number) {
        $product = Product::create([
            'category_id' => $categoryId, 'brand_id' => $brand->brand_id,
            'shoe_type_id' => $type->shoe_type_id, 'product_name' => 'Shoe '.$number,
            'is_active' => true,
        ]);
        foreach ([0, 1, 2] as $order) {
            ProductImage::create(['product_id' => $product->product_id,
                'image_path' => 'shoe-'.$order.'.jpg', 'display_order' => $order]);
        }
    }
    $controller = app(PageController::class);
    $request = Request::create('/'.$page);
    $controller->$page($request); // Warm filter cache.
    DB::enableQueryLog();
    DB::flushQueryLog();
    $products = $controller->$page($request)->getData()['products'];
    expect($products->total())->toBe(9);
    foreach ($products as $product) {
        expect($product->images)->toHaveCount(1);
        expect($product->images->first()->image_path)->toBe('shoe-0.jpg');
        expect($product->brand->brand_name)->toBe('Catalog Brand');
        expect($product->shoeType->shoe_type_name)->toBe('Catalog Type');
        expect($product->relationLoaded('variants'))->toBeFalse();
        expect($product->display_price)->toBeNull();
    }
    expect(DB::getQueryLog())->toHaveCount(5);
    DB::disableQueryLog();
})->with([['new', 1], ['men', 1], ['women', 2], ['kids', 5]]);

it('reuses filter options and invalidates them after admin model changes', function () {
    $brand = Brand::create(['brand_name' => 'Old Brand', 'is_active' => true]);
    $type = ShoeType::create(['shoe_type_name' => 'Type', 'is_active' => true, 'display_order' => 0]);
    $controller = app(PageController::class);
    $request = Request::create('/men');
    $controller->men($request);
    expect(Cache::has('catalog.filter-options.v1'))->toBeTrue();
    $brand->update(['brand_name' => 'Updated Brand']);
    expect(Cache::has('catalog.filter-options.v1'))->toBeFalse();
    expect($controller->men($request)->getData()['brands']->first()->brand_name)->toBe('Updated Brand');
    $type->update(['is_active' => false]);
    expect(Cache::has('catalog.filter-options.v1'))->toBeFalse();
    expect($controller->men($request)->getData()['shoeTypes'])->toHaveCount(0);
    $brand->delete();
    expect(Cache::has('catalog.filter-options.v1'))->toBeFalse();
});