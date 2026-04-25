@extends('layouts.app')

@section('title', $product->product_name . ' - ACHILLES')

@section('styles')
<style>
    /* Cinematic Animations */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    @keyframes pulseRed {
        0%, 100% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.4); }
        50% { box-shadow: 0 0 0 8px rgba(220, 38, 38, 0); }
    }
    
    @keyframes shimmer {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }
    
    .product-page {
        animation: fadeIn 0.6s ease-out;
    }
    
    .variant-btn, .size-btn {
        transition: all 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }
    
    .variant-btn::before, .size-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: left 0.5s ease;
    }
    
    .variant-btn:hover::before, .size-btn:hover::before {
        left: 100%;
    }
    
    .variant-btn.active, .size-btn.active {
        background: linear-gradient(135deg, #dc2626, #b91c1c) !important;
        color: white !important;
        border-color: transparent !important;
        transform: scale(1.02);
        box-shadow: 0 8px 20px -8px rgba(220, 38, 38, 0.5);
    }
    
    .glass-card {
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.5);
        transition: all 0.3s ease;
    }
    
    .glass-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 25px 35px -12px rgba(0, 0, 0, 0.15);
    }
    
    .gradient-text {
        background: linear-gradient(135deg, #000000, #dc2626);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }
    
    .quantity-input {
        transition: all 0.2s ease;
    }
    
    .quantity-input:focus {
        border-color: #dc2626;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        outline: none;
        transform: translateY(-1px);
    }
    
    .add-to-cart-btn {
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .add-to-cart-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: left 0.5s ease;
    }
    
    .add-to-cart-btn:hover::before {
        left: 100%;
    }
    
    .add-to-cart-btn:active {
        transform: scale(0.98);
    }
    
    .stock-badge {
        background: linear-gradient(135deg, #d1fae5, #a7f3d0);
        color: #065f46;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .stock-badge.low {
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        color: #92400e;
    }
    
    .stock-badge.out {
        background: linear-gradient(135deg, #fee2e2, #fecaca);
        color: #991b1b;
    }
    
    /* Loading overlay */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        backdrop-filter: blur(4px);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }
    
    .loading-spinner {
        width: 60px;
        height: 60px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #dc2626;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endsection

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50 py-12 relative overflow-hidden">
    
    <!-- Cinematic Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-red-100 rounded-full mix-blend-multiply filter blur-3xl opacity-20"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gray-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20"></div>
    </div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        
        <!-- Back Button -->
        <div class="mb-8">
            <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 text-red-600 hover:text-red-700 font-semibold transition-all hover:translate-x-[-4px]">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            
            <!-- Product Image Gallery -->
            <div class="space-y-4">
                <div class="glass-card rounded-2xl p-4 shadow-xl overflow-hidden">
                    <div class="aspect-square bg-gradient-to-br from-gray-100 to-gray-200 rounded-xl flex items-center justify-center">
                        @if($product->images->first())
                            <img src="{{ asset('storage/' . $product->images->first()->image_path) }}" 
                                 alt="{{ $product->product_name }}" 
                                 class="w-full h-full object-cover rounded-xl transition-transform duration-500 hover:scale-110">
                        @else
                            <i class="fas fa-shoe-prints text-gray-400 text-6xl"></i>
                        @endif
                    </div>
                </div>
                
                <!-- Thumbnail Gallery (optional) -->
                @if($product->images->count() > 1)
                <div class="flex gap-3 overflow-x-auto pb-2 custom-scroll">
                    @foreach($product->images as $image)
                        <div class="w-20 h-20 bg-gray-100 rounded-lg overflow-hidden cursor-pointer hover:ring-2 hover:ring-red-500 transition-all">
                            <img src="{{ asset('storage/' . $image->image_path) }}" 
                                 alt="Thumbnail" 
                                 class="w-full h-full object-cover">
                        </div>
                    @endforeach
                </div>
                @endif
            </div>
            
            <!-- Product Info -->
            <div class="space-y-6">
                <!-- Title & Brand -->
                <div>
                    <div class="inline-block px-3 py-1 bg-red-100 text-red-600 rounded-full text-xs font-semibold mb-3">
                        <i class="fas fa-tag mr-1"></i> New Arrival
                    </div>
                    <h1 class="text-4xl md:text-5xl font-black gradient-text mb-2">{{ $product->product_name }}</h1>
                    <p class="text-gray-500 text-sm">{{ $product->category->category_name ?? 'Uncategorized' }} | {{ $product->brand->brand_name ?? 'No Brand' }}</p>
                </div>
                
                <!-- Description -->
                <div class="glass-card rounded-2xl p-5">
                    <p class="text-gray-700 leading-relaxed">{{ $product->product_description ?? 'No description available for this product.' }}</p>
                </div>
                
                <!-- Color Selection -->
                <div>
                    <h4 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                        <i class="fas fa-palette text-red-600"></i>
                        Select Color
                    </h4>
                    <div id="variant-container" class="flex flex-wrap gap-3">
                        @foreach($product->variants->unique('color') as $variant)
                            <button 
                                class="variant-btn px-5 py-2.5 bg-white border-2 border-gray-200 rounded-full font-medium text-gray-700 hover:border-red-500 transition-all shadow-sm"
                                data-id="{{ $variant->product_variant_id }}"
                                data-color="{{ $variant->color }}">
                                {{ $variant->color }}
                            </button>
                        @endforeach
                    </div>
                </div>
                
                <!-- Size Selection -->
                <div>
                    <h4 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                        <i class="fas fa-ruler-combined text-red-600"></i>
                        Select Size
                    </h4>
                    <div id="size-container" class="flex flex-wrap gap-3">
                        <div class="text-gray-400 italic py-3">Select color first</div>
                    </div>
                </div>
                
                <!-- Price & Stock Info -->
                <div class="bg-gradient-to-r from-gray-50 to-white rounded-2xl p-5 flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Price</p>
                        <p class="text-3xl font-black text-red-600">
                            ₱<span id="priceDisplay">0</span>
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500 mb-1">Availability</p>
                        <p><span id="stockDisplay" class="stock-badge">Select size</span></p>
                    </div>
                </div>
                
                <!-- Add to Cart Form -->
                <form action="{{ route('cart.add') }}" method="POST" id="addToCartForm">
                    @csrf
                    <input type="hidden" name="product_variant_id" id="selectedVariant">
                    <input type="hidden" name="size" id="selectedSize">
                    
                    <div class="flex gap-4 items-center mb-6">
                        <div class="flex-1">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Quantity</label>
                            <input type="number" 
                                   name="quantity" 
                                   min="1" 
                                   value="1" 
                                   class="quantity-input w-32 px-4 py-2.5 border border-gray-200 rounded-xl focus:border-red-500 transition-all">
                        </div>
                    </div>
                    
                    <button type="submit" 
                            onclick="return validateSelection()" 
                            class="add-to-cart-btn w-full bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-bold py-4 rounded-xl transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-1 text-lg">
                        <i class="fas fa-shopping-cart mr-2"></i> Add to Cart
                    </button>
                </form>
                
                <!-- Benefits -->
                <div class="grid grid-cols-3 gap-3 pt-4">
                    <div class="text-center p-3 bg-gray-50 rounded-xl">
                        <i class="fas fa-truck-fast text-red-600 text-xl mb-1 block"></i>
                        <p class="text-xs font-semibold text-gray-700">Free Shipping</p>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-xl">
                        <i class="fas fa-rotate-left text-red-600 text-xl mb-1 block"></i>
                        <p class="text-xs font-semibold text-gray-700">60-Day Returns</p>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-xl">
                        <i class="fas fa-shield-alt text-red-600 text-xl mb-1 block"></i>
                        <p class="text-xs font-semibold text-gray-700">Authentic Guarantee</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="loading-overlay">
    <div class="loading-spinner"></div>
</div>

<script>
const variants = @json($product->variants);

let selectedVariant = null;
let selectedSize = null;

/* =========================
   COLOR CLICK
========================= */
document.querySelectorAll('.variant-btn').forEach(button => {
    button.addEventListener('click', function () {
        // Remove active class from all color buttons
        document.querySelectorAll('.variant-btn').forEach(btn => btn.classList.remove('active'));
        
        // Add active class to clicked button
        this.classList.add('active');
        
        // Store selected variant ID
        selectedVariant = this.dataset.id;
        document.getElementById('selectedVariant').value = selectedVariant;
        
        // Reset size selection
        selectedSize = null;
        document.getElementById('selectedSize').value = '';
        
        // Render sizes for this variant
        renderSizes(selectedVariant);
        
        // Reset price and stock display
        document.getElementById('priceDisplay').innerText = '0';
        const stockSpan = document.getElementById('stockDisplay');
        stockSpan.innerText = 'Select size';
        stockSpan.className = 'stock-badge';
    });
});

/* =========================
   RENDER SIZES (FROM VARIANT)
========================= */
function renderSizes(variantId) {
    const sizeContainer = document.getElementById('size-container');
    sizeContainer.innerHTML = '';
    
    const variant = variants.find(v => v.product_variant_id == variantId);
    
    if (!variant) {
        console.error("Variant not found");
        sizeContainer.innerHTML = '<div class="text-red-500 italic py-3">Size not available</div>';
        return;
    }
    
    // Create size button
    const button = document.createElement('button');
    button.classList.add('size-btn', 'px-5', 'py-2.5', 'bg-white', 'border-2', 'border-gray-200', 'rounded-full', 'font-medium', 'text-gray-700', 'hover:border-red-500', 'transition-all', 'shadow-sm');
    button.dataset.size = variant.size;
    button.innerText = variant.size;
    
    sizeContainer.appendChild(button);
    
    attachSizeEvents();
    
    // Get stock info from stock table
    const stock = variant.stocks?.[0];
    if (stock) {
        document.getElementById('priceDisplay').innerText = stock.price;
        updateStockDisplay(stock.quantity);
    }
}

/* =========================
   SIZE CLICK
========================= */
function attachSizeEvents() {
    document.querySelectorAll('.size-btn').forEach(button => {
        button.addEventListener('click', function () {
            // Remove active class from all size buttons
            document.querySelectorAll('.size-btn').forEach(btn => btn.classList.remove('active'));
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // Store selected size
            selectedSize = this.dataset.size;
            document.getElementById('selectedSize').value = selectedSize;
            
            // Update stock info based on selected variant
            const variant = variants.find(v => v.product_variant_id == selectedVariant);
            const stock = variant.stocks?.[0];
            
            if (stock) {
                document.getElementById('priceDisplay').innerText = stock.price;
                updateStockDisplay(stock.quantity);
            }
        });
    });
}

/* =========================
   UPDATE STOCK DISPLAY
========================= */
function updateStockDisplay(quantity) {
    const stockSpan = document.getElementById('stockDisplay');
    
    if (quantity <= 0) {
        stockSpan.innerText = 'Out of Stock';
        stockSpan.className = 'stock-badge out';
    } else if (quantity <= 5) {
        stockSpan.innerText = 'Low Stock (' + quantity + ' left)';
        stockSpan.className = 'stock-badge low';
    } else {
        stockSpan.innerText = 'In Stock (' + quantity + ' available)';
        stockSpan.className = 'stock-badge';
    }
}

/* =========================
   VALIDATION
========================= */
function validateSelection() {
    if (!selectedVariant) {
        alert('Please select a color first');
        return false;
    }
    if (!selectedSize) {
        alert('Please select a size');
        return false;
    }
    
    // Show loading overlay
    const overlay = document.getElementById('loadingOverlay');
    overlay.style.display = 'flex';
    
    return true;
}

// Hide loading overlay after form submission (if validation passes)
document.getElementById('addToCartForm')?.addEventListener('submit', function() {
    if (validateSelection()) {
        const overlay = document.getElementById('loadingOverlay');
        overlay.style.display = 'flex';
    }
});

// Auto-hide loading overlay if there's an error
window.addEventListener('pageshow', function() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) overlay.style.display = 'none';
});

// Pre-select if there's only one variant
document.addEventListener('DOMContentLoaded', function() {
    const colorButtons = document.querySelectorAll('.variant-btn');
    if (colorButtons.length === 1) {
        colorButtons[0].click();
    }
});
</script>
@endsection