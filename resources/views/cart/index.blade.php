@extends('layouts.app')

@section('title', 'Shopping Cart - ACHILLES')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50 py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="mb-8">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-red-600 hover:text-red-700 font-semibold mb-4">
                    <i class="fas fa-arrow-left"></i> Back to Shop
                </a>
                <h1 class="text-4xl font-black bg-gradient-to-r from-black to-red-600 bg-clip-text text-transparent">Your Shopping Cart</h1>
                <p class="text-gray-600 mt-2">Review your items before checkout</p>
            </div>

            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-lg">
                    <p class="text-green-700 font-medium">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
                    <p class="text-red-700 font-medium">{{ session('error') }}</p>
                </div>
            @endif

            @if($cart && $cart->items->count() > 0)
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Cart Items -->
                    <div class="lg:col-span-2 space-y-4">
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                            <h2 class="text-lg font-bold mb-6 flex items-center gap-2">
                                <i class="fas fa-shopping-bag text-red-600"></i>
                                <span>Cart Items ({{ $cart->items->count() }})</span>
                            </h2>

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
                                    
                                    <div class="border border-gray-200 rounded-xl p-4 flex gap-4 hover:shadow-md transition-shadow">
                                        <!-- Product Image -->
                                        <div class="w-24 h-24 bg-gray-100 rounded-lg flex-shrink-0 flex items-center justify-center">
                                            @if($product->images->first())
                                                <img src="{{ asset('storage/' . $product->images->first()->image_path) }}" alt="{{ $product->product_name }}" class="w-full h-full object-cover rounded-lg">
                                            @else
                                                <i class="fas fa-shoe-prints text-gray-400 text-2xl"></i>
                                            @endif
                                        </div>

                                        <!-- Product Details -->
                                        <div class="flex-1">
                                            <p class="font-bold text-gray-900">{{ $product->product_name }}</p>
                                            <p class="text-sm text-gray-600">{{ $variant->size }} / {{ $variant->color }}</p>
                                            <p class="text-sm font-semibold text-red-600 mt-1">₱{{ number_format($item->price, 2) }}</p>
                                        </div>

                                        <!-- Quantity Control -->
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <form action="{{ route('cart.increase', $item->cart_item_id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="w-8 h-8 bg-gray-200 hover:bg-gray-300 rounded text-gray-700 font-bold" {{ $item->quantity >= ($stock?->quantity ?? 0) ? 'disabled' : '' }}>+</button>
                                            </form>
                                            
                                            <span class="font-semibold text-gray-900 w-6 text-center">{{ $item->quantity }}</span>
                                            
                                            <form action="{{ route('cart.decrease', $item->cart_item_id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="w-8 h-8 bg-gray-200 hover:bg-gray-300 rounded text-gray-700 font-bold" {{ $item->quantity <= 1 ? 'disabled' : '' }}>−</button>
                                            </form>
                                        </div>

                                        <!-- Subtotal & Remove -->
                                        <div class="flex flex-col items-end justify-between">
                                            <p class="font-bold text-gray-900">₱{{ number_format($subtotal, 2) }}</p>
                                            
                                            <form action="{{ route('cart.remove', $item->cart_item_id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-700 font-semibold text-sm">
                                                    <i class="fas fa-trash mr-1"></i> Remove
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary Sidebar -->
                    <div>
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 sticky top-24">
                            <h2 class="text-lg font-bold mb-6 flex items-center gap-2">
                                <i class="fas fa-receipt text-red-600"></i>
                                <span>Order Summary</span>
                            </h2>

                            <div class="space-y-3 mb-6 pb-6 border-b border-gray-200">
                                <div class="flex justify-between text-gray-600">
                                    <span>Subtotal:</span>
                                    <span class="font-semibold">₱{{ number_format($total, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-gray-600">
                                    <span>Shipping:</span>
                                    <span class="font-semibold text-green-600">FREE</span>
                                </div>
                                <div class="flex justify-between text-lg font-bold">
                                    <span>Total:</span>
                                    <span class="text-red-600">₱{{ number_format($total, 2) }}</span>
                                </div>
                            </div>

                            <!-- Checkout Button -->
                            <a href="{{ route('checkout.index') }}" class="block w-full bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-bold py-3 rounded-lg text-center transition-all shadow-lg hover:shadow-xl mb-3">
                                <i class="fas fa-credit-card mr-2"></i> Proceed to Checkout
                            </a>

                            <!-- Continue Shopping -->
                            <a href="{{ route('home') }}" class="block text-center text-gray-600 hover:text-gray-900 font-medium">
                                ← Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <!-- Empty Cart -->
                <div class="bg-white rounded-2xl p-16 shadow-sm border border-gray-100 text-center">
                    <div class="mb-6">
                        <i class="fas fa-shopping-cart text-6xl text-gray-300"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Your cart is empty</h2>
                    <p class="text-gray-600 mb-6">You haven't added any items to your cart yet. Start shopping to add items!</p>
                    <a href="{{ route('home') }}" class="inline-block bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-bold py-3 px-8 rounded-lg transition-all shadow-lg hover:shadow-xl">
                        <i class="fas fa-store mr-2"></i> Start Shopping
                    </a>
                </div>
            @endif
        </div>
    </div>

    <script>
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                if(this.querySelector('button[type="submit"]').disabled) {
                    e.preventDefault();
                }
            });
        });
    </script>
@endsection