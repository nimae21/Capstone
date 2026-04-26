@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('page-title', 'Admin Dashboard')
@section('page-subtitle', 'Manage your online shoe store efficiently.')

@section('styles')
    <!-- Tailwind CSS + Google Fonts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Chart.js for real-time charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        * { font-family: 'Inter', sans-serif; }

        body {
            background: linear-gradient(145deg, #f1f5f9 0%, #eef2f6 100%);
        }

        h1, h2, h3, h4, .title-bold, .page-title, .card-title, .stat-label {
            font-weight: 800 !important;
            letter-spacing: -0.01em;
        }

        .card-3d {
            transition: all 0.25s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            transform: translateZ(0);
            box-shadow: 0 1px 3px rgba(0,0,0,0.02), 0 1px 2px rgba(0,0,0,0.03);
        }
        .card-3d:hover {
            transform: translateY(-6px) translateZ(12px) scale(1.02);
            box-shadow: 0 25px 35px -12px rgba(0, 0, 0, 0.15), 0 4px 8px -4px rgba(0, 0, 0, 0.05);
        }

        .btn-3d-green {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: #10b981;
            color: white;
            font-weight: 700;
            padding: 0.75rem 1.25rem;
            border-radius: 0.5rem;
            border: 1px solid #059669;
            cursor: pointer;
            transition: all 0.05s linear;
            box-shadow: 0 6px 0 #047857, 0 3px 8px rgba(0,0,0,0.1);
            transform: translateY(-2px);
            text-decoration: none;
        }
        .btn-3d-green:active {
            transform: translateY(4px);
            box-shadow: 0 1px 0 #047857, 0 3px 8px rgba(0,0,0,0.05);
        }
        .btn-3d-green:hover {
            background: #059669;
            border-color: #047857;
        }

        .btn-3d-blue {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: #3b82f6;
            color: white;
            font-weight: 700;
            padding: 0.75rem 1.25rem;
            border-radius: 0.5rem;
            border: 1px solid #2563eb;
            cursor: pointer;
            transition: all 0.05s linear;
            box-shadow: 0 6px 0 #1d4ed8, 0 3px 8px rgba(0,0,0,0.1);
            transform: translateY(-2px);
            text-decoration: none;
        }
        .btn-3d-blue:active {
            transform: translateY(4px);
            box-shadow: 0 1px 0 #1d4ed8, 0 3px 8px rgba(0,0,0,0.05);
        }
        .btn-3d-blue:hover {
            background: #2563eb;
            border-color: #1d4ed8;
        }

        .btn-3d-logout {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            color: #1e293b;
            font-weight: 600;
            padding: 0.375rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            font-size: 0.813rem;
        }
        
        .btn-3d-logout:hover {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border-color: #f87171;
            color: #991b1b;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px -4px rgba(220, 38, 38, 0.2);
        }

        .admin-badge {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 0.375rem 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            font-size: 0.813rem;
            color: #1e293b;
        }
        
        .admin-dot {
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(1.1); }
        }

        .table-row-3d {
            transition: all 0.2s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            transform: translateZ(0);
        }
        .table-row-3d:hover {
            transform: translateX(4px) translateY(-2px) translateZ(8px) scale(1.01);
            background: #ffffff;
            box-shadow: 0 8px 15px -6px rgba(0, 0, 0, 0.08), -3px 0 0 #cbd5e1;
            z-index: 2;
            position: relative;
        }

        .status {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-block;
        }
        .status.pending {
            background: #fef3c7;
            color: #92400e;
        }
        .status.completed {
            background: #d1fae5;
            color: #065f46;
        }
        .status.processing {
            background: #dbeafe;
            color: #1e40af;
        }
        .status.cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        .custom-scroll::-webkit-scrollbar {
            height: 4px;
            width: 4px;
        }
        .custom-scroll::-webkit-scrollbar-track {
            background: #e2e8f0;
            border-radius: 10px;
        }
        .custom-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .welcome-title {
            font-weight: 900 !important;
            letter-spacing: -0.02em;
            background: linear-gradient(135deg, #000000 0%, #dc2626 50%, #000000 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            font-size: 2rem;
        }
        
        .section-header {
            font-weight: 800 !important;
            font-size: 1.25rem !important;
            letter-spacing: -0.01em;
            position: relative;
            display: inline-block;
        }
        
        .section-header::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 40px;
            height: 3px;
            background: linear-gradient(90deg, #dc2626, #ef4444);
            border-radius: 3px;
        }
        
        .stat-number {
            font-weight: 900 !important;
            font-size: 2.5rem !important;
        }
        
        .admin-info-bar {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        /* Trend indicator */
        .trend-up { color: #10b981; }
        .trend-down { color: #ef4444; }
    </style>
@endsection

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        
        <!-- Welcome Card -->
        <div class="card-3d bg-white rounded-xl overflow-hidden shadow-md">
            <div class="px-6 py-5 border-b border-gray-100">
                <div class="flex justify-between items-center flex-wrap gap-4">
                    <div>
                        <h1 class="welcome-title">Welcome Back, {{ Auth::user()->name }}!</h1>
                        <p class="text-gray-500 mt-1 text-sm">Here's what's happening with your store today.</p>
                    </div>
                    <div class="admin-info-bar">
                        <div class="admin-badge">
                            <span class="admin-dot"></span>
                            <span>Logged as Admin</span>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn-3d-logout">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Stats Cards - Real Data -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Products -->
            <div class="card-3d rounded-xl p-6" style="background: linear-gradient(115deg, #ffffff 0%, #eff6ff 100%); border-left: 5px solid #3b82f6;">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-700 uppercase tracking-wide">Total Products</p>
                        <p class="stat-number text-4xl text-gray-900 mt-2">{{ $totalProducts }}</p>
                        <p class="text-xs text-gray-500 mt-1">+{{ $newProducts }} new this month</p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-3 h-0.5 w-10 bg-blue-200 rounded-full"></div>
            </div>

            <!-- Total Orders -->
            <div class="card-3d rounded-xl p-6" style="background: linear-gradient(115deg, #ffffff 0%, #ecfdf5 100%); border-left: 5px solid #10b981;">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-green-700 uppercase tracking-wide">Total Orders</p>
                        <p class="stat-number text-4xl text-gray-900 mt-2">{{ $totalOrders }}</p>
                        <p class="text-xs text-gray-500 mt-1">
                            @if($ordersGrowth >= 0)
                                <span class="trend-up">↑ {{ number_format($ordersGrowth, 1) }}%</span>
                            @else
                                <span class="trend-down">↓ {{ number_format(abs($ordersGrowth), 1) }}%</span>
                            @endif
                            from last month
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.5 6h11L17 13M9 21h.01M15 21h.01"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-3 h-0.5 w-10 bg-green-200 rounded-full"></div>
            </div>

            <!-- Registered Users -->
            <div class="card-3d rounded-xl p-6" style="background: linear-gradient(115deg, #ffffff 0%, #eff6ff 100%); border-left: 5px solid #3b82f6;">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-700 uppercase tracking-wide">Registered Users</p>
                        <p class="stat-number text-4xl text-gray-900 mt-2">{{ $totalUsers }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $newUsers }} new this month</p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-3 h-0.5 w-10 bg-blue-200 rounded-full"></div>
            </div>

            <!-- Low Stock Items -->
            <div class="card-3d rounded-xl p-6" style="background: linear-gradient(115deg, #ffffff 0%, #fef2f2 100%); border-left: 5px solid #ef4444;">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-red-700 uppercase tracking-wide">Low Stock Items</p>
                        <p class="stat-number text-4xl text-gray-900 mt-2">{{ $lowStockItems }}</p>
                        <p class="text-xs text-gray-500 mt-1">Need restock soon</p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center text-red-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-3 h-0.5 w-10 bg-red-200 rounded-full"></div>
            </div>
        </div>

        <!-- Revenue Chart & Order Status -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Revenue Chart -->
            <div class="card-3d bg-white rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="section-header text-gray-800">Revenue Overview</h3>
                    <p class="text-xs text-gray-500 mt-2">Last 7 days sales</p>
                </div>
                <div class="p-6">
                    <canvas id="revenueChart" height="250"></canvas>
                </div>
            </div>

            <!-- Order Status Distribution -->
            <div class="card-3d bg-white rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="section-header text-gray-800">Order Status</h3>
                    <p class="text-xs text-gray-500 mt-2">Current order distribution</p>
                </div>
                <div class="p-6">
                    <canvas id="statusChart" height="250"></canvas>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div>
            <h2 class="section-header text-gray-800 mb-4">Quick Actions</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('admin.products.index') }}" class="btn-3d-green text-center">+ Add Product</a>
                <a href="{{ route('admin.categories.index') }}" class="btn-3d-green text-center">+ Add Category</a>
                <a href="{{ route('admin.orders.index') }}" class="btn-3d-blue text-center">Manage Orders</a>
                <a href="{{ route('admin.users.index') }}" class="btn-3d-blue text-center">Manage Users</a>
            </div>
        </div>

        <!-- Lower Grid: Recent Orders + Low Stock Alerts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Orders Table -->
            <div class="card-3d bg-white rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="section-header text-gray-800">Recent Orders</h3>
                </div>
                <div class="overflow-x-auto custom-scroll">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 text-gray-600 text-xs font-bold uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-3">Order ID</th>
                                <th class="px-6 py-3">Customer</th>
                                <th class="px-6 py-3">Total</th>
                                <th class="px-6 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($recentOrders as $order)
                            <tr class="table-row-3d">
                                <td class="px-6 py-3 text-sm font-semibold text-gray-900">#{{ $order->order_id }}</td>
                                <td class="px-6 py-3 text-sm text-gray-600">{{ $order->user->name ?? 'Guest' }}</td>
                                <td class="px-6 py-3 text-sm font-semibold text-gray-900">₱{{ number_format($order->total_amount, 2) }}</td>
                                <td class="px-6 py-3">
                                    <span class="status {{ $order->status }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">No orders yet</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Low Stock Alerts -->
            <div class="card-3d bg-white rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="section-header text-gray-800">Low Stock Alerts</h3>
                </div>
                <div class="p-4 space-y-3">
                    @forelse($lowStockProducts as $product)
                    <div class="alert-item-3d border-l-4 border-red-400 p-4 rounded-lg transition-all bg-red-50/30">
                        <div class="flex justify-between items-start">
                            <div>
                                <strong class="block text-gray-800 font-bold">{{ $product->product_name }}</strong>
                                <span class="text-sm text-gray-600">{{ $product->variant_size }} / {{ $product->variant_color }}</span>
                            </div>
                            <span class="text-xs font-bold text-red-600 bg-red-100 px-2 py-1 rounded-full">
                                Only {{ $product->available_stock }} left
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-check-circle text-green-500 text-2xl mb-2 block"></i>
                        All stock levels are healthy!
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Revenue Chart
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: @json($chartLabels),
                    datasets: [{
                        label: 'Revenue (₱)',
                        data: @json($chartData),
                        borderColor: '#dc2626',
                        backgroundColor: 'rgba(220, 38, 38, 0.05)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#dc2626',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return '₱' + context.raw.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₱' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });

            // Status Chart
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: @json($statusLabels),
                    datasets: [{
                        data: @json($statusCounts),
                        backgroundColor: ['#10b981', '#f59e0b', '#3b82f6', '#ef4444', '#8b5cf6'],
                        borderWidth: 0,
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { usePointStyle: true, pointStyle: 'circle' }
                        }
                    },
                    cutout: '60%'
                }
            });
        });
    </script>
@endsection