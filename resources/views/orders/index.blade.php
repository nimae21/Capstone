<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>My Orders | ACHILLES</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        body { background-color: #ffffff; overflow-x: hidden; }
        @keyframes subtleFloat {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-8px) rotate(1deg); }
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes float3D {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-3px); }
            100% { transform: translateY(0px); }
        }
        .float-orb { animation: subtleFloat 12s ease-in-out infinite; }
        .float-orb-delayed { animation: subtleFloat 15s ease-in-out infinite reverse; }
        .card-hover-glow {
            transition: all 0.4s cubic-bezier(0.2, 0.9, 0.4, 1.1);
        }
        .card-hover-glow:hover {
            box-shadow: 0 25px 40px -12px rgba(220, 38, 38, 0.25), 0 0 0 1px rgba(220, 38, 38, 0.15);
            transform: translateY(-6px);
        }
        .status-badge {
            backdrop-filter: blur(4px);
        }
        .button-arrow-hover span {
            transition: transform 0.25s ease;
        }
        .button-arrow-hover:hover span {
            transform: translateX(4px);
        }
        .glass-nav {
            background: rgba(255, 255, 255, 0.82);
            backdrop-filter: blur(16px);
        }
        .hero-grid-pattern {
            background-image: radial-gradient(circle at 1px 1px, rgba(0,0,0,0.02) 1px, transparent 1px);
            background-size: 32px 32px;
        }
        .fade-in-up {
            animation: fadeInUp 0.5s ease-out forwards;
        }
        .filter-dropdown {
            position: absolute;
            right: 0;
            top: calc(100% + 8px);
            min-width: 200px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 35px -10px rgba(0,0,0,0.15), 0 0 0 1px rgba(0,0,0,0.02);
            z-index: 9999;
            overflow: hidden;
        }
        .relative {
            z-index: 10;
        }
        .relative .filter-dropdown {
            z-index: 9999;
        }
        
        /* 3D Button Styles */
        .btn-3d {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            font-weight: 600;
            border-radius: 12px;
            padding: 10px 20px;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            box-shadow: 0 4px 0 0 #7f1d1d, 0 2px 8px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .btn-3d:hover {
            transform: translateY(0px);
            box-shadow: 0 1px 0 0 #7f1d1d, 0 4px 12px rgba(220, 38, 38, 0.4);
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }
        .btn-3d:active {
            transform: translateY(2px);
            box-shadow: 0 0px 0 0 #7f1d1d;
        }
        
        .btn-3d-white {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: white;
            color: #1f2937;
            font-weight: 500;
            border-radius: 12px;
            padding: 10px 20px;
            font-size: 0.875rem;
            border: 1px solid #e5e7eb;
            transition: all 0.2s ease;
            box-shadow: 0 2px 0 0 #d1d5db;
            transform: translateY(-1px);
        }
        .btn-3d-white:hover {
            transform: translateY(0px);
            box-shadow: 0 0px 0 0 #d1d5db;
            background: #f9fafb;
            border-color: #9ca3af;
        }
        .btn-3d-white:active {
            transform: translateY(1px);
        }
        
        .filter-btn-3d {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(8px);
            color: #374151;
            font-weight: 500;
            border-radius: 9999px;
            padding: 10px 20px;
            font-size: 0.875rem;
            border: 1px solid #e5e7eb;
            transition: all 0.2s ease;
            box-shadow: 0 2px 0 0 #d1d5db;
            transform: translateY(-1px);
        }
        .filter-btn-3d:hover {
            transform: translateY(0px);
            box-shadow: 0 0px 0 0 #d1d5db;
            background: white;
            border-color: #dc2626;
        }
        
        .sort-select-3d {
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(8px);
            padding: 8px 12px;
            border-radius: 9999px;
            border: 1px solid #f3f4f6;
            font-size: 0.75rem;
            font-weight: 600;
            color: #1f2937;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 1px 0 0 #e5e7eb;
        }
        .sort-select-3d:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 0 0 #d1d5db;
        }
    </style>
</head>
<body class="antialiased">

    <!-- CINEMATIC BACKGROUND ORBS + LIGHTING -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
        <div class="absolute top-[-20%] left-[-10%] w-[600px] h-[600px] bg-red-50/40 rounded-full blur-[100px] float-orb"></div>
        <div class="absolute bottom-[-10%] right-[-5%] w-[550px] h-[550px] bg-gray-100/60 rounded-full blur-[120px] float-orb-delayed"></div>
        <div class="absolute top-[30%] right-[20%] w-[300px] h-[300px] bg-red-100/30 rounded-full blur-[80px]"></div>
        <div class="hero-grid-pattern absolute inset-0 opacity-40"></div>
        <div class="absolute top-0 left-0 w-full h-[500px] bg-gradient-to-b from-white via-white/95 to-transparent pointer-events-none"></div>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto px-5 lg:px-8 py-6">
        
        <!-- STICKY NAVBAR -->
        <div class="sticky top-4 z-50 rounded-2xl glass-nav border border-white/40 shadow-sm mb-8 transition-all duration-300">
            <div class="flex items-center justify-between py-3 px-6 flex-wrap gap-4">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-red-600 to-red-400 rounded-xl shadow-md flex items-center justify-center">
                        <div class="w-2 h-2 bg-white rounded-full"></div>
                    </div>
                    <span class="font-bold text-xl tracking-tight bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">ACHILLES</span>
                </div>
            </div>
        </div>

        <!-- HERO + HEADER SECTION -->
        <div class="relative mb-12 mt-2 fade-in-up">
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
                <div class="space-y-3">
                    <h1 class="text-5xl md:text-6xl font-bold tracking-tight text-gray-900 leading-[1.1]">My Orders</h1>
                    <p class="text-gray-500 text-base md:text-lg tracking-wide max-w-xl">Track, manage, and explore your purchases — cinematic luxury experience.</p>
                    <div class="w-16 h-0.5 bg-red-500/70 rounded-full mt-2"></div>
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