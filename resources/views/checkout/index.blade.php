@extends('layouts.app')

@section('title', 'Checkout - ACHILLES')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50 py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Back Button -->
            <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 text-red-600 hover:text-red-700 font-semibold mb-8">
                <i class="fas fa-arrow-left"></i> Back to Shop
            </a>

            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-4xl font-black bg-gradient-to-r from-black to-red-600 bg-clip-text text-transparent">Secure Checkout</h1>
                <p class="text-gray-600 mt-2">Complete your order with your preferred payment method</p>
            </div>

            <!-- Error Messages -->
            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
                    <p class="text-red-700 font-medium">{{ session('error') }}</p>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
                    <p class="text-red-700 font-semibold mb-2">Please fix the following errors:</p>
                    <ul class="list-disc list-inside text-red-600 text-sm space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('checkout.place-order') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                @csrf
                <!-- Main Form Column -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Delivery Address Section -->
                    <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100">
                        <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
                            <i class="fas fa-map-marker-alt text-red-600"></i>
                            <span class="bg-gradient-to-r from-black to-red-600 bg-clip-text text-transparent">Delivery Address</span>
                        </h2>

                        <!-- Saved Addresses -->
                        @if($addresses->count() > 0)
                            <div class="mb-8">
                                <p class="text-sm font-bold text-gray-600 uppercase tracking-wide mb-4">Saved Addresses</p>
                                <div class="space-y-3">
                                    @foreach($addresses as $address)
                                        <label class="block cursor-pointer">
                                            <div class="border-2 border-gray-200 rounded-xl p-4 hover:border-red-500 hover:bg-red-50 transition-all" id="addr-{{ $address->address_id }}">
                                                <div class="flex items-start gap-3">
                                                    
    <input type="radio"
name="address_id"
    value="{{ $address->address_id }}"
    {{ $address->is_default ? 'checked' : '' }} class="saved-address"

>
                                                    <div class="flex-1">
                                                        <p class="font-semibold text-gray-900">
    {{ $address->full_name }}
</p>

<p class="text-sm text-gray-600 mt-1">
    📞 {{ $address->phone_number }}
</p>

<p class="text-sm text-gray-500 mt-2">
    {{ $address->street }},
    {{ $address->barangay }},
    {{ $address->city }},
    {{ $address->province }},
    {{ $address->postal_code }}
</p>
                                                        @if($address->is_default)
                                                            <span class="inline-block mt-2 bg-blue-100 text-blue-700 px-2 py-0.5 rounded text-xs font-semibold">Default</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                    @endforeach

                                    <a href="{{ route('addresses.create') }}"
   class="block border-2 border-dashed border-red-400 rounded-xl p-5 hover:bg-red-50 hover:border-red-600 transition">

    <div class="flex items-center justify-center gap-3">

        <i class="fas fa-plus-circle text-red-600 text-xl"></i>

        <span class="font-semibold text-red-600">
            Add New Address
        </span>

    </div>

