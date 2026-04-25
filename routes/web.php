<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\PageController;
use App\Http\Controllers\Guest\GuestController;

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductVariantController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\InventoryController;

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\UserAddressController;



/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

// Landing page
Route::get('/', [GuestController::class, 'index'])->name('index');

// Product detail (PUBLIC - no login needed)
Route::get('/product/{id}', [PageController::class, 'showProduct'])
    ->name('product.show');


/*
|--------------------------------------------------------------------------
| AUTH ROUTES (Laravel built-in + email verification)
|--------------------------------------------------------------------------
*/

Auth::routes(['verify' => true]);


/*
|--------------------------------------------------------------------------
| USER ROUTES (MUST BE VERIFIED)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

    // Pages
    Route::get('/home', [PageController::class, 'home'])->name('home');
    Route::get('/men', [PageController::class, 'men'])->name('men');
    Route::get('/women', [PageController::class, 'women'])->name('women');
    Route::get('/kids', [PageController::class, 'kids'])->name('kids');
    Route::get('/sale', [PageController::class, 'sale'])->name('sale');
    Route::get('/new', [PageController::class, 'new'])->name('new');
    
    // All products listing (for back to shop button)
    Route::get('/products', [PageController::class, 'home'])->name('products.index');


    /*
    |--------------------------------------------------------------------------
    | CART ROUTES
    |--------------------------------------------------------------------------
    */

    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'store'])->name('cart.add');

    Route::patch('/cart/item/{id}/increase', [CartController::class, 'increase'])
        ->name('cart.increase');

    Route::patch('/cart/item/{id}/decrease', [CartController::class, 'decrease'])
        ->name('cart.decrease');

    Route::delete('/cart/item/{id}', [CartController::class, 'remove'])
        ->name('cart.remove');


    /*
    |--------------------------------------------------------------------------
    | CHECKOUT ROUTES
    |--------------------------------------------------------------------------
    */

    Route::get('/checkout', [CheckoutController::class, 'checkout'])
        ->name('checkout.index');

    Route::post('/checkout/place-order', [CheckoutController::class, 'placeOrder'])
        ->name('checkout.place-order');


    /*
    |--------------------------------------------------------------------------
    | ORDERS
    |--------------------------------------------------------------------------
    */

    Route::get('/orders', [CheckoutController::class, 'myOrders'])
        ->name('orders.index');

    Route::get('/orders/{id}', [CheckoutController::class, 'show'])
        ->name('orders.show');


    /*
    |--------------------------------------------------------------------------
    | ADDRESSES
    |--------------------------------------------------------------------------
    */

    Route::get('/addresses', [UserAddressController::class, 'index'])
        ->name('addresses.index');

    Route::get('/addresses/create', [UserAddressController::class, 'create'])
        ->name('addresses.create');

    Route::post('/addresses', [UserAddressController::class, 'store'])
        ->name('addresses.store');

    Route::get('/addresses/{address}/edit', [UserAddressController::class, 'edit'])
        ->name('addresses.edit');

    Route::put('/addresses/{address}', [UserAddressController::class, 'update'])
        ->name('addresses.update');

    Route::delete('/addresses/{address}', [UserAddressController::class, 'destroy'])
        ->name('addresses.destroy');

    Route::post('/addresses/{address}/set-default', [UserAddressController::class, 'setDefault'])
        ->name('addresses.set-default');
});


/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');


        // Categories
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');


        // Brands
        Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
        Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');
        Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->name('brands.destroy');
        Route::get('/brands/{brand}/edit', [BrandController::class, 'edit'])->name('brands.edit');
        Route::put('/brands/{brand}', [BrandController::class, 'update'])->name('brands.update');


        // Products
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');


        // Product Variants
        Route::get('/products/{product}/variants', [ProductVariantController::class, 'index'])
            ->name('products.variants.index');

        Route::post('/products/{product}/variants', [ProductVariantController::class, 'store'])
            ->name('products.variants.store');

        Route::get('/variants/{variant}/edit', [ProductVariantController::class, 'edit'])
            ->name('variants.edit');

        Route::put('/variants/{variant}', [ProductVariantController::class, 'update'])
            ->name('variants.update');

        Route::delete('/variants/{variant}', [ProductVariantController::class, 'destroy'])
            ->name('variants.destroy');


        // Stocks
        Route::get('/variants/{product_variant_id}/stocks', [StockController::class, 'index'])
            ->name('stocks.index');

        Route::post('/variants/{product_variant_id}/stocks', [StockController::class, 'store'])
            ->name('stocks.store');

        Route::get('/stocks/{stock}/edit', [StockController::class, 'edit'])
            ->name('stocks.edit');

        Route::put('/stocks/{stock}', [StockController::class, 'update'])
            ->name('stocks.update');

        Route::delete('/stocks/{stock}', [StockController::class, 'destroy'])
            ->name('stocks.destroy');


        // Users Management
        Route::get('/users', [AdminUserController::class, 'index'])
            ->name('users.index');

        Route::get('/users/create-admin', [AdminUserController::class, 'createAdmin'])
            ->name('users.create-admin');

        Route::post('/users/create-admin', [AdminUserController::class, 'storeAdmin'])
            ->name('users.store-admin');

        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])
            ->name('users.edit');

        Route::put('/users/{user}', [AdminUserController::class, 'update'])
            ->name('users.update');

        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])
            ->name('users.destroy');


        // Orders Management
        Route::get('/orders', [AdminOrderController::class, 'index'])
            ->name('orders.index');

        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])
            ->name('orders.show');

        Route::put('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])
            ->name('orders.update-status');
        //reports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        
        //inventory routes
        Route::get('/inventory', [InventoryController::class, 'index'])
        ->name('inventory.index');
    });