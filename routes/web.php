<?php
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductVariantController;
use App\Http\Controllers\guest\GuestController;
use App\Http\Controllers\Admin\StockController;

//public routes
Route::get('/', [GuestController::class, 'index'])->name('index');



//auth routes
Auth::routes(); // adds login, register, password routes

// User dashboard
Route::middleware(['auth'])->group(function () {
    Route::get('/home', [PageController::class, 'home'])->name('home');
    Route::get('/men', [PageController::class, 'men'])->name('men');
    Route::get('/women', [PageController::class, 'women'])->name('women');
    Route::get('/kids', [PageController::class, 'kids'])->name('kids');
    Route::get('/sale', [PageController::class, 'sale'])->name('sale');
    Route::get('/new', [PageController::class, 'new'])->name('new');
});

//admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');


    //category routes
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');  
        
    //brands routes
        Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
        Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');
        Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->name('brands.destroy'); 
        
    //products routes
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');

 // Product Variant Routes

// List variants of a product
Route::get('/products/{product}/variants', [ProductVariantController::class, 'index'])
    ->name('products.variants.index');

// Store a new variant
Route::post('/products/{product}/variants', [ProductVariantController::class, 'store'])
    ->name('products.variants.store');

// Edit variant
Route::get('/variants/{variant}/edit', [ProductVariantController::class, 'edit'])
    ->name('variants.edit');

// Update variant
Route::put('/variants/{variant}', [ProductVariantController::class, 'update'])
    ->name('variants.update');

// Delete variant
Route::delete('/variants/{variant}', [ProductVariantController::class, 'destroy'])
    ->name('variants.destroy');

//Stock Routes

//Stock Routes
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
});


//ORDER ROUTES
Route::post('/checkout', [OrderController::class, 'store'])->name('checkout.store');