</a>
                                </div>
                                <hr class="my-8">
                            </div>
                        @endif

                    <!-- Payment Method Section -->
                    <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100">
                        <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
                            <i class="fas fa-credit-card text-red-600"></i>
                            <span class="bg-gradient-to-r from-black to-red-600 bg-clip-text text-transparent">Payment Method</span>
                        </h2>

                        @error('payment_method')<span class="text-red-600 text-sm block mb-4">{{ $message }}</span>@enderror

                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <label class="cursor-pointer">
                                <input type="radio" name="payment_method" value="credit_card" {{ old('payment_method') === 'credit_card' ? 'checked' : '' }} class="hidden peer">
                                <div class="border-2 border-gray-300 rounded-xl p-4 text-center peer-checked:border-red-600 peer-checked:bg-red-50 transition-all hover:border-red-400">
                                    <i class="fas fa-credit-card text-red-600 text-2xl mb-2 block"></i>
                                    <span class="font-semibold text-sm">Credit Card</span>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" name="payment_method" value="debit_card" {{ old('payment_method') === 'debit_card' ? 'checked' : '' }} class="hidden peer">
                                <div class="border-2 border-gray-300 rounded-xl p-4 text-center peer-checked:border-red-600 peer-checked:bg-red-50 transition-all hover:border-red-400">
                                    <i class="fas fa-id-card text-green-600 text-2xl mb-2 block"></i>
                                    <span class="font-semibold text-sm">Debit Card</span>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" name="payment_method" value="gcash" {{ old('payment_method') === 'gcash' ? 'checked' : '' }} class="hidden peer">
                                <div class="border-2 border-gray-300 rounded-xl p-4 text-center peer-checked:border-red-600 peer-checked:bg-red-50 transition-all hover:border-red-400">
                                    <i class="fas fa-mobile-alt text-blue-600 text-2xl mb-2 block"></i>
                                    <span class="font-semibold text-sm">GCash</span>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" name="payment_method" value="paypal" {{ old('payment_method') === 'paypal' ? 'checked' : '' }} class="hidden peer">
                                <div class="border-2 border-gray-300 rounded-xl p-4 text-center peer-checked:border-red-600 peer-checked:bg-red-50 transition-all hover:border-red-400">
                                    <i class="fab fa-paypal text-blue-800 text-2xl mb-2 block"></i>
                                    <span class="font-semibold text-sm">PayPal</span>
                                </div>
                            </label>

                            <label class="cursor-pointer col-span-2 md:col-span-1">
                                <input type="radio" name="payment_method" value="cash_on_delivery" {{ old('payment_method') === 'cash_on_delivery' ? 'checked' : '' }} class="hidden peer">
                                <div class="border-2 border-gray-300 rounded-xl p-4 text-center peer-checked:border-red-600 peer-checked:bg-red-50 transition-all hover:border-red-400">
                                    <i class="fas fa-money-bill-wave text-amber-600 text-2xl mb-2 block"></i>
                                    <span class="font-semibold text-sm">Cash on Delivery</span>
                                </div>
                            </label>
                        </div>

                        <div class="mt-6 p-4 bg-blue-50 rounded-lg border-l-4 border-blue-500">
                            <p class="text-blue-700 text-sm"><strong>Demo:</strong> All payments are securely processed and auto-approved for testing.</p>
                        </div>
                    </div>
                </div>

                <!-- Sidebar: Order Summary -->
                <div>
                    <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100 sticky top-24">
                        <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
                            <i class="fas fa-shopping-bag text-red-600"></i>
                            <span class="bg-gradient-to-r from-black to-red-600 bg-clip-text text-transparent">Order Summary</span>
                        </h2>

                        <!-- Items List -->
                        <div class="mb-6 pb-6 border-b border-gray-200 space-y-3 max-h-64 overflow-y-auto">
                            @forelse($cart->items as $item)
                                <div class="flex justify-between text-sm">
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ substr($item->variant->product->product_name, 0, 20) }}...</p>
                                        <p class="text-xs text-gray-500">{{ $item->variant->size }} / {{ $item->variant->color }} × {{ $item->quantity }}</p>
                                    </div>
                                    <p class="font-semibold text-gray-900">₱{{ number_format($item->price * $item->quantity, 2) }}</p>
                                </div>
                            @empty
                                <p class="text-gray-500 text-center py-4">No items in cart</p>
                            @endforelse
                        </div>

                        <!-- Totals -->
                        <div class="space-y-2 mb-6">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal:</span>
                                <span class="font-semibold">₱{{ number_format($cart->items->sum(function($item) { return $item->price * $item->quantity; }), 2) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Shipping:</span>
                                <span class="font-semibold text-green-600">FREE</span>
                            </div>
                            <div class="flex justify-between text-lg font-bold pt-2 border-t-2 border-gray-200">
                                <span>Total:</span>
                                <span class="text-red-600">₱{{ number_format($cart->items->sum(function($item) { return $item->price * $item->quantity; }), 2) }}</span>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="w-full bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-bold py-3 rounded-lg transition-all duration-200 flex items-center justify-center gap-2 shadow-lg hover:shadow-xl">
                            <i class="fas fa-lock"></i> Complete Order
                        </button>

                        <!-- Continue Shopping -->
                        <a href="{{ route('products.index') }}" class="block text-center mt-3 text-gray-600 hover:text-gray-900 font-medium">
                            ← Continue Shopping
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection