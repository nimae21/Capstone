@extends('layouts.app')

@section('title', 'Shopping Cart - ACHILLES')

@section('styles')
<style>
    /* Custom animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes pulseRed {
        0%, 100% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.4); }
        50% { box-shadow: 0 0 0 8px rgba(220, 38, 38, 0); }
    }
    
    .cart-item {
        animation: fadeInUp 0.5s ease-out;
        transition: all 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
    }
    
    .cart-item:hover {
        transform: translateY(-4px) translateX(4px);
        box-shadow: 0 15px 30px -12px rgba(0, 0, 0, 0.15);
    }
    
    .quantity-btn {
        transition: all 0.2s ease;
        cursor: pointer;
    }
    
    .quantity-btn:active {
        transform: scale(0.95);
    }
    
    .quantity-btn.disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    .glass-card {
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.5);
    }
    
    .gradient-text {
        background: linear-gradient(135deg, #000000, #dc2626);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }
    
    @keyframes shimmer {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }
    
    .checkout-btn {
        position: relative;
        overflow: hidden;
    }
    
    .checkout-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: left 0.5s ease;
    }
    
    .checkout-btn:hover::before {
        left: 100%;
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
            
            <!-- Header -->
            <div class="mb-10">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-red-600 hover:text-red-700 font-semibold transition-all hover:translate-x-[-4px] mb-6">
                    <i class="fas fa-arrow-left"></i> Back to Shop
                </a>
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-red-700 rounded-2xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-shopping-cart text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-5xl font-black gradient-text">Shopping Cart</h1>
                        <p class="text-gray-500 mt-2">Review your items before checkout</p>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-xl shadow-md animate-pulse">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-check-circle text-green-500 text-xl"></i>
                        <p class="text-green-700 font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-xl shadow-md">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                        <p class="text-red-700 font-medium">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            @if($cart && $cart->items->count() > 0)
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Cart Items -->
                    <div class="lg:col-span-2 space-y-4">
                        <div class="glass-card rounded-2xl p-6 shadow-xl border border-gray-100">
                            <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200">
                                <h2 class="text-xl font-bold flex items-center gap-2">
                                    <i class="fas fa-shopping-bag text-red-600"></i>
                                    <span class="gradient-text">Cart Items</span>
                                </h2>
                                <span class="px-3 py-1 bg-red-100 text-red-600 rounded-full text-sm font-semibold">
                                    {{ $cart->items->count() }} {{ Str::plural('item', $cart->items->count()) }}
                                </span>
                            </div>

                            <div class="space-y-4">
                                @php $total = 0; @endphp
                                @foreach($cart->items as $item)
                                    @php
                                        $variant = $item->variant;
                                        $product = $variant->product;
                                        $stock = $variant->stocks()->latest()->first();
                                        $subtotal = $item->price * $item->quantity;
                                        $total += $subtotal;
                                    @endphp
                                    
                                    <div class="cart-item bg-white rounded-xl p-5 border border-gray-100 shadow-sm hover:shadow-xl transition-all">
                                        <div class="flex gap-5">
                                            <!-- Product Image -->
                                            <div class="w-28 h-28 bg-gradient-to-br from-gray-100 to-gray-200 rounded-xl flex-shrink-0 flex items-center justify-center overflow-hidden shadow-md">
                                                @if($product->images->first())
                                                    <img src="{{ asset('storage/' . $product->images->first()->image_path) }}" alt="{{ $product->product_name }}" class="w-full h-full object-cover rounded-xl transition-transform duration-300 hover:scale-110">
                                                @else
                                                    <i class="fas fa-shoe-prints text-gray-400 text-3xl"></i>
                                                @endif
                                            </div>

                                            <!-- Product Details -->
                                            <div class="flex-1">
                                                <h3 class="font-bold text-lg text-gray-900">{{ $product->product_name }}</h3>
                                                <div class="flex gap-2 mt-1">
                                                    <span class="inline-block px-2 py-0.5 bg-gray-100 rounded-md text-xs font-medium text-gray-600">
                                                        <i class="fas fa-ruler-combined mr-1"></i>{{ $variant->size }}
                                                    </span>
                                                    <span class="inline-block px-2 py-0.5 bg-gray-100 rounded-md text-xs font-medium text-gray-600">
                                                        <i class="fas fa-palette mr-1"></i>{{ $variant->color }}
                                                    </span>
                                                </div>
                                                <p class="text-2xl font-bold text-red-600 mt-2">₱{{ number_format($item->price, 2) }}</p>
                                            </div>

                                            <!-- Quantity Control -->
                                            <div class="flex flex-col items-center justify-between gap-2">
                                                <div class="flex items-center gap-2 bg-gray-100 rounded-full p-1 shadow-inner">
                                                    <form action="{{ route('cart.decrease', $item->cart_item_id) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="quantity-btn w-8 h-8 bg-white hover:bg-red-500 hover:text-white rounded-full text-gray-700 font-bold transition-all shadow-sm" {{ $item->quantity <= 1 ? 'disabled' : '' }}>
                                                            <i class="fas fa-minus text-xs"></i>
                                                        </button>
                                                    </form>
                                                    
                                                    <span class="font-bold text-gray-900 w-8 text-center text-lg">{{ $item->quantity }}</span>
                                                    
                                                    <form action="{{ route('cart.increase', $item->cart_item_id) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="quantity-btn w-8 h-8 bg-white hover:bg-red-500 hover:text-white rounded-full text-gray-700 font-bold transition-all shadow-sm" {{ $item->quantity >= ($stock?->quantity ?? 0) ? 'disabled' : '' }}>
                                                            <i class="fas fa-plus text-xs"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                                
                                                <form action="{{ route('cart.remove', $item->cart_item_id) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-gray-400 hover:text-red-600 font-medium text-sm transition-all hover:scale-105">
                                                        <i class="fas fa-trash-alt mr-1"></i> Remove
                                                    </button>
                                                </form>
                                            </div>

                                            <!-- Subtotal -->
                                            <div class="flex flex-col items-end justify-between min-w-[100px]">
                                                <p class="font-bold text-gray-900 text-lg">₱{{ number_format($subtotal, 2) }}</p>
                                                <p class="text-xs text-gray-400 mt-1">Subtotal</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        
                        <!-- Continue Shopping -->
                        <div class="text-center">
                            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-gray-500 hover:text-red-600 transition-all hover:translate-x-[-2px]">
                                <i class="fas fa-arrow-left"></i> Continue Shopping
                            </a>
                        </div>
                    </div>

                    <!-- Order Summary Sidebar -->
                    <div>
                        <div class="glass-card rounded-2xl p-6 shadow-xl sticky top-24 border border-gray-100">
                            <h2 class="text-xl font-bold mb-6 flex items-center gap-2 pb-4 border-b border-gray-200">
                                <i class="fas fa-receipt text-red-600"></i>
                                <span class="gradient-text">Order Summary</span>
                            </h2>

                            <div class="space-y-4 mb-6 pb-6 border-b border-gray-200">
                                <div class="flex justify-between text-gray-600">
                                    <span>Subtotal:</span>
                                    <span class="font-semibold">₱{{ number_format($total, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-gray-600">
                                    <span>Shipping:</span>
                                    <span class="font-semibold text-green-600 flex items-center gap-1">
                                        <i class="fas fa-truck"></i> FREE
                                    </span>
                                </div>
                                <div class="flex justify-between text-gray-600">
                                    <span>Tax (12% VAT):</span>
                                    <span class="font-semibold">₱{{ number_format($total * 0.12, 2) }}</span>
                                </div>
                                <div class="h-px bg-gradient-to-r from-transparent via-gray-300 to-transparent my-2"></div>
                                <div class="flex justify-between text-xl font-bold">
                                    <span>Total:</span>
                                    <span class="text-red-600 text-2xl">₱{{ number_format($total + ($total * 0.12), 2) }}</span>
                                </div>
                            </div>

                            <!-- Checkout Button -->
                            <a href="{{ route('checkout.index') }}" class="checkout-btn block w-full bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-bold py-3.5 rounded-xl text-center transition-all shadow-lg hover:shadow-xl mb-4 transform hover:-translate-y-1">
                                <i class="fas fa-credit-card mr-2"></i> Proceed to Checkout
                            </a>

                            <!-- Payment Methods -->
                            <div class="text-center pt-4 border-t border-gray-200">
                                <p class="text-xs text-gray-500 mb-3">Secure payment methods</p>
                                <div class="flex justify-center gap-3 text-2xl text-gray-400">
                                    <i class="fab fa-cc-visa hover:text-blue-600 transition-colors"></i>
                                    <i class="fab fa-cc-mastercard hover:text-red-600 transition-colors"></i>
                                    <i class="fab fa-cc-paypal hover:text-blue-400 transition-colors"></i>
                                    <i class="fab fa-cc-amex hover:text-blue-600 transition-colors"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- Empty Cart - Magnificent Design -->
                <div class="glass-card rounded-2xl p-20 shadow-xl border border-gray-100 text-center relative overflow-hidden">
                    <div class="relative z-10">
                        <div class="mb-8">
                            <div class="w-32 h-32 mx-auto bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center animate-pulse">
                                <i class="fas fa-shopping-cart text-5xl text-gray-400"></i>
                            </div>
                        </div>
                        <h2 class="text-3xl font-bold gradient-text mb-3">Your cart is empty</h2>
                        <p class="text-gray-500 mb-8 max-w-md mx-auto">You haven't added any items to your cart yet. Start shopping to add items!</p>
                        <a href="{{ route('home') }}" class="inline-block bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-bold py-3.5 px-10 rounded-xl transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                            <i class="fas fa-store mr-2"></i> Start Shopping
                        </a>
                    </div>
                    
                    <!-- Decorative Elements -->
                    <div class="absolute top-0 right-0 w-64 h-64 bg-red-100 rounded-full filter blur-3xl opacity-20"></div>
                    <div class="absolute bottom-0 left-0 w-64 h-64 bg-gray-200 rounded-full filter blur-3xl opacity-20"></div>
                </div>
            @endif
        </div>
    </div>

    <script>
        // Add loading state to forms
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const btn = this.querySelector('button[type="submit"]');
                if(btn && btn.disabled) {
                    e.preventDefault();
                } else if(btn && !btn.disabled) {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Processing...';
                }
            });
        });
        
        // Remove animation on cart item removal
        const removeButtons = document.querySelectorAll('.cart-item form button[type="submit"]');
        removeButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                const cartItem = this.closest('.cart-item');
                if(cartItem) {
                    cartItem.style.opacity = '0';
                    cartItem.style.transform = 'translateX(100px)';
                }
            });
        });
    </script>
@endsection