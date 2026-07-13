@extends('layouts.app')

@section('title', 'My Orders - ACHILLES')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50 py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="mb-8">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-red-600 hover:text-red-700 font-semibold mb-4">
                    <i class="fas fa-arrow-left"></i> Back to Shop
                </a>
                <h1 class="text-4xl font-black bg-gradient-to-r from-black to-red-600 bg-clip-text text-transparent">My Orders</h1>
                <p class="text-gray-600 mt-2">Track and manage all your purchases</p>
            </div>
            
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">

    <div>
        <label class="text-sm font-semibold text-gray-600">
            Filter
        </label>

        <select id="statusFilter"
            class="mt-1 border rounded-lg px-4 py-2 focus:ring-red-500 focus:border-red-500">

            <option value="all">All Orders</option>
            <option value="pending">Pending</option>
            <option value="paid">Paid</option>
            <option value="shipped">Shipped</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>

        </select>
    </div>

    <div class="w-full md:w-72">
        <label class="text-sm font-semibold text-gray-600">
            Search
        </label>

        <input
            type="text"
            id="searchOrder"
            placeholder="Search Order ID..."
            class="mt-1 w-full border rounded-lg px-4 py-2 focus:ring-red-500 focus:border-red-500">

    </div>

</div>

            @if($orders->count() > 0)
                <div class="grid grid-cols-1 gap-6">
                    @foreach($orders as $order)

<div
class="order-card"
data-status="{{ strtolower($order->status) }}"
data-id="{{ $order->order_id }}">
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-center">
                                <!-- Order Number -->
                                <div>
                                    <p class="text-sm font-semibold text-gray-500 uppercase">Order ID</p>
                                    <p class="text-2xl font-bold text-gray-900">#{{ $order->order_id }}</p>
                                
                                </div>
                                <!-- Order Date -->
                                <div>
                                    
                                    <p class="text-sm font-semibold text-gray-500 uppercase">Date</p>
                                    <p class="font-semibold text-gray-900">{{ $order->created_at->format('M d, Y') }}</p>
                                    <p class="text-xs text-gray-500">{{ $order->created_at->format('H:i A') }}</p>
                                </div>

                                <!-- Status -->
                                <div>
                                    <p class="text-sm font-semibold text-gray-500 uppercase">Status</p>
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-700',
                                            'paid' => 'bg-blue-100 text-blue-700',
                                            'shipped' => 'bg-purple-100 text-purple-700',
                                            'completed' => 'bg-green-100 text-green-700',
                                            'cancelled' => 'bg-red-100 text-red-700',
                                        ];
                                    @endphp
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-700' }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>

                                <!-- Items Count -->
                                <div>
                                    <p class="text-sm font-semibold text-gray-500 uppercase">Items</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ $order->items->count() }}</p>
                                </div>

                                <!-- Amount & Action -->
                                <div>
                                    <p class="text-sm font-semibold text-gray-500 uppercase">Total</p>
                                    <p class="text-2xl font-bold text-red-600">₱{{ number_format($order->total_amount, 2) }}</p>
                                    <a href="{{ route('orders.show', $order->order_id) }}" class="inline-block mt-2 text-red-600 hover:text-red-700 font-semibold text-sm">
                                        View Details →
                                    </a>
                                </div>
                            </div>
                        </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($orders->hasPages())
                    <div class="mt-8">
                        {{ $orders->links() }}
                    </div>
                @endif
            @else
                <!-- Empty State -->
                <div class="bg-white rounded-2xl p-16 shadow-sm border border-gray-100 text-center">
                    <div class="mb-6">
                        <i class="fas fa-box-open text-6xl text-gray-300"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">No orders yet</h2>
                    <p class="text-gray-600 mb-6">You haven't made any purchases yet. Start shopping to create your first order!</p>
                    <a href="{{ route('home') }}" class="inline-block bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-bold py-3 px-8 rounded-lg transition-all shadow-lg hover:shadow-xl">
                        <i class="fas fa-store mr-2"></i> Start Shopping
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>

document.addEventListener('DOMContentLoaded', function () {

    const filter = document.getElementById('statusFilter');
    const search = document.getElementById('searchOrder');
    const cards = document.querySelectorAll('.order-card');

    function filterOrders() {

        const status = filter.value.toLowerCase();
        const keyword = search.value.toLowerCase();

        cards.forEach(card => {

            const cardStatus = card.dataset.status;
            const cardId = card.dataset.id;

            const statusMatch =
                status === 'all' || cardStatus === status;

            const searchMatch =
                cardId.includes(keyword);

            if (statusMatch && searchMatch) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }

        });

    }

    filter.addEventListener('change', filterOrders);
    search.addEventListener('input', filterOrders);

});

</script>
@endpush