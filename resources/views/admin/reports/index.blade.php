@extends('layouts.admin')

@section('title', 'Reports')
@section('page-title', 'Analytics & Reports')
@section('page-subtitle', 'Track your store performance and sales insights.')

@section('styles')
    <!-- Tailwind CSS + Google Fonts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        * { font-family: 'Inter', sans-serif; }

        body {
            background: linear-gradient(145deg, #f0f4f8 0%, #e9eef3 100%);
            position: relative;
        }

        /* Cinematic animated gradient background */
        body::before {
            content: '';
            position: fixed;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 30% 40%, rgba(220,38,38,0.04) 0%, rgba(0,0,0,0.02) 50%, transparent 80%);
            animation: cinematicMove 20s ease infinite;
            pointer-events: none;
            z-index: 0;
        }
        @keyframes cinematicMove {
            0% { transform: translate(0, 0); }
            50% { transform: translate(5%, 5%); }
            100% { transform: translate(0, 0); }
        }

        .card-3d {
            transition: all 0.4s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            transform: translateZ(0);
            box-shadow: 0 8px 20px -6px rgba(0,0,0,0.05), 0 1px 1px rgba(0,0,0,0.02);
            background: rgba(255,255,255,0.96);
            backdrop-filter: blur(2px);
            border: 1px solid rgba(255,255,255,0.5);
        }
        .card-3d:hover {
            transform: translateY(-6px) translateZ(12px);
            box-shadow: 0 25px 35px -12px rgba(0, 0, 0, 0.15);
            border-color: rgba(220,38,38,0.2);
        }

        .gradient-title {
            font-weight: 800 !important;
            background: linear-gradient(135deg, #000000, #dc2626);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            letter-spacing: -0.02em;
        }

        .stat-card {
            background: linear-gradient(115deg, #ffffff, #fefefe);
            border-radius: 1rem;
            transition: all 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -12px rgba(0,0,0,0.1);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .table-row {
            transition: all 0.25s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            transform: translateZ(0);
        }
        .table-row:hover {
            transform: translateX(4px) translateY(-2px) translateZ(6px);
            background: linear-gradient(90deg, #ffffff, #fafcff);
            box-shadow: 0 8px 15px -6px rgba(0, 0, 0, 0.08);
            position: relative;
            z-index: 2;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        .status-badge.completed {
            background: #d1fae5;
            color: #065f46;
        }
        .status-badge.pending {
            background: #fef3c7;
            color: #92400e;
        }
        .status-badge.cancelled {
            background: #fee2e2;
            color: #991b1b;
        }
        .status-badge.processing {
            background: #dbeafe;
            color: #1e40af;
        }

        .custom-scroll::-webkit-scrollbar {
            height: 5px;
            width: 5px;
        }
        .custom-scroll::-webkit-scrollbar-track {
            background: #e2e8f0;
            border-radius: 10px;
        }
        .custom-scroll::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #94a3b8, #64748b);
            border-radius: 10px;
        }
    </style>
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8 relative z-10">

    <!-- Header Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Sales -->
        <div class="stat-card p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Sales</p>
                    <p class="text-3xl font-black text-gray-800 mt-2">₱{{ number_format($totalSales, 2) }}</p>
                </div>
                <div class="stat-icon bg-gradient-to-br from-green-50 to-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 h-0.5 w-12 bg-gradient-to-r from-green-300 to-transparent rounded-full"></div>
        </div>

        <!-- Total Orders -->
        <div class="stat-card p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Orders</p>
                    <p class="text-3xl font-black text-gray-800 mt-2">{{ $totalOrders }}</p>
                </div>
                <div class="stat-icon bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 h-0.5 w-12 bg-gradient-to-r from-blue-300 to-transparent rounded-full"></div>
        </div>

        <!-- Average Order Value -->
        <div class="stat-card p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Average Order</p>
                    <p class="text-3xl font-black text-gray-800 mt-2">₱{{ number_format($totalOrders > 0 ? $totalSales / $totalOrders : 0, 2) }}</p>
                </div>
                <div class="stat-icon bg-gradient-to-br from-purple-50 to-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 h-0.5 w-12 bg-gradient-to-r from-purple-300 to-transparent rounded-full"></div>
        </div>

        <!-- Order Status Summary -->
        <div class="stat-card p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Completed Orders</p>
                    <p class="text-3xl font-black text-gray-800 mt-2">{{ $ordersByStatus->where('status', 'completed')->first()->total ?? 0 }}</p>
                </div>
                <div class="stat-icon bg-gradient-to-br from-red-50 to-red-100 text-red-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 h-0.5 w-12 bg-gradient-to-r from-red-300 to-transparent rounded-full"></div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Sales Chart (Line) -->
        <div class="card-3d rounded-2xl p-6">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-1 h-5 bg-gradient-to-b from-red-600 to-black rounded-full"></div>
                <h3 class="text-lg font-bold gradient-title">Sales Over Time</h3>
            </div>
            <canvas id="salesChart" height="250"></canvas>
            @if($salesByDate->isEmpty())
                <p class="text-center text-gray-500 py-8">No sales data available yet.</p>
            @endif
        </div>

        <!-- Order Status Chart (Doughnut) -->
        <div class="card-3d rounded-2xl p-6">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-1 h-5 bg-gradient-to-b from-red-600 to-black rounded-full"></div>
                <h3 class="text-lg font-bold gradient-title">Order Status Distribution</h3>
            </div>
            <canvas id="statusChart" height="250"></canvas>
            @if($ordersByStatus->isEmpty())
                <p class="text-center text-gray-500 py-8">No order data available yet.</p>
            @endif
        </div>
    </div>

    <!-- Order Status Table & Top Customers -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Orders by Status Table -->
        <div class="card-3d rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-bold gradient-title">Order Status Summary</h3>
            </div>
            <div class="overflow-x-auto custom-scroll">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-gray-600 text-xs font-semibold uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3 text-right">Count</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($ordersByStatus as $status)
                            <tr class="table-row">
                                <td class="px-6 py-3">
                                    <span class="status-badge {{ $status->status }}">
                                        {{ ucfirst($status->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-right font-semibold text-gray-800">
                                    {{ $status->total }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-6 py-12 text-center text-gray-500">
                                    No order data available.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Customers -->
        <div class="card-3d rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-1 h-5 bg-gradient-to-b from-red-600 to-black rounded-full"></div>
                    <h3 class="text-lg font-bold gradient-title">Top Customers</h3>
                </div>
                <span class="text-xs text-gray-500">By total spent</span>
            </div>
            <div class="overflow-x-auto custom-scroll">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-gray-600 text-xs font-semibold uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3">#</th>
                            <th class="px-6 py-3">Customer Name</th>
                            <th class="px-6 py-3 text-right">Total Spent</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($topCustomers as $index => $customer)
                            <tr class="table-row">
                                <td class="px-6 py-3 text-sm text-gray-500">{{ $index + 1 }}</td>
                                <td class="px-6 py-3">
                                    <span class="font-medium text-gray-800">
                                        {{ $customer->user->name ?? 'Guest User' }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-right font-semibold text-gray-800">
                                    ₱{{ number_format($customer->total_spent, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-12 text-center text-gray-500">
                                    No customer data available.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Sales List -->
    <div class="card-3d rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-1 h-5 bg-gradient-to-b from-red-600 to-black rounded-full"></div>
                <h3 class="text-lg font-bold gradient-title">Recent Sales</h3>
            </div>
            <span class="text-xs text-gray-500">Last 30 days</span>
        </div>
        <div class="overflow-x-auto custom-scroll">
            <table class="w-full text-left">
                <thead class="bg-gray-50 text-gray-600 text-xs font-semibold uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3 text-right">Sales</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($salesByDate->take(10) as $sale)
                        <tr class="table-row">
                            <td class="px-6 py-3 text-sm text-gray-600">
                                {{ \Carbon\Carbon::parse($sale->date)->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-3 text-right font-semibold text-gray-800">
                                ₱{{ number_format($sale->total_sales, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-6 py-12 text-center text-gray-500">
                                No sales data available.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sales Chart (Line)
        const salesData = @json($salesByDate);
        
        if(salesData.length > 0) {
            const ctx = document.getElementById('salesChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: salesData.map(item => item.date),
                    datasets: [{
                        label: 'Sales (₱)',
                        data: salesData.map(item => item.total_sales),
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
                        legend: {
                            display: false
                        },
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
                            },
                            grid: {
                                color: '#e2e8f0'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        // Status Chart (Doughnut)
        const statusData = @json($ordersByStatus);
        
        if(statusData.length > 0) {
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            
            const statusColors = {
                'completed': '#10b981',
                'pending': '#f59e0b',
                'cancelled': '#ef4444',
                'processing': '#3b82f6'
            };
            
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: statusData.map(item => item.status.charAt(0).toUpperCase() + item.status.slice(1)),
                    datasets: [{
                        data: statusData.map(item => item.total),
                        backgroundColor: statusData.map(item => statusColors[item.status] || '#6b7280'),
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
                            labels: {
                                padding: 15,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = statusData.reduce((sum, item) => sum + item.total, 0);
                                    const percentage = ((context.raw / total) * 100).toFixed(1);
                                    return `${context.label}: ${context.raw} orders (${percentage}%)`;
                                }
                            }
                        }
                    },
                    cutout: '60%'
                }
            });
        }
    });
</script>
@endsection