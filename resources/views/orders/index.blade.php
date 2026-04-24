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

            @if($orders->count() > 0)
                <div class="grid grid-cols-1 gap-6">
                    @foreach($orders as $order)
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
                </div>
                <div class="flex flex-wrap items-center gap-4">
                    <div class="relative" style="z-index: 9999;">
                        <button id="filterButton" class="filter-btn-3d">
                            <span id="filterButtonText">All Orders</span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div id="filterDropdown" class="filter-dropdown hidden" style="z-index: 10000;">
                            <div class="py-2">
                                <button class="filter-option w-full text-left px-5 py-2.5 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors" data-filter="all">All Orders</button>
                                <button class="filter-option w-full text-left px-5 py-2.5 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors" data-filter="pending">Pending</button>
                                <button class="filter-option w-full text-left px-5 py-2.5 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors" data-filter="completed">Completed</button>
                                <button class="filter-option w-full text-left px-5 py-2.5 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors" data-filter="processing">Processing</button>
                                <button class="filter-option w-full text-left px-5 py-2.5 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors" data-filter="cancelled">Cancelled</button>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 bg-white/50 backdrop-blur-sm px-4 py-2 rounded-full border border-gray-100 shadow-sm">
                        <span class="text-xs font-medium text-gray-500 tracking-wide">SORT BY</span>
                        <select id="sortSelect" class="sort-select-3d">
                            <option value="latest">Latest</option>
                            <option value="oldest">Oldest</option>
                            <option value="highest">Highest Amount</option>
                            <option value="lowest">Lowest Amount</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- PREMIUM STATS BAR -->
        @php
            $totalOrders = isset($orders) ? count($orders) : 0;
            $pendingOrders = isset($orders) ? $orders->filter(function($order) { return strtolower($order->status) === 'pending'; })->count() : 0;
            $completedOrders = isset($orders) ? $orders->filter(function($order) { return strtolower($order->status) === 'completed'; })->count() : 0;
            $totalSpent = isset($orders) ? $orders->sum('total_amount') : 0;
        @endphp

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-12">
            <div class="bg-white/70 backdrop-blur-sm border border-gray-100 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all hover:border-red-200/50 group">
                <div class="flex items-start justify-between">
                    <div><p class="text-gray-400 text-xs tracking-wider uppercase">Total Orders</p><p class="text-3xl font-bold text-gray-900 mt-1" id="statTotalOrders">{{ $totalOrders }}</p></div>
                    <div class="w-8 h-8 rounded-full bg-red-50 flex items-center justify-center"><svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg></div>
                </div>
                <div class="h-0.5 w-8 bg-red-400/40 rounded-full mt-3 group-hover:w-full transition-all duration-300"></div>
            </div>
            <div class="bg-white/70 backdrop-blur-sm border border-gray-100 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all hover:border-amber-200/50">
                <div class="flex items-start justify-between"><div><p class="text-gray-400 text-xs tracking-wider uppercase">Pending</p><p class="text-3xl font-bold text-gray-900 mt-1" id="statPendingOrders">{{ $pendingOrders }}</p></div><div class="w-8 h-8 rounded-full bg-amber-50 flex items-center justify-center"><svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div></div>
                <div class="h-0.5 w-6 bg-amber-300/60 rounded-full mt-3"></div>
            </div>
            <div class="bg-white/70 backdrop-blur-sm border border-gray-100 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all hover:border-emerald-200/50">
                <div class="flex items-start justify-between"><div><p class="text-gray-400 text-xs tracking-wider uppercase">Completed</p><p class="text-3xl font-bold text-gray-900 mt-1" id="statCompletedOrders">{{ $completedOrders }}</p></div><div class="w-8 h-8 rounded-full bg-emerald-50 flex items-center justify-center"><svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div></div>
                <div class="h-0.5 w-8 bg-emerald-400/50 rounded-full mt-3"></div>
            </div>
            <div class="bg-white/70 backdrop-blur-sm border border-gray-100 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all hover:border-red-200/50">
                <div class="flex items-start justify-between"><div><p class="text-gray-400 text-xs tracking-wider uppercase">Total Spent</p><p class="text-3xl font-bold text-gray-900 mt-1" id="statTotalSpent">${{ number_format($totalSpent, 2) }}</p></div><div class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center"><svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div></div>
                <div class="h-0.5 w-8 bg-gray-400/40 rounded-full mt-3"></div>
            </div>
        </div>

        <!-- ORDERS GRID SECTION -->
        @if(isset($orders) && count($orders) > 0)
            <div id="ordersGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-7 auto-rows-fr">
                @foreach($orders as $index => $order)
                    <div class="order-card group bg-white border border-gray-100/80 rounded-3xl overflow-hidden transition-all duration-500 card-hover-glow shadow-sm hover:shadow-2xl relative" data-order-id="{{ $order->order_id }}" data-status="{{ strtolower($order->status) }}" data-amount="{{ $order->total_amount }}" data-date="{{ $loop->index }}" style="animation: fadeInUp 0.5s ease-out forwards; animation-delay: {{ $index * 0.07 }}s; opacity:0;">
                        <div class="absolute inset-0 bg-gradient-to-br from-white via-white/95 to-red-50/5 opacity-0 group-hover:opacity-100 transition-opacity duration-700 pointer-events-none rounded-3xl"></div>
                        <div class="p-6 relative z-2">
                            <div class="flex justify-between items-start border-b border-gray-100 pb-4 mb-4">
                                <div>
                                    <span class="text-[11px] font-semibold tracking-wider text-gray-400 uppercase">Order ID</span>
                                    <h3 class="text-xl font-bold tracking-tight text-gray-900 mt-0.5">#{{ $order->order_id }}</h3>
                                </div>
                                <div class="text-right">
                                    <span class="text-[11px] font-semibold tracking-wider text-gray-400 uppercase">Total</span>
                                    <p class="text-2xl font-black text-gray-900 tracking-tight">${{ number_format($order->total_amount, 2) }}</p>
                                </div>
                            </div>
                            <div class="flex justify-between items-center mb-5">
                                <div class="flex items-center gap-2">
                                    @php
                                        $status = strtolower($order->status);
                                        $badgeColor = match($status) {
                                            'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
                                            'cancelled' => 'bg-gray-100 text-gray-600 border-gray-200',
                                            default => 'bg-blue-50 text-blue-700 border-blue-200'
                                        };
                                        $dotPulse = ($status === 'pending') ? 'animate-pulse' : '';
                                    @endphp
                                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-full border {{ $badgeColor }} backdrop-blur-sm status-badge">
                                        <div class="w-1.5 h-1.5 rounded-full {{ $status === 'completed' ? 'bg-emerald-500' : ($status === 'pending' ? 'bg-amber-500 '.$dotPulse : 'bg-gray-500') }}"></div>
                                        <span class="text-[11px] font-bold tracking-widest uppercase">{{ $order->status }}</span>
                                    </div>
                                    <span class="text-xs text-gray-400 flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg> Placed recently</span>
                                </div>
                            </div>
                            
                            <div class="my-6 px-1">
                                <div class="relative flex items-center justify-between">
                                    <div class="absolute left-0 top-1/2 h-[1.5px] w-full bg-gray-200 -translate-y-1/2 z-0"></div>
                                    @php
                                        $steps = ['Ordered', 'Shipped', 'Delivered'];
                                        $statusIndex = match($status) {
                                            'completed' => 2,
                                            'pending' => 0,
                                            'processing' => 1,
                                            default => 0
                                        };
                                    @endphp
                                    @foreach($steps as $idx => $step)
                                        <div class="relative z-10 flex flex-col items-center gap-1.5 bg-white px-1">
                                            <div class="w-7 h-7 rounded-full flex items-center justify-center {{ $idx <= $statusIndex ? 'bg-red-500 text-white shadow-md shadow-red-200' : 'bg-gray-100 text-gray-400' }} transition-all duration-300 text-xs font-bold">{{ $idx+1 }}</div>
                                            <span class="text-[10px] font-semibold tracking-wide text-gray-500">{{ $step }}</span>
                                            @if($idx === $statusIndex && $status !== 'completed' && $status === 'pending')
                                                <div class="w-1 h-1 rounded-full bg-red-500 animate-pulse"></div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row gap-3 mt-5">
                                <a href="{{ route('orders.show', $order->order_id) }}" class="btn-3d button-arrow-hover">
                                    View Details
                                    <span class="inline-block transition-transform duration-200">→</span>
                                </a>
                            </div>
                        </div>
                        <div class="absolute top-4 right-4 w-8 h-8 border-t border-r border-red-200/50 rounded-tr-2xl pointer-events-none opacity-0 group-hover:opacity-100 transition-all duration-500"></div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-24 px-6 text-center bg-white/40 backdrop-blur-sm rounded-3xl border border-gray-100 shadow-inner mt-6">
                <div class="relative">
                    <div class="w-28 h-28 rounded-full bg-gradient-to-tr from-gray-50 to-red-50/30 flex items-center justify-center mx-auto shadow-2xl">
                        <svg class="w-12 h-12 text-red-400/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                    </div>
                    <div class="absolute -top-2 -right-2 w-4 h-4 bg-red-500 rounded-full animate-ping opacity-60"></div>
                </div>
                <h3 class="text-3xl font-bold tracking-tight text-gray-800 mt-8">No Orders Yet</h3>
                <p class="text-gray-400 max-w-sm mt-2 text-sm">Your luxury collection awaits. Start shopping to see premium order tracking.</p>
                <button class="btn-3d mt-8 px-8 py-3">
                    Start Shopping
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                </button>
            </div>
        @endif

        <div class="h-16"></div>
    </div>

    @if(isset($orders) && count($orders) > 0)
    <script>
        // DOM elements
        const filterDropdown = document.getElementById('filterDropdown');
        const filterButton = document.getElementById('filterButton');
        const filterButtonText = document.getElementById('filterButtonText');
        const sortSelect = document.getElementById('sortSelect');
        const ordersGrid = document.getElementById('ordersGrid');
        
        let currentFilter = 'all';

        // Store all original cards
        const allCards = Array.from(document.querySelectorAll('.order-card'));

        // Function to filter and sort orders
        function filterAndSortOrders() {
            // Filter cards based on current filter
            let filteredCards = allCards.filter(card => {
                const status = card.getAttribute('data-status');
                if (currentFilter === 'all') return true;
                return status === currentFilter;
            });

            // Sort filtered cards
            const sortBy = sortSelect.value;
            filteredCards.sort((a, b) => {
                if (sortBy === 'latest') {
                    return parseInt(b.getAttribute('data-date')) - parseInt(a.getAttribute('data-date'));
                } else if (sortBy === 'oldest') {
                    return parseInt(a.getAttribute('data-date')) - parseInt(b.getAttribute('data-date'));
                } else if (sortBy === 'highest') {
                    return parseFloat(b.getAttribute('data-amount')) - parseFloat(a.getAttribute('data-amount'));
                } else if (sortBy === 'lowest') {
                    return parseFloat(a.getAttribute('data-amount')) - parseFloat(b.getAttribute('data-amount'));
                }
                return 0;
            });

            // Update grid
            if (filteredCards.length === 0) {
                ordersGrid.innerHTML = `
                    <div class="col-span-full flex flex-col items-center justify-center py-16 px-6 text-center">
                        <div class="w-20 h-20 rounded-full bg-gray-50 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <p class="text-gray-500 text-lg">No orders match your filters</p>
                        <p class="text-gray-400 text-sm mt-1">Try adjusting your filter criteria</p>
                    </div>
                `;
            } else {
                ordersGrid.innerHTML = '';
                filteredCards.forEach(card => {
                    ordersGrid.appendChild(card);
                });
                // Re-trigger animations
                filteredCards.forEach((card, idx) => {
                    card.style.animation = 'none';
                    card.offsetHeight;
                    card.style.animation = `fadeInUp 0.5s ease-out forwards ${idx * 0.07}s`;
                });
            }
        }

        // Toggle dropdown
        filterButton.addEventListener('click', (e) => {
            e.stopPropagation();
            filterDropdown.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!filterButton.contains(e.target) && !filterDropdown.contains(e.target)) {
                filterDropdown.classList.add('hidden');
            }
        });

        // Filter options from dropdown
        document.querySelectorAll('.filter-option').forEach(option => {
            option.addEventListener('click', (e) => {
                currentFilter = option.getAttribute('data-filter');
                const filterText = option.textContent;
                filterButtonText.innerText = filterText;
                filterDropdown.classList.add('hidden');
                filterAndSortOrders();
            });
        });

        // Sort functionality
        sortSelect.addEventListener('change', () => {
            filterAndSortOrders();
        });

        // Initialize
        filterAndSortOrders();
    </script>
    @endif
</body>
</html>