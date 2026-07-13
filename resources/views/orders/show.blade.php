@extends('layouts.app')

@section('title', 'Order #' . $order->order_id . ' - ACHILLES')

@section('content')

@if(session('success'))
<div class="mb-6 bg-green-50 border border-green-200 text-green-700 p-4 rounded-xl">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="mb-6 bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl">
    {{ session('error') }}
</div>
@endif
    <div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50 py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Back Button -->
            <a href="{{ route('orders.index') }}" class="inline-flex items-center gap-2 text-red-600 hover:text-red-700 font-semibold mb-8">
                <i class="fas fa-arrow-left"></i> Back to My Orders
            </a>

            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-4xl font-black bg-gradient-to-r from-black to-red-600 bg-clip-text text-transparent">Order #{{ $order->order_id }}</h1>
                        <p class="text-gray-600 mt-2">Placed on {{ $order->created_at->format('F d, Y \a\t H:i A') }}</p>
                    </div>
                    <div class="text-right">
                        @php
                            $statusColors = [
                                'pending' => 'bg-yellow-100 text-yellow-700',
                                'paid' => 'bg-blue-100 text-blue-700',
                                'shipped' => 'bg-purple-100 text-purple-700',
                                'completed' => 'bg-green-100 text-green-700',
                                'cancelled' => 'bg-red-100 text-red-700',
                            ];
                        @endphp
                        <span class="inline-block px-4 py-2 rounded-full text-sm font-bold {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-700' }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Order Items -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                        <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
                            <i class="fas fa-box text-red-600"></i>
                            <span>Order Items ({{ $order->items->count() }})</span>
                        </h2>

                        <div class="space-y-4">
                            @foreach($order->items as $item)
                                <div class="border rounded-2xl p-5 flex gap-5 hover:shadow-md transition">

    <div class="w-24 h-24 bg-gray-100 rounded-xl overflow-hidden flex-shrink-0">
        @if($item->variant->product->images->first())
            <img
                src="{{ asset('storage/' . $item->variant->product->images->first()->image_path) }}"
                class="w-full h-full object-cover">
        @else
            <div class="w-full h-full flex items-center justify-center">
                <i class="fas fa-shoe-prints text-3xl text-gray-400"></i>
            </div>
        @endif
    </div>

    <div class="flex-1">

        <h3 class="font-bold text-lg">
            {{ $item->variant->product->product_name }}
        </h3>

        <div class="mt-2 flex gap-2 flex-wrap">

            <span class="px-3 py-1 bg-gray-100 rounded-full text-sm">
                Size {{ $item->variant->size }}
            </span>

            <span class="px-3 py-1 bg-gray-100 rounded-full text-sm">
                {{ $item->variant->color }}
            </span>

        </div>

        <p class="mt-3 text-red-600 font-bold">
            ₱{{ number_format($item->price,2) }}
            <span class="text-gray-500 text-sm font-normal">each</span>
        </p>

    </div>

    <div class="text-right flex flex-col justify-between">

        <span class="bg-red-100 text-red-600 px-3 py-1 rounded-full font-bold">
            x{{ $item->quantity }}
        </span>

        <p class="text-xl font-black">
            ₱{{ number_format($item->price * $item->quantity,2) }}
        </p>

    </div>

</div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Shipping Address -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                        <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                            <i class="fas fa-map-marker-alt text-red-600"></i>
                            <span>Shipping Address</span>
                        </h2>
                        <div class="text-gray-700 space-y-2">
                            <p class="font-semibold text-lg">{{ $order->full_name }}</p>
                            <p>{{ $order->street }}, {{ $order->barangay }}</p>
                            <p>{{ $order->city }}, {{ $order->province }} {{ $order->postal_code }}</p>
                            <p class="font-semibold">📞 {{ $order->phone_number }}</p>
                        </div>
                    </div>

                    <!-- Payment Information -->
                    @if($order->payment)
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                            <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                                <i class="fas fa-credit-card text-red-600"></i>
                                <span>Payment Information</span>
                            </h2>
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Payment Method:</span>
                                    <span class="font-semibold">{{ ucfirst(str_replace('_', ' ', $order->payment->method)) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Status:</span>
                                    @php
                                        $paymentStatusColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-700',
                                            'completed' => 'bg-green-100 text-green-700',
                                            'failed' => 'bg-red-100 text-red-700',
                                        ];
                                    @endphp
                                    <span class="inline-block px-2 py-1 rounded text-xs font-bold {{ $paymentStatusColors[$order->payment->status] ?? 'bg-gray-100 text-gray-700' }}">
                                        {{ ucfirst($order->payment->status) }}
                                    </span>
                                </div>
                                @if($order->payment->payment_date)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Payment Date:</span>
                                        <span class="font-semibold">{{ $order->payment->payment_date->format('F d, Y H:i A') }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Order Total -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                        <h2 class="text-xl font-bold mb-4">Order Total</h2>
                        <div class="space-y-2 pb-4 border-b border-gray-200">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal:</span>
                                <span class="font-semibold">₱{{ number_format($order->total_amount, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Shipping:</span>
                                <span class="font-semibold text-green-600">FREE</span>
                            </div>
                        </div>
                        <div class="flex justify-between text-lg font-bold pt-4">
                            <span>Total:</span>
                            <span class="text-red-600">₱{{ number_format($order->total_amount, 2) }}</span>
                        </div>
                    </div>

                   <!-- Order Progress -->
@if($order->status === 'cancelled')

<div class="bg-white rounded-2xl p-6 shadow-sm border border-red-200">

    <h2 class="text-xl font-bold text-red-600 mb-3 flex items-center gap-2">
        <i class="fas fa-times-circle"></i>
        <span>Order Cancelled</span>
    </h2>

    <p class="text-gray-600">
        This order has been cancelled and will no longer be processed.
    </p>

</div>

@else

<div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">

    <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
        <i class="fas fa-truck text-red-600"></i>
        <span>Order Progress</span>
    </h2>

    @php
        $steps = [
            'ordered',
            'paid',
            'shipped',
            'completed'
        ];

        $current = match($order->status) {
            'pending' => 0,
            'paid' => 1,
            'shipped' => 2,
            'completed' => 3,
            default => 0,
        };
    @endphp

    <div class="flex items-center justify-between">

        @foreach($steps as $index => $step)

            <div class="flex-1 flex flex-col items-center relative">

                @if(!$loop->last)
                    <div class="absolute top-4 left-1/2 w-full h-1 {{ $index < $current ? 'bg-green-500' : 'bg-gray-300' }}"></div>
                @endif

                <div class="relative z-10 w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-bold {{ $index <= $current ? 'bg-green-500' : 'bg-gray-300' }}">
                    @if($index <= $current)
                        <i class="fas fa-check text-xs"></i>
                    @endif
                </div>

                <span class="mt-3 text-xs font-semibold uppercase tracking-wide">
                    {{ ucfirst($step) }}
                </span>

            </div>

        @endforeach

    </div>

</div>

@endif

<!-- Order Timeline -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                        <h2 class="text-xl font-bold mb-4">Timeline</h2>
                        <div class="space-y-3 text-sm">
                            <div class="flex gap-3">
                                <div class="flex-shrink-0 w-3 h-3 bg-red-600 rounded-full mt-1.5"></div>
                                <div>
                                    <p class="font-semibold text-gray-900">Order Placed</p>
                                    <p class="text-gray-500">{{ $order->created_at->format('M d, Y H:i A') }}</p>
                                </div>
                            </div>
                            @if($order->status === 'cancelled')

<div class="flex gap-3">
    <div class="flex-shrink-0 w-3 h-3 bg-red-600 rounded-full mt-1.5"></div>
    <div>
        <p class="font-semibold text-gray-900">Order Cancelled</p>
        <p class="text-gray-500">{{ $order->updated_at->format('M d, Y H:i A') }}</p>
    </div>
</div>

@elseif($order->payment && $order->payment->payment_date)

<div class="flex gap-3">
    <div class="flex-shrink-0 w-3 h-3 bg-green-600 rounded-full mt-1.5"></div>
    <div>
        <p class="font-semibold text-gray-900">Payment Received</p>
        <p class="text-gray-500">{{ $order->payment->payment_date->format('M d, Y H:i A') }}</p>
    </div>
</div>

@endif

@if($order->status === 'shipped')

<div class="flex gap-3">
    <div class="flex-shrink-0 w-3 h-3 bg-blue-600 rounded-full mt-1.5"></div>
    <div>
        <p class="font-semibold text-gray-900">Shipped</p>
        <p class="text-gray-500">{{ $order->updated_at->format('M d, Y H:i A') }}</p>
    </div>
</div>

@elseif($order->status === 'completed')

<div class="flex gap-3">
    <div class="flex-shrink-0 w-3 h-3 bg-purple-600 rounded-full mt-1.5"></div>
    <div>
        <p class="font-semibold text-gray-900">Delivered</p>
        <p class="text-gray-500">{{ $order->updated_at->format('M d, Y H:i A') }}</p>
    </div>
</div>

@endif
</div>
    </div>
                    <!-- Action Buttons -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">

    @if($order->status === 'pending')

        <form action="{{ route('orders.cancel', $order->order_id) }}"
              method="POST"
              onsubmit="return confirm('Are you sure you want to cancel this order?');">

            @csrf
            @method('PUT')

            <button
                class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-lg mb-4">

                Cancel Order

            </button>

        </form>

    @endif

    <a href="{{ route('orders.index') }}"
       class="block w-full bg-gray-900 hover:bg-black text-white font-bold py-3 rounded-lg text-center mb-3">

        ← Back to Orders

    </a>

    <a href="{{ route('home') }}"
       class="block w-full border-2 border-gray-300 hover:border-red-600 text-center py-3 rounded-lg">

        Continue Shopping

    </a>

</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
