@extends('layouts.app')

@section('title', 'Checkout - ACHILLES')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50 py-8 sm:py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Back Button -->
            <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 text-red-600 hover:text-red-700 font-semibold mb-8">
                <i class="fas fa-arrow-left"></i> Back to Shop
            </a>

            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl sm:text-4xl font-black bg-gradient-to-r from-black to-red-600 bg-clip-text text-transparent">Secure Checkout</h1>
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

            <form id="checkoutForm" action="{{ route('checkout.place-order') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                @csrf
                <!-- Main Form Column -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Delivery Address Section -->
                    <div class="bg-white rounded-2xl p-5 sm:p-8 shadow-sm border border-gray-100">
                        <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
                            <i class="fas fa-map-marker-alt text-red-600"></i>
                            <span class="bg-gradient-to-r from-black to-red-600 bg-clip-text text-transparent">Delivery Address</span>
                        </h2>

                        @php
                            $selectedAddress = old('address_id', session('selected_address_id', $addresses->firstWhere('is_default', true)?->address_id ?? $addresses->first()?->address_id));
                        @endphp
                        <div id="checkoutAddressWarning" role="alert" tabindex="-1"
                             class="checkout-notice checkout-warning" @if($addresses->isNotEmpty() && !$errors->has('address_id')) hidden @endif>
                            {{ $errors->first('address_id') ?: 'Please add a delivery address before completing your order.' }}
                        </div>
                        @if(session('success'))
                            <p class="checkout-notice checkout-success" role="status">{{ session('success') }}</p>
                        @endif
                        <div class="space-y-3">
                            @foreach($addresses as $address)
                                <label class="checkout-address">
                                    <input type="radio" name="address_id" value="{{ $address->address_id }}"
                                           class="saved-address" @checked((string) $selectedAddress === (string) $address->address_id)>
                                    <span>
                                        <strong>{{ $address->full_name }}</strong>
                                        <span class="checkout-address-detail">{{ $address->phone_number }}</span>
                                        <span class="checkout-address-detail">{{ $address->street }}, {{ $address->barangay }}, {{ $address->city }}, {{ $address->province }}, {{ $address->postal_code }}</span>
                                        @if($address->is_default)<span class="text-sm text-red-600">Default address</span>@endif
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <button type="button" id="addCheckoutAddress" class="checkout-add-address" aria-haspopup="dialog" aria-controls="checkoutAddressModal">
                            <i class="fas fa-plus-circle" aria-hidden="true"></i> Add New Address
                        </button>
                        <noscript><a href="{{ route('addresses.create') }}">Add a delivery address</a></noscript>
                    </div>
                    <div class="bg-white rounded-2xl p-5 sm:p-8 shadow-sm border border-gray-100">
    <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
        <i class="fas fa-shield-alt text-red-600"></i>
        <span class="bg-gradient-to-r from-black to-red-600 bg-clip-text text-transparent">Secure Payment</span>
    </h2>
    <p class="text-gray-600 text-sm mb-4">
        You'll be redirected to PayMongo's secure checkout to complete payment via
        Card, GCash, GrabPay, or Maya.
    </p>
</div>

                </div>
                <!-- Sidebar: Order Summary -->
                <div>
                    <div class="bg-white rounded-2xl p-5 sm:p-8 shadow-sm border border-gray-100 lg:sticky lg:top-24">
                        <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
                            <i class="fas fa-shopping-bag text-red-600"></i>
                            <span class="bg-gradient-to-r from-black to-red-600 bg-clip-text text-transparent">Order Summary</span>
                        </h2>

                        <!-- Items List -->
                        <div class="mb-6 pb-6 border-b border-gray-200 space-y-3 max-h-64 overflow-y-auto">
                            @forelse($cart->items as $item)
                                <div class="flex justify-between gap-3 text-sm">
                                    @php $imageUrl = $item->variant->product->images->first()?->image_url; @endphp
                                    <div class="w-12 h-12 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                        @if($imageUrl)
                                            <img src="{{ $imageUrl }}" alt="{{ $item->variant->product->product_name }}"
                                                 class="w-full h-full object-cover" loading="lazy" decoding="async"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        @endif
                                        <div class="w-full h-full flex items-center justify-center" style="{{ $imageUrl ? 'display: none;' : '' }}" aria-hidden="true">
                                            <i class="fas fa-shoe-prints text-gray-400"></i>
                                        </div>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-semibold text-gray-900 truncate">{{ substr($item->variant->product->product_name, 0, 20) }}...</p>
                                        <p class="text-xs text-gray-500">{{ $item->variant->size }} / {{ $item->variant->color }} × {{ $item->quantity }}</p>
                                    </div>
                                    <p class="font-semibold text-gray-900 flex-shrink-0">₱{{ number_format($item->price * $item->quantity, 2) }}</p>
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
    <dialog id="checkoutAddressModal" class="checkout-address-modal" aria-labelledby="checkoutAddressTitle"
            data-reopen="{{ old('return_to') === 'checkout' && ($errors->any() || session('error')) ? 'true' : 'false' }}">
        <div class="checkout-modal-header">
            <h2 id="checkoutAddressTitle">Add delivery address</h2>
            <button type="button" data-close-address aria-label="Close address form" class="checkout-modal-close">&times;</button>
        </div>
        <p class="text-gray-600 mb-6">Save a new address and use it for this order. Fields marked * are required.</p>
        <div id="checkoutAddressFormWarning" class="checkout-notice checkout-warning" role="alert" @if(!$errors->any() && !session('error')) hidden @endif>
            @if($errors->any())
                Please check the address details below.
            @elseif(session('error'))
                {{ session('error') }}
            @endif
        </div>
        <form id="checkoutAddressForm" action="{{ route('addresses.store') }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="return_to" value="checkout">
            @include('addresses.fields')
            <div class="checkout-modal-actions">
                <button type="submit" id="saveCheckoutAddress" class="checkout-save-address">Save and use this address</button>
                <button type="button" data-close-address class="checkout-cancel-address">Cancel</button>
            </div>
        </form>
    </dialog>
@endsection

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<link rel="stylesheet" href="{{ asset('css/checkout.css') }}?v={{ filemtime(public_path('css/checkout.css')) }}">
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="{{ asset('js/address-selector.js') }}?v={{ filemtime(public_path('js/address-selector.js')) }}"></script>
<script src="{{ asset('js/checkout-address.js') }}?v={{ filemtime(public_path('js/checkout-address.js')) }}"></script>
@endpush