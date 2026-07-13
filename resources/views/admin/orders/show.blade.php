@extends('layouts.admin')

@section('title', 'Order Details')
@section('page-title', 'Order #' . $order->order_id)
@section('page-subtitle', 'View and manage order details.')

@section('styles')
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }

        body {
            background: linear-gradient(145deg, #f0f4f8 0%, #e9eef3 100%);
        }

        .card-cinematic {
            background: rgba(255,255,255,0.96);
            backdrop-filter: blur(2px);
            border: 1px solid rgba(255,255,255,0.5);
            box-shadow: 0 8px 20px -6px rgba(0,0,0,0.05);
        }

        .gradient-title {
            font-weight: 800 !important;
            background: linear-gradient(135deg, #000000, #dc2626);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .section-title {
            font-weight: 700;
            color: #1e293b;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 0.75rem;
            margin-bottom: 1rem;
        }

        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
        }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-paid { background: #dbeafe; color: #1e40af; }
        .status-shipped { background: #e0e7ff; color: #3730a3; }
        .status-completed { background: #d1fae5; color: #065f46; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }

        .select-compact {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            background-color: #ffffff;
            cursor: pointer;
            transition: all 0.2s;
        }
        .select-compact:focus {
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220,38,38,0.1);
            outline: none;
        }

        .btn-update {
            background: #dc2626;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }
        .btn-update:hover {
            background: #b91c1c;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #dc2626;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }
        .btn-back:hover {
            color: #b91c1c;
        }
    </style>
@endsection

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8 relative z-10">

        <!-- Back Button -->
        <a href="{{ route('admin.orders.index') }}" class="btn-back">
            <i class="fas fa-arrow-left"></i> Back to Orders
        </a>

        <!-- Success Alert -->
        @if(session('success'))
            <div class="bg-emerald-50/90 border-l-4 border-emerald-400 text-emerald-700 p-4 rounded-xl shadow-md flex items-center justify-between">
                <div class="font-medium">{{ session('success') }}</div>
                <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">✕</button>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Order Status Card -->
                <div class="card-cinematic rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-2xl font-bold gradient-title">Order #{{ $order->order_id }}</h2>
                        <span class="status-badge status-{{ $order->status }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                    <p class="text-gray-600 text-sm">
                        Placed on {{ $order->created_at->format('F d, Y \a\t H:i A') }}
                    </p>

                    <div class="mt-6">

    @if($order->status === 'pending')

        <form action="{{ route('admin.orders.update-status', $order->order_id) }}"
              method="POST"
              onsubmit="return confirm('Approve payment for this order?');">

            @csrf
            @method('PUT')

            <input type="hidden" name="status" value="paid">

            <button class="btn-update w-full">
                <i class="fas fa-check-circle mr-2"></i>
                Approve Payment
            </button>

        </form>

    @elseif($order->status === 'paid')

        <form action="{{ route('admin.orders.update-status', $order->order_id) }}"
              method="POST"
              onsubmit="return confirm('Mark this order as shipped?');">

            @csrf
            @method('PUT')

            <input type="hidden" name="status" value="shipped">

            <button class="btn-update w-full bg-blue-600 hover:bg-blue-700">
                <i class="fas fa-truck mr-2"></i>
                Ship Order
            </button>

        </form>

    @elseif($order->status === 'shipped')

        <form action="{{ route('admin.orders.update-status', $order->order_id) }}"
              method="POST"
              onsubmit="return confirm('Mark this order as delivered?');">

            @csrf
            @method('PUT')

            <input type="hidden" name="status" value="completed">

            <button class="btn-update w-full bg-green-600 hover:bg-green-700">
                <i class="fas fa-box-open mr-2"></i>
                Mark as Delivered
            </button>

        </form>

    @elseif($order->status === 'completed')

        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-center">
            <i class="fas fa-check-circle text-green-600 text-2xl mb-2"></i>
            <p class="font-semibold text-green-700">
                This order has been completed.
            </p>
        </div>

    @elseif($order->status === 'cancelled')

        <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-center">
            <i class="fas fa-times-circle text-red-600 text-2xl mb-2"></i>
            <p class="font-semibold text-red-700">
                This order has been cancelled.
            </p>
        </div>

    @endif

</div>
                </div>

                <!-- Order Items -->
                <div class="card-cinematic rounded-2xl p-6">
                    <h3 class="section-title">Order Items</h3>
                    <div class="space-y-4">
                        @foreach($order->items as $item)
                            <div class="border border-gray-200 rounded-lg p-4 flex gap-4">
                                <!-- Item Info -->
                                <div class="flex-1">
                                    <div>
                                        <h4 class="font-semibold text-gray-900">
                                            {{ $item->variant->product->product_name }}
                                        </h4>
                                        <p class="text-sm text-gray-600">
                                            <strong>Variant:</strong> {{ $item->variant->size }} / {{ $item->variant->color }}
                                        </p>
                                    </div>
                                </div>
                                <!-- Quantity & Price -->
                                <div class="text-right">
                                    <p class="font-medium text-gray-900">
                                        Qty: {{ $item->quantity }}
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        ₱{{ number_format($item->price, 2) }} each
                                    </p>
                                    <p class="font-bold text-gray-900 mt-1">
                                        ₱{{ number_format($item->quantity * $item->price, 2) }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Shipping Address -->
                <div class="card-cinematic rounded-2xl p-6">
                    <h3 class="section-title">Shipping Address</h3>
                    <div class="text-gray-700 space-y-1">
                        <p class="font-semibold">{{ $order->full_name }}</p>
                        <p>{{ $order->phone_number }}</p>
                        <p>{{ $order->street }}, {{ $order->barangay }}</p>
                        <p>{{ $order->city }}, {{ $order->province }} {{ $order->postal_code }}</p>
                    </div>
                </div>

                <!-- Payment Info -->
                @if($order->payment)
                    <div class="card-cinematic rounded-2xl p-6">
                        <h3 class="section-title">Payment Information</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Payment Method:</span>
                                <span class="font-medium">{{ ucfirst($order->payment->method) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Status:</span>
                                <span class="font-medium">{{ ucfirst($order->payment->status) }}</span>
                            </div>
                            @if($order->payment->payment_date)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Payment Date:</span>
                                    <span class="font-medium">{{ $order->payment->payment_date->format('F d, Y H:i A') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Customer Info -->
                <div class="card-cinematic rounded-2xl p-6">
                    <h3 class="section-title">Customer Information</h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">Name</p>
                            <p class="font-medium text-gray-900">{{ $order->user->first_name }} {{ $order->user->last_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">Email</p>
                            <p class="font-medium text-gray-900">{{ $order->user->email }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">User Since</p>
                            <p class="font-medium text-gray-900">{{ $order->user->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="card-cinematic rounded-2xl p-6">
                    <h3 class="section-title">Order Summary</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal:</span>
                            <span class="font-medium">₱{{ number_format($order->total_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Shipping:</span>
                            <span class="font-medium">FREE</span>
                        </div>
                        <div class="flex justify-between py-2 border-t border-b border-gray-200">
                            <span class="font-semibold text-gray-900">Total:</span>
                            <span class="font-bold text-lg text-red-600">₱{{ number_format($order->total_amount, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="card-cinematic rounded-2xl p-6">
                    <h3 class="section-title">Timeline</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex gap-3">
                            <i class="fas fa-calendar text-red-500 mt-1"></i>
                            <div>
                                <p class="font-medium text-gray-900">Order Created</p>
                                <p class="text-gray-600 text-xs">{{ $order->created_at->format('M d, Y H:i A') }}</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <i class="fas fa-clock text-gray-400 mt-1"></i>
                            <div>
                                <p class="font-medium text-gray-900">Last Updated</p>
                                <p class="text-gray-600 text-xs">{{ $order->updated_at->format('M d, Y H:i A') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
