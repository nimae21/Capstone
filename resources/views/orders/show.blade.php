<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Order Details | ACHILLES</title>
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
        .float-orb { animation: subtleFloat 12s ease-in-out infinite; }
        .float-orb-delayed { animation: subtleFloat 15s ease-in-out infinite reverse; }
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
        
        .btn-3d-outline {
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
        .btn-3d-outline:hover {
            transform: translateY(0px);
            box-shadow: 0 0px 0 0 #d1d5db;
            background: #f9fafb;
            border-color: #dc2626;
            color: #dc2626;
        }
        
        .status-badge {
            backdrop-filter: blur(4px);
        }
        
        .order-item-card {
            transition: all 0.3s ease;
        }
        .order-item-card:hover {
            transform: translateX(4px);
            background: #fef2f2;
            border-left-color: #dc2626;
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

    <div class="relative z-10 max-w-5xl mx-auto px-5 lg:px-8 py-6">
        
        <!-- STICKY NAVBAR -->
        <div class="sticky top-4 z-50 rounded-2xl glass-nav border border-white/40 shadow-sm mb-8 transition-all duration-300">
            <div class="flex items-center justify-between py-3 px-6 flex-wrap gap-4">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-red-600 to-red-400 rounded-xl shadow-md flex items-center justify-center">
                        <div class="w-2 h-2 bg-white rounded-full"></div>
                    </div>
                    <span class="font-bold text-xl tracking-tight bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">ACHILLES</span>
                </div>
                <a href="{{ url()->previous() }}" class="btn-3d-outline">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Back to Orders
                </a>
            </div>
        </div>

        <!-- ORDER DETAILS HEADER -->
        <div class="fade-in-up mb-8">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                </div>
                <h1 class="text-4xl md:text-5xl font-bold tracking-tight text-gray-900">Order Details</h1>
            </div>
            <div class="w-20 h-0.5 bg-red-500/70 rounded-full mt-2"></div>
        </div>

        <!-- MAIN CONTENT GRID -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- LEFT COLUMN - ORDER INFORMATION -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Order Info Card -->
                <div class="bg-white/70 backdrop-blur-sm border border-gray-100 rounded-3xl p-6 shadow-sm hover:shadow-md transition-all">
                    <div class="flex items-center gap-2 mb-5">
                        <div class="w-1 h-6 bg-red-500 rounded-full"></div>
                        <h2 class="text-lg font-bold text-gray-900 tracking-tight">Order Information</h2>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-[11px] font-semibold tracking-wider text-gray-400 uppercase mb-1">Order ID</p>
                            <p class="text-2xl font-bold text-gray-900 tracking-tight">#{{ $order->order_id }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold tracking-wider text-gray-400 uppercase mb-1">Status</p>
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
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border {{ $badgeColor }} backdrop-blur-sm status-badge">
                                <div class="w-1.5 h-1.5 rounded-full {{ $status === 'completed' ? 'bg-emerald-500' : ($status === 'pending' ? 'bg-amber-500 '.$dotPulse : 'bg-gray-500') }}"></div>
                                <span class="text-[11px] font-bold tracking-widest uppercase">{{ $order->status }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shipping Address Card -->
                <div class="bg-white/70 backdrop-blur-sm border border-gray-100 rounded-3xl p-6 shadow-sm hover:shadow-md transition-all">
                    <div class="flex items-center gap-2 mb-5">
                        <div class="w-1 h-6 bg-red-500 rounded-full"></div>
                        <h2 class="text-lg font-bold text-gray-900 tracking-tight">Shipping Address</h2>
                    </div>
                    
                    <div class="space-y-3">
                        <div>
                            <p class="text-[11px] font-semibold tracking-wider text-gray-400 uppercase mb-1">Full Name</p>
                            <p class="text-base font-medium text-gray-800">{{ $order->full_name }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold tracking-wider text-gray-400 uppercase mb-1">Address</p>
                            <p class="text-base text-gray-700 leading-relaxed">
                                {{ $order->street }},<br>
                                {{ $order->barangay }},<br>
                                {{ $order->city }},<br>
                                {{ $order->province }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN - ORDER SUMMARY -->
            <div class="space-y-6">
                <div class="bg-gradient-to-br from-gray-50 to-white border border-gray-100 rounded-3xl p-6 shadow-sm sticky top-24">
                    <div class="flex items-center gap-2 mb-5">
                        <div class="w-1 h-6 bg-red-500 rounded-full"></div>
                        <h2 class="text-lg font-bold text-gray-900 tracking-tight">Order Summary</h2>
                    </div>
                    
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-500">Subtotal</span>
                            <span class="text-sm font-semibold text-gray-900">₱{{ number_format($order->items->sum('price'), 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-500">Shipping</span>
                            <span class="text-sm font-semibold text-gray-900">₱0.00</span>
                        </div>
                        <div class="flex justify-between items-center py-3 mt-2">
                            <span class="text-base font-bold text-gray-900">Total</span>
                            <span class="text-2xl font-black text-red-600">₱{{ number_format($order->items->sum('price'), 2) }}</span>
                        </div>
                    </div>
                    
                    <div class="pt-4 border-t border-gray-200">
                        <div class="flex items-center gap-2 text-xs text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>Order placed recently</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ORDER ITEMS SECTION -->
        <div class="mt-8">
            <div class="bg-white/70 backdrop-blur-sm border border-gray-100 rounded-3xl p-6 shadow-sm">
                <div class="flex items-center gap-2 mb-6">
                    <div class="w-1 h-6 bg-red-500 rounded-full"></div>
                    <h2 class="text-lg font-bold text-gray-900 tracking-tight">Order Items</h2>
                    <span class="ml-auto text-sm text-gray-400">{{ count($order->items) }} item(s)</span>
                </div>
                
                <div class="space-y-3">
                    @foreach($order->items as $item)
                        <div class="order-item-card bg-white border-l-4 border-l-transparent rounded-xl p-4 transition-all shadow-sm hover:shadow-md">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div class="flex-1">
                                    <p class="font-bold text-gray-900 text-lg tracking-tight">
                                        {{ $item->variant->product->name ?? 'Product' }}
                                    </p>
                                    <div class="flex items-center gap-4 mt-2">
                                        <span class="text-sm text-gray-500">Quantity: <span class="font-semibold text-gray-700">{{ $item->quantity }}</span></span>
                                        <span class="text-sm text-gray-500">Price: <span class="font-semibold text-gray-700">₱{{ number_format($item->price, 2) }}</span></span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-400">Subtotal</p>
                                    <p class="text-xl font-bold text-gray-900">₱{{ number_format($item->quantity * $item->price, 2) }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- PROGRESS TIMELINE SECTION -->
        <div class="mt-8 mb-12">
            <div class="bg-white/70 backdrop-blur-sm border border-gray-100 rounded-3xl p-6 shadow-sm">
                <div class="flex items-center gap-2 mb-6">
                    <div class="w-1 h-6 bg-red-500 rounded-full"></div>
                    <h2 class="text-lg font-bold text-gray-900 tracking-tight">Order Progress</h2>
                </div>
                
                <div class="px-4 py-6">
                    <div class="relative flex items-center justify-between">
                        <div class="absolute left-0 top-1/2 h-[2px] w-full bg-gray-200 -translate-y-1/2 z-0"></div>
                        @php
                            $steps = ['Ordered', 'Shipped', 'Delivered'];
                            $statusIndex = match(strtolower($order->status)) {
                                'completed' => 2,
                                'pending' => 0,
                                'processing' => 1,
                                default => 0
                            };
                        @endphp
                        @foreach($steps as $idx => $step)
                            <div class="relative z-10 flex flex-col items-center gap-2 bg-white px-2">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $idx <= $statusIndex ? 'bg-red-500 text-white shadow-md shadow-red-200' : 'bg-gray-100 text-gray-400' }} transition-all duration-300 text-sm font-bold">
                                    {{ $idx+1 }}
                                </div>
                                <span class="text-xs font-semibold tracking-wide text-gray-600">{{ $step }}</span>
                                @if($idx === $statusIndex && strtolower($order->status) === 'pending')
                                    <div class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="h-8"></div>
    </div>

</body>
</html>